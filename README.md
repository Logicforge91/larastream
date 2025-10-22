# Multi-Camera Live Streaming MVP (Laravel 10)

This project is a **multi-camera live streaming MVP** built with **Laravel 10** and **WebRTC**.  
It allows a host to broadcast from multiple cameras, switch between cameras, inject pre-recorded videos, and stream to multiple guests in real-time — all without external WebSocket packages.

---

## **Features**

### Host Features
- Multi-camera streaming (supports 2+ cameras)
- Live camera switching during broadcast
- Pre-recorded video injection into live stream
- Invite multiple guests to join
- Guest video grid view
- Upload videos for injection

### Guest Features
- Connect to host via unique URL
- Receive live host feed
- Send guest camera feed to host
- Supports multiple simultaneous guests

### Technical Details
- **Backend:** Laravel 10
- **Frontend:** HTML, JS, WebRTC
- **Database:** MySQL/PostgreSQL
- **Signaling:** Laravel API endpoints (no WebSockets)
- **Video Uploads:** Stored in `public/uploads/`
- **Real-time video:** WebRTC with polling for signaling

---

## **Installation**

```bash
# Clone the repo
git clone <repo_url>
cd livestream-mvp

# Install dependencies
composer install
npm install
npm run dev

# Set up database
cp .env.example .env
php artisan key:generate

# Configure DB in .env
DB_DATABASE=livestream
DB_USERNAME=root
DB_PASSWORD=secret

php artisan migrate

# Start server
php artisan serve
