# WhatsApp Multi-Device Gateway

A modern, full-featured WhatsApp gateway built with Node.js, Express.js, and React TypeScript for the Student Attendance System. This application provides seamless WhatsApp integration with QR code authentication, message queue management, and real-time communication.

## 🚀 Features

### Core Features
- **Multi-Device WhatsApp Integration** using @WhiskeySockets/Baileys
- **QR Code Authentication** - Web and terminal scanning support
- **Real-time Communication** with Socket.IO
- **Message Queue Management** with automatic retry mechanism
- **Bulk Message Sending** with rate limiting protection
- **RESTful API** for external integrations
- **Webhook Support** for attendance notifications
- **Modern React Frontend** with TypeScript and Material-UI

### Technical Features
- **Auto-reconnection** when connection is lost
- **Session Persistence** for WhatsApp authentication
- **Rate Limiting** to prevent API abuse
- **Database Integration** with MySQL
- **Comprehensive Logging** with Winston
- **Error Handling** and retry mechanisms
- **CORS Support** for cross-origin requests
- **Security Headers** with Helmet.js

## 🏗️ Architecture

```
wamd_apps/
├── index.js                 # Main application entry point
├── package.json            # Node.js dependencies
├── .env.example           # Environment configuration template
├── src/
│   ├── routes/            # Express.js routes
│   │   ├── api.js         # API endpoints
│   │   ├── qr.js          # QR code routes
│   │   └── webhook.js     # Webhook endpoints
│   ├── services/          # Business logic services
│   │   ├── WhatsAppService.js    # Baileys integration
│   │   ├── DatabaseService.js    # MySQL operations
│   │   ├── MessageQueue.js       # Queue management
│   │   └── WebhookService.js     # Webhook handling
│   ├── middleware/        # Express middleware
│   │   └── RateLimiter.js # Rate limiting
│   └── utils/             # Utility functions
│       └── Logger.js      # Winston logging
├── frontend/              # React TypeScript frontend
│   ├── src/
│   │   ├── components/    # React components
│   │   ├── pages/         # Page components
│   │   ├── contexts/      # React contexts
│   │   └── services/      # API services
│   └── public/            # Static assets
├── public/                # Static files served by Express
└── sessions/              # WhatsApp session storage
```

## 🛠️ Installation

### Prerequisites
- Node.js 16+ 
- MySQL 5.7+
- npm or yarn

### Backend Setup

1. **Clone and navigate to the directory:**
```bash
cd wamd_apps
```

2. **Install dependencies:**
```bash
npm install
```

3. **Configure environment:**
```bash
cp .env.example .env
# Edit .env with your configuration
```

4. **Environment Variables:**
```env
# Server Configuration
PORT=3000
HOST=localhost
NODE_ENV=development

# Database Configuration
DB_HOST=localhost
DB_PORT=3306
DB_USER=root
DB_PASSWORD=your_password
DB_NAME=studentfinger

# WhatsApp Configuration
WA_AUTO_RECONNECT=true
WA_PRINT_QR_IN_TERMINAL=true
WA_MARK_ONLINE_ON_CONNECT=true

# API Configuration
API_KEY=your_secure_api_key_here
WEBHOOK_URL=http://localhost/studentfinger/whatsappintegration/webhook/baileys

# Security
RATE_LIMIT_WINDOW_MS=60000
RATE_LIMIT_MAX_REQUESTS=100
```

5. **Start the backend:**
```bash
npm start
# or for development
npm run dev
```

### Frontend Setup

1. **Navigate to frontend directory:**
```bash
cd frontend
```

2. **Install dependencies:**
```bash
npm install
```

3. **Configure environment:**
```bash
cp .env.example .env
# Edit .env with your configuration
```

4. **Start the frontend:**
```bash
npm start
```

The frontend will be available at `http://localhost:3001` and will proxy API requests to the backend at `http://localhost:3000`.

## 📱 Usage

### 1. Connect WhatsApp Device

1. Open the application in your browser
2. Navigate to "QR Scanner" page
3. Scan the QR code with your WhatsApp mobile app:
   - Open WhatsApp → Settings → Linked Devices
   - Tap "Link a Device"
   - Scan the QR code

### 2. Send Messages

**Single Message:**
- Go to "Send Message" page
- Enter phone number (with country code)
- Type your message
- Click "Send Message"

