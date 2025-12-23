# Real-time Notification System Implementation

## Overview
This document describes the real-time notification system implemented across all admin dashboards.

## Components

### 1. WebSocket Server
- **File**: `admin/websocket-server.php`
- **Port**: 8080
- **Channels**: 
  - `orders` - Order notifications
  - `contact_messages` - Contact message notifications
  - `reservations` - Reservation notifications
  - `main_dashboard` - Aggregated notifications for main dashboard

**To run the WebSocket server:**
```bash
php admin/websocket-server.php
```

### 2. WebSocket Client
- **File**: `admin/js/websocket-client.js`
- Automatically connects to WebSocket server
- Handles reconnection and fallback to polling
- Supports multiple channels

### 3. Dashboard Implementations

#### Main Dashboard (`admin/dashboard.php`)
- Aggregates notifications from all dashboards
- Real-time updates via WebSocket
- Shows notifications from orders, contact messages, reservations, etc.
- Filter by notification type

#### Contact Messages Dashboard (`admin/admin-contact-messages.php`)
- Notification bell with unread message count
- Real-time updates when new messages arrive
- Dropdown with message previews
- Marks as read when clicked
- WebSocket connection to `contact_messages` channel

#### Orders Dashboard (`admin/admin-orders.php`)
- Live order count badge
- Toast notifications for new orders
- Sound alerts (optional)
- Auto-updating order list
- WebSocket connection to `orders` channel

### 4. API Endpoints

#### Contact Messages
- `api/get-contact-notifications.php` - Get contact message notifications
- `api/mark-contact-read.php` - Mark a contact message as read
- `api/mark-all-contact-read.php` - Mark all contact messages as read

#### Orders
- `api/get-order-notifications.php` - Get order notifications
- `api/mark-notification-read.php` - Mark notification as read (general)
- `api/mark-all-notifications-read.php` - Mark all notifications as read (general)

### 5. Profile Settings Access
All dashboards now have consistent access to profile settings:
- Main Dashboard: User menu dropdown → Settings
- Contact Messages: User menu → Settings
- Orders: User menu dropdown → Settings
- Menu Management: Sidebar → Settings

## Features

### Real-time Updates
- WebSocket connections for instant notifications
- Automatic reconnection on disconnect
- Fallback to HTTP polling if WebSocket fails

### Notification Types
- **Orders**: New orders, order status updates
- **Contact Messages**: New messages, unread count
- **Reservations**: New reservations, pending confirmations

### User Experience
- Toast notifications for new items
- Sound alerts (optional, can be disabled)
- Badge counts on notification icons
- Dropdown previews with click-to-view
- Auto-updating lists

## Setup Instructions

1. **Start WebSocket Server:**
   ```bash
   php admin/websocket-server.php
   ```
   Or run as a background service:
   ```bash
   nohup php admin/websocket-server.php > websocket.log 2>&1 &
   ```

2. **Ensure Database Tables Exist:**
   - `notifications` table (already exists)
   - `contact_messages` table (already exists)
   - `orders` table (already exists)
   - `reservations` table (already exists)

3. **Configure WebSocket Port:**
   - Default port: 8080
   - Can be changed in `websocket-server.php` (WS_PORT constant)

4. **Firewall Configuration:**
   - Ensure port 8080 is open for WebSocket connections

## Browser Compatibility
- Modern browsers with WebSocket support
- Automatic fallback to polling for older browsers
- Works with HTTPS (wss://) and HTTP (ws://)

## Troubleshooting

### WebSocket Not Connecting
1. Check if server is running: `ps aux | grep websocket-server.php`
2. Check firewall settings for port 8080
3. Verify WebSocket URL in browser console
4. System will automatically fallback to polling

### Notifications Not Updating
1. Check browser console for errors
2. Verify API endpoints are accessible
3. Check database connection
4. Ensure session is active

### Sound Not Playing
- Sound alerts are optional and may be blocked by browser
- Check browser autoplay policies
- Sound will fail silently if not allowed

## Future Enhancements
- Notification preferences per admin
- Email notifications for critical items
- Push notifications for mobile
- Notification history and search
- Custom notification sounds

