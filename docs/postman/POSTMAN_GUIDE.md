# WhatsApp Workflow Automation API - Postman Guide

## Overview
This directory contains the Postman collection and environment files required to interact with the WhatsApp Workflow Automation API. These resources are specifically designed for the Flutter mobile developer to test and integrate authentication and core messaging features.

## Files Included
1. `whatsapp-workflow-api.postman_collection.json`: The core API collection containing all versioned endpoints.
2. `whatsapp-workflow-local.postman_environment.json`: Local development environment variables.

## How to Import into Postman
1. Open Postman.
2. Click the **Import** button in the top-left corner.
3. Drag and drop both the collection and environment JSON files into the import area.
4. Ensure the **WhatsApp Workflow Automation API - v1** collection and **WhatsApp Workflow - Local** environment are selected.

## Environment Setup
After importing, select the **WhatsApp Workflow - Local** environment from the environment dropdown in the top-right corner.

The following variables are available:
- `base_url`: Default is `http://localhost:8000`.
- `api_version`: Default is `v1`.
- `auth_token`: Automatically updated upon successful login.
- `test_email`: Used for login requests.
- `test_password`: Used for login requests.

## Authentication Flow
The API uses **Laravel Sanctum** for bearer token authentication.

### How to Login and Save Token Automatically
1. Locate the **Auth > Login** request in the collection.
2. Ensure your local server is running (`php artisan serve`).
3. Click **Send**.
4. The successful response includes a `token`.
5. A **Post-response script** (Tests tab) automatically saves this token to your `auth_token` environment variable.

### How to Call Protected Routes
All protected routes (e.g., `/me`, `/logout`) automatically include the following header:
`Authorization: Bearer {{auth_token}}`

## Common Error Responses
- **401 Unauthenticated**: The bearer token is missing, invalid, or expired.
- **422 Validation Error**: Required fields are missing or data format is incorrect. Check the `errors` object in the response.
- **404 Not Found**: The requested resource or endpoint does not exist.

## Local Development
By default, the `base_url` is set to `http://localhost:8000`. If you are using a different port or a tool like Ngrok, update the `base_url` variable in your Postman environment.

## Sample cURL Requests

### Login
```bash
curl --location 'http://localhost:8000/api/v1/auth/login' \
--header 'Accept: application/json' \
--header 'Content-Type: application/json' \
--data-raw '{
    "email": "developer@example.com",
    "password": "password"
}'
```

### Get Profile (Me)
```bash
curl --location 'http://localhost:8000/api/v1/auth/me' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer <your_token_here>'
```

### Logout
```bash
curl --location --request POST 'http://localhost:8000/api/v1/auth/logout' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer <your_token_here>'
```

## WhatsApp Templates APIs
These endpoints allow you to manage WhatsApp message templates.

### Workflow:
1. **Login first**: Ensure you have a valid `auth_token`.
2. **List Templates**: Call `GET /whatsapp/templates`. 
   - This request includes a test script that automatically saves the ID of the first template in the list to the `template_id` environment variable.
3. **Show Template**: Call `GET /whatsapp/templates/{{template_id}}` to see full details.
4. **Sync Templates**: Call `POST /whatsapp/templates/sync` to pull the latest templates from Meta.
   - > [!WARNING]
   - > Sync calls the Meta Graph API. Avoid excessive calling in production.
5. **Delete Template**: Call `DELETE /whatsapp/templates/{{template_id}}`.
   - > [!CAUTION]
   - > This will attempt to delete the template from Meta as well.

### Common Errors:
- **403 Forbidden**: Returned if you try to access or delete a template belonging to a different company.
- **404 Not Found**: Returned if the template ID does not exist in the database.

## Chats APIs
These endpoints allow you to interact with chat conversations and messages.

### Workflow:
1. **Login first**: Ensure you have a valid `auth_token`.
2. **List Chats**: Call `GET /chats`.
   - This request includes a test script that automatically saves the ID of the first conversation to the `conversation_id` environment variable.
3. **Show Chat**: Call `GET /chats/{{conversation_id}}` to see details.
4. **List Messages**: Call `GET /chats/{{conversation_id}}/messages`.
5. **Send Text Message**: Call `POST /chats/{{conversation_id}}/messages/text`.
6. **Send Media Message**: Call `POST /chats/{{conversation_id}}/messages/media`.
   - Use `multipart/form-data`.
   - Attach a file to the `media_file` key.
7. **Actions**: Use `POST /chats/{{conversation_id}}/read`, `/close`, etc.

### Common Errors:
- **403 Forbidden / 404 Not Found**: Returned if you try to access a conversation belonging to a different company.
- **422 Unprocessable Entity**: Returned if validation fails (e.g., missing message body or unsupported media type).
- **500 Internal Server Error**: Often indicates a WhatsApp provider error. Check the `message` for details like "WhatsApp message provider rejected the request."

## Audience Group Members APIs
These endpoints allow you to manage the membership of audience groups (lists).

### Workflow:
1. **Login first**: Ensure you have a valid `auth_token`.
2. **List Groups**: Call `GET /contact-groups`.
   - This request includes a test script that automatically saves the ID of the first group to the `audience_group_id` environment variable.
3. **Available Contacts for Group**: Call `GET /contact-groups/{{audience_group_id}}/available-contacts`.
   - This returns contacts that are NOT yet in the group.
   - The test script saves the ID of the first available contact to the `contact_id` environment variable.
4. **Add Contacts to Group**: Call `POST /contact-groups/{{audience_group_id}}/members`.
   - Send an array of `contact_ids` in the body.
5. **List Group Members**: Call `GET /contact-groups/{{audience_group_id}}/members`.
   - Returns all contacts currently inside the group.
6. **Remove Contacts from Group**: Call `DELETE /contact-groups/{{audience_group_id}}/members`.
   - Send an array of `contact_ids` to remove them from the group.

### Common Errors:
- **403 Forbidden / 404 Not Found**: Returned if you try to access a group or contacts belonging to a different company.
- **422 Unprocessable Entity**: Returned if you try to add a contact that is already a member, or if the selected contacts do not belong to your company.

## Troubleshooting
- **Connection Refused**: Ensure your Laravel server is running and accessible from Postman.
- **CSRF Token Mismatch**: API routes are exempt from CSRF protection. Ensure you are calling `/api/...` and not a web route.
- **Token Not Saving**: Ensure you have the correct environment selected in the top-right corner before logging in.
