# Mobile Real-time Chat Websocket Handoff

This document outlines the websocket architecture and implementation details for the WhatsApp workflow automation platform. Mobile developers (Flutter) should use this guide to implement real-time messaging updates.

## 1. Overview
The platform uses **Laravel Broadcasting** with a **Pusher-compatible** websocket server.
Mobile authentication is handled via **Sanctum (Bearer Token)**.

- **Broadcaster**: Pusher
- **Real-time Engine**: Laravel Broadcasting (PHP) / Laravel Echo (JS equivalent)
- **Auth Guard**: `auth:sanctum`

## 2. Connection Configuration
The mobile app must connect to the websocket server using the following parameters. 

| Parameter | Value (Placeholder) |
|-----------|-------------------|
| **App Key** | `PUSHER_APP_KEY` |
| **Cluster** | `PUSHER_APP_CLUSTER` |
| **Host** | `PUSHER_HOST` (e.g. `ws-ap2.pusher.com` or custom) |
| **Port** | `80` (WS) / `443` (WSS) |
| **Encrypted (TLS)** | `true` |
| **Auth Endpoint** | `https://api.your-domain.com/api/v1/broadcasting/auth` |

### Flutter Config Example
```dart
class RealtimeConfig {
  static const String pusherKey = 'YOUR_PUSHER_KEY';
  static const String pusherCluster = 'YOUR_PUSHER_CLUSTER';
  static const String authEndpoint = 'https://your-api.com/api/v1/broadcasting/auth';
  
  // Use these for custom host if not using standard Pusher
  static const String wsHost = 'your-ws-host.com';
  static const int wsPort = 443;
}
```

## 3. Authentication
Since the channels are **Private**, you must provide the Bearer token during the subscription process.

- **Method**: `POST`
- **URL**: `/api/v1/broadcasting/auth`
- **Headers**:
  - `Authorization: Bearer <YOUR_ACCESS_TOKEN>`
  - `Accept: application/json`
  - `Content-Type: application/x-www-form-urlencoded`
- **Body**: `socket_id=<SOCKET_ID>&channel_name=<CHANNEL_NAME>`

## 4. Channels & Events

### A. Company-Level Channel (Inbox Updates)
Used for updating the conversation list, unread counts, and showing global notifications.

- **Channel Name**: `private-company.{companyId}.chats`
- **Events**:

#### 1. `chat.inbound.received`
Fired when a new message arrives from a customer.
```json
{
  "company_id": 1,
  "conversation_id": 123,
  "message_id": 456,
  "message_preview": "Hello there!",
  "created_at": "2026-05-08 12:00:00",
  "phone_number": "+1234567890",
  "sender_name": "John Doe",
  "direction": "inbound"
}
```

#### 2. `conversation.updated`
Fired when conversation metadata changes (preview, unread count, assignment).
```json
{
  "id": 123,
  "company_id": 1,
  "contact_name": "John Doe",
  "contact_phone": "+1234567890",
  "status": "open",
  "assignment_status": "assigned",
  "preview": "Hello there!",
  "last_message": "Hello there!",
  "unread_count": 5,
  "time_label": "2m ago",
  "last_message_at": "2026-05-08 12:00:00"
}
```

### B. Conversation-Level Channel (Chat Window)
Used for granular message updates within a specific opened chat.

- **Channel Name**: `private-company.{companyId}.conversation.{conversationId}`
- **Events**:

#### 1. `message.received`
Fired when a message is added to this conversation (both inbound and outbound).
```json
{
  "id": 456,
  "conversation_id": 123,
  "company_id": 1,
  "contact_id": 789,
  "direction": "inbound",
  "message_type": "text",
  "body": "Hello there!",
  "media_url": null,
  "resolved_media_url": null,
  "status": "received",
  "time_label": "12:00",
  "created_at": "2026-05-08 12:00:00",
  "sender_name": "John Doe"
}
```

## 5. Testing & Debugging
You can trigger a test event using the following Artisan command on the server:

```bash
php artisan test:broadcast-chat {conversation_id} "Your test message"
```

This will broadcast an `InboundMessageReceived` event to the company channel associated with that conversation.

## 6. Implementation Notes for Flutter
1. **Leading Dots**: In Laravel Echo, event names are often prefixed with a dot (e.g., `.chat.inbound.received`). When using the native Pusher client, you may or may not need this dot depending on the library. Try without the dot first.
2. **Channel Authorization**: Ensure your Pusher client is configured to send the `Authorization` header to the auth endpoint.
3. **Company ID**: You can get your `company_id` from the `/api/v1/auth/me` endpoint.