**Bulk Messages:**
- Go to "Bulk Message" page
- Add contacts manually or upload from file
- Type your message
- Set delay between messages
- Click "Send to X Contacts"

### 3. Monitor Queue

- Go to "Message Queue" page
- View pending, sent, and failed messages
- Retry failed messages
- Clear queue if needed

## 🔌 API Reference

### Authentication
All API requests require the `X-API-Key` header:
```bash
curl -H "X-API-Key: your_secure_api_key_here" http://localhost:3000/api/status
```

### Core Endpoints

#### Get WhatsApp Status
```http
GET /api/status
```

#### Send Single Message
```http
POST /api/send-message
Content-Type: application/json

{
  "to": "+6281234567890",
  "message": "Hello from WhatsApp Gateway!",
  "delay": 0
}
```

#### Send Bulk Messages
```http
POST /api/send-bulk
Content-Type: application/json

{
  "contacts": ["+6281234567890", "+6281234567891"],
  "message": "Bulk message content",
  "delay": 1
}
```

#### Get Queue Statistics
```http
GET /api/queue/stats
```

### Webhook Endpoints

#### Send Attendance Notification
```http
POST /webhook/attendance-notification
Content-Type: application/json

{
  "student_id": "12345",
  "student_name": "John Doe",
  "parent_phone": "+6281234567890",
  "attendance_status": "present",
  "class_name": "Grade 10A",
  "session_name": "Morning Session"
}
```

## 🔧 Configuration

### Database Tables
The application expects these MySQL tables:
- `wa_devices` - Device management
- `wa_messages` - Message queue and history
- `wa_contacts` - Contact management
- `wa_templates` - Message templates
- `wa_logs` - Activity logging

### WhatsApp Configuration
- **Session Storage**: Sessions are stored in `./sessions/` directory
- **Auto-reconnect**: Automatically reconnects when connection is lost
- **QR Code**: Generated every time a new connection is needed
- **Multi-device**: Supports WhatsApp multi-device feature

### Rate Limiting
- **API Endpoints**: 100 requests per minute per IP
- **Message Sending**: Configurable delay between bulk messages
- **Queue Processing**: Configurable interval and retry attempts

## 🚀 Deployment

### Production Setup

1. **Environment Configuration:**
```env
NODE_ENV=production
PORT=3000
HOST=0.0.0.0
```

2. **Process Management:**
```bash
# Using PM2
npm install -g pm2
pm2 start index.js --name "whatsapp-gateway"

# Using systemd
sudo systemctl enable whatsapp-gateway
sudo systemctl start whatsapp-gateway
```

3. **Reverse Proxy (Nginx):**
```nginx
server {
    listen 80;
    server_name your-domain.com;
    
    location / {
        proxy_pass http://localhost:3000;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host $host;
        proxy_cache_bypass $http_upgrade;
    }
}
```

### Docker Deployment

```dockerfile
FROM node:16-alpine
WORKDIR /app
COPY package*.json ./
RUN npm ci --only=production
COPY . .
EXPOSE 3000
CMD ["npm", "start"]
```

## 🔍 Monitoring

### Health Check
```http
GET /health
```

### Logs
- Application logs: `./logs/app.log`
- Error logs: `./logs/error.log`
- Exception logs: `./logs/exceptions.log`

### Socket.IO Events
- `whatsapp_status` - Connection status updates
- `qr_code` - New QR code generated
- `incoming_message` - New message received

## 🤝 Integration

### CodeIgniter 4 Integration
The gateway is designed to integrate with the Student Attendance System:

```php
// Send attendance notification
$response = $this->http->post('http://localhost:3000/webhook/attendance-notification', [
    'json' => [
        'student_id' => $student['id'],
        'student_name' => $student['name'],
        'parent_phone' => $student['parent_phone'],
        'attendance_status' => 'present',
        'class_name' => $student['class'],
        'session_name' => $session['name']
    ],
    'headers' => [
        'X-API-Key' => 'your_secure_api_key_here'
    ]
]);
```

## 📝 License

This project is licensed under the MIT License - see the LICENSE file for details.

## 🆘 Support

For support and questions:
1. Check the logs in `./logs/` directory
2. Verify environment configuration
3. Ensure WhatsApp is properly connected
4. Check database connectivity

## 🔄 Updates

To update the application:
1. Pull latest changes
2. Run `npm install` to update dependencies
3. Restart the application
4. Check logs for any issues
