# Real-Time WebSocket & Inbound Message Event Integration Guide

## Task Context
The backend broadcasting infrastructure uses Pusher WebSockets and Sanctum API Token Authentication. End-to-end server tests confirm that when an inbound WhatsApp message arrives, real-time events are dispatched over Pusher. 

Currently, the mobile app requires a manual pull-to-refresh to view new inbound messages. Follow the implementation requirements below to fix real-time event listening.

---

## 1. Broadcasting Channel Authorization API Endpoint

Private channels require authentication via Sanctum. Flutter must pass the user's Sanctum Bearer token when authorizing Pusher channels.

- **Endpoint**: `POST /api/v1/broadcasting/auth`
- **Full URL**: `https://whatsapp-automate.empoweredtechinnovations.org/api/v1/broadcasting/auth`
- **HTTP Headers**:
  ```http
  Authorization: Bearer <SANCTUM_USER_TOKEN>
  Accept: application/json
  Content-Type: application/x-www-form-urlencoded
  ```
- **Form Body Data**:
  - `socket_id`: `<socket_id_from_pusher>`
  - `channel_name`: `private-company.<company_id>.chats` (or `private-company.<company_id>.conversation.<conversation_id>`)

- **Expected Response (HTTP 200 OK)**:
  ```json
  {
    "auth": "093079a2a87c59e12c7a:58d0b78dcb12569c6f66075b34f931bb83729d0f55a2166d878460d972b0072a"
  }
  ```

> ⚠️ **CRITICAL**: Do NOT call `/broadcasting/auth` (web session route). You MUST call `/api/v1/broadcasting/auth`.

---

## 2. Pusher Channels & Exact `private-` Prefix Rule

In Flutter (using `pusher_channels_flutter` or `laravel_echo`), channel names MUST explicitly start with the **`private-`** prefix.

1. **Company Chats Channel (For Inbox List & General Events)**:
   - Channel Name: `private-company.<COMPANY_ID>.chats`
2. **Single Conversation Channel (For Active Chat Screen)**:
   - Channel Name: `private-company.<COMPANY_ID>.conversation.<CONVERSATION_ID>`

---

## 3. Real-Time Event Names & Listener Specifications

The backend uses `broadcastAs()` to map event class names to short event names. Flutter MUST bind to these exact strings:

### A. Event: `message.received`
- **Fired On Channels**: `private-company.<company_id>.chats` AND `private-company.<company_id>.conversation.<conversation_id>`
- **Use Case**: Append new message to the active chat screen or play sound.
- **Event Payload Structure**:
  ```json
  {
    "id": 692,
    "conversation_id": 95,
    "company_id": 6,
    "contact_id": 75,
    "direction": "inbound",
    "message_type": "text",
    "body": "Hello, need help with my order",
    "media_url": null,
    "resolved_media_url": null,
    "status": "received",
    "time_label": "17:18",
    "created_at": "2026-09-18 17:18:54",
    "sender_name": "Jeremiah"
  }
  ```

### B. Event: `chat.inbound.received`
- **Fired On Channel**: `private-company.<company_id>.chats`
- **Use Case**: Update chat list preview, bump conversation to top of list, show badge notification.
- **Event Payload Structure**:
  ```json
  {
    "company_id": 6,
    "conversation_id": 95,
    "message_id": 692,
    "message_preview": "Hello, need help with my order",
    "created_at": "2026-09-18 17:18:54",
    "phone_number": "+2347010894583",
    "sender_name": "Jeremiah",
    "direction": "inbound"
  }
  ```

### C. Event: `conversation.updated`
- **Fired On Channels**: `private-company.<company_id>.chats` AND `private-company.<company_id>.conversation.<conversation_id>`
- **Use Case**: Update unread count badges, delivery ticks status (`single_grey`, `double_grey`, `double_blue`), and active session state.
- **Event Payload Structure**:
  ```json
  {
    "id": 95,
    "company_id": 6,
    "contact_name": "Jeremiah",
    "contact_phone": "+2347010894583",
    "status": "open",
    "unread_count": 1,
    "last_message_preview": "Hello, need help with my order",
    "last_message_status": "received",
    "last_message_ticks_state": "pending"
  }
  ```

---

## 4. Flutter Dart Sample Implementation Snippet

If using `pusher_channels_flutter`, initialize and authorize channels like this:

```dart
import 'package:pusher_channels_flutter/pusher_channels_flutter.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';

PusherChannelsFlutter pusher = PusherChannelsFlutter.getInstance();

Future<void> initPusher({
  required String userSanctumToken,
  required int companyId,
}) async {
  try {
    await pusher.init(
      apiKey: "093079a2a87c59e12c7a",
      cluster: "ap2",
      onAuthorizer: (channelName, socketId, options) async {
        // Custom authorization call to Laravel Sanctum API endpoint
        final response = await http.post(
          Uri.parse("https://whatsapp-automate.empoweredtechinnovations.org/api/v1/broadcasting/auth"),
          headers: {
            "Authorization": "Bearer $userSanctumToken",
            "Accept": "application/json",
            "Content-Type": "application/x-www-form-urlencoded",
          },
          body: {
            "socket_id": socketId,
            "channel_name": channelName,
          },
        );
        return jsonDecode(response.body);
      },
      onEvent: (PusherEvent event) {
        print("Pusher Event Received: ${event.eventName} on channel ${event.channelName}");
        
        if (event.eventName == 'message.received') {
          final messageData = jsonDecode(event.data);
          // TODO: Add message to current conversation state
        } else if (event.eventName == 'chat.inbound.received') {
          final inboundData = jsonDecode(event.data);
          // TODO: Update chat inbox list preview & move to top
        } else if (event.eventName == 'conversation.updated') {
          final conversationData = jsonDecode(event.data);
          // TODO: Update unread counts and ticks
        }
      },
    );

    // Subscribe to company chats channel (ALWAYS include private- prefix)
    await pusher.subscribe(channelName: "private-company.$companyId.chats");
    await pusher.connect();
  } catch (e) {
    print("Pusher Error: $e");
  }
}
```

---

## 5. Summary Checklist for Mobile Dev

- [ ] Ensure channel subscriptions use `private-company.<company_id>.chats` (with `private-` prefix).
- [ ] Ensure authentication endpoint is `POST /api/v1/broadcasting/auth` with `Authorization: Bearer <token>`.
- [ ] Ensure event listeners bind to `message.received`, `chat.inbound.received`, and `conversation.updated`.
- [ ] Test receiving an inbound message while the app is foregrounded without pulling to refresh.
