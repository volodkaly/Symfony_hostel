# Real-time Chat Implementation with Mercure

## Overview

Per-user real-time messaging system using Mercure WebSocket hub. Admins and users can communicate in real-time without seeing each other's private conversations.

## How It Works

### User-to-Admin Flow

1. **User sends message** → `ApiChatController` automatically finds first admin and sets as recipient
2. **Message saved to DB** → Recipient and sender stored
3. **Mercure event dispatched** → `ChatNotificationSubscriber` publishes to admin's Mercure topic
4. **Admin receives** → Subscribed to `chat/{adminId}`, sees message in real-time

### Admin-to-User Flow

1. **Admin sends message** → Specifies `recipientId` in the form
2. **Message saved to DB** → User set as recipient
3. **Mercure event dispatched** → Published to user's Mercure topic
4. **User receives** → Subscribed to `chat/{userId}`, sees message in real-time

### Key Technical Details

**Per-User Topics**

- Each user subscribes only to their own topic: `http://mysite.com/chat/{userId}`
- Messages are published only to the recipient's topic
- Prevents message leaks between conversations

**Optimistic UI**

- Sender sees their own message immediately (before Mercure confirmation)
- No wait for round-trip through server
- Deduplication via message ID Set prevents double-display

**Mercure Payload**

```json
{
  "id": 123,
  "content": "message text",
  "senderId": 38,
  "senderName": "User38",
  "recipientId": 1
}
```

## Files Modified

1. **`templates/chat/index.html.twig`**

   - Per-user topic subscription
   - Optimistic message display
   - Deduplication logic with Set

2. **`src/Controller/ApiChatController.php`**

   - Auto-assign admin as recipient for user messages
   - Respect explicit recipientId from admin

3. **`src/EventSubscriber/ChatNotificationSubscriber.php`**
   - Publish only to recipient's Mercure topic
   - Clean payload with metadata

## Testing

**User → Admin:**

- Open chat as User38
- Send "test"
- Admin should see it immediately

**Admin → User:**

- Open chat as Admin
- Type User ID in the field (e.g., 38)
- Send "reply"
- User38 should see it immediately

## Troubleshooting

- **Messages not appearing:** Check Mercure hub is running and reachable at `mercure_public_url`
- **Duplicate messages:** Deduplication is automatic via message ID Set
- **Admin not found:** Verify users have `ROLE_ADMIN` in their roles (JSON field)

---

Done! The chat system now works end-to-end with proper privacy isolation.
