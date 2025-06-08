# Wablas Integration Module for CodeIgniter 4

A comprehensive, pluggable module for integrating with Wablas.com WhatsApp API in CodeIgniter 4 applications.

## Features

### Core Features
- **Complete API Coverage**: Full implementation of Wablas.com API v1 and v2
- **Device Management**: Manage multiple WhatsApp devices
- **Message Sending**: Support for all message types (text, image, document, video, audio, location, list)
- **Bulk Messaging**: Send messages to multiple recipients with personalization
- **Scheduled Messaging**: Schedule messages for future delivery
- **Contact Management**: Organize and manage WhatsApp contacts
- **Auto-Reply System**: Automated responses based on keywords
- **Webhook Handling**: Process incoming messages and status updates
- **Reporting & Analytics**: Comprehensive reporting and statistics
- **Group Messaging**: Send messages to WhatsApp groups
- **File Upload Support**: Upload and manage media files

### Technical Features
- **Pluggable Architecture**: Easy to install, uninstall, and update
- **HMVC Structure**: Follows CodeIgniter 4 HMVC pattern
- **Database Migrations**: Automated database setup
- **Comprehensive Logging**: Track all API calls and activities
- **Error Handling**: Robust error handling and retry mechanisms
- **Rate Limiting**: Respect API rate limits
- **Queue Support**: Background processing for bulk operations
- **Template System**: Reusable message templates
- **Campaign Management**: Organize bulk messaging campaigns

## Requirements

- PHP 8.1 or higher
- CodeIgniter 4.0 or higher
- MySQL 5.7 or higher
- cURL extension
- JSON extension
- OpenSSL extension
- Guzzle HTTP Client (^7.0)

## Installation

### 1. Install Dependencies

```bash
composer require guzzlehttp/guzzle
```

### 2. Copy Module Files

Copy the `WablasIntegration` module to your `app/Modules/` directory.

### 3. Environment Configuration

Add the following to your `.env` file:

```env
# Wablas Configuration
WABLAS_TOKEN=your_wablas_token_here
WABLAS_SECRET_KEY=your_secret_key_here
WABLAS_WEBHOOK_SECRET=your_webhook_secret_here
```

### 4. Run Installation

Visit `/wablas/install` in your browser and follow the installation wizard, or run the installation programmatically:

```php
$module = new \App\Modules\WablasIntegration\WablasIntegrationModule();
$result = $module->install();
```

### 5. Configure Routes

The module routes are automatically loaded. Ensure your main routes file includes module discovery:

```php
// app/Config/Routes.php
$routes->setAutoRoute(true);
```

## Configuration

### Basic Configuration

Create or update `app/Config/WablasIntegration.php`:

```php
<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class WablasIntegration extends BaseConfig
{
    public array $wablas = [
        'base_url' => 'https://wablas.com',
        'token' => env('WABLAS_TOKEN', ''),
        'secret_key' => env('WABLAS_SECRET_KEY', ''),
        'timeout' => 30,
        'verify_ssl' => false
    ];
    
    public array $webhooks = [
        'incoming_url' => '',
        'status_url' => '',
        'device_url' => '',
        'verify_signature' => true
    ];
    
    public array $messages = [
        'default_delay' => 1,
        'max_retries' => 3,
        'retry_delay' => 5,
        'enable_logging' => true
    ];
}
```

## Usage

### Basic Usage

#### Initialize the Service

```php
use App\Modules\WablasIntegration\Services\WablasService;

$wablasService = new WablasService();
```

#### Send a Simple Message

```php
$result = $wablasService->sendMessage(
    deviceId: 1,
    phoneNumber: '6281234567890',
    message: 'Hello from Wablas!'
);

if ($result['success']) {
    echo "Message sent successfully!";
} else {
    echo "Failed to send message: " . $result['error'];
}
```

#### Send Media Message

```php
$result = $wablasService->sendMediaMessage(
    deviceId: 1,
    phoneNumber: '6281234567890',
    mediaUrl: 'https://example.com/image.jpg',
    type: 'image',
    options: ['caption' => 'Check out this image!']
);
```

#### Send Bulk Messages

```php
$recipients = [
    ['phone_number' => '6281234567890', 'name' => 'John Doe'],
    ['phone_number' => '6281234567891', 'name' => 'Jane Smith']
];

$result = $wablasService->sendBulkMessages(
    deviceId: 1,
    recipients: $recipients,
    message: 'Hello {name}, this is a personalized message!'
);
```

#### Schedule a Message

```php
$result = $wablasService->scheduleMessage(
    deviceId: 1,
    phoneNumber: '6281234567890',
    message: 'This is a scheduled message',
    scheduledAt: '2024-12-25 10:00:00'
);
```

### Advanced Usage

#### Using the API Library Directly

```php
use App\Libraries\WablasApi;

$config = [
    'base_url' => 'https://wablas.com',
    'token' => 'your_token',
    'secret_key' => 'your_secret'
];

$api = new WablasApi($config);

// Send message
$response = $api->sendMessage('6281234567890', 'Hello World!');

// Send image
$response = $api->sendImage('6281234567890', 'https://example.com/image.jpg', 'Caption');

// Get device info
$response = $api->getDeviceInfo();

// Check phone numbers
$response = $api->checkPhoneNumbers(['6281234567890', '6281234567891']);
```

#### Working with Models

```php
use App\Modules\WablasIntegration\Models\WablasDeviceModel;
use App\Modules\WablasIntegration\Models\WablasMessageModel;
use App\Modules\WablasIntegration\Models\WablasContactModel;

// Device operations
$deviceModel = new WablasDeviceModel();
$devices = $deviceModel->getActiveDevices();
$device = $deviceModel->getByPhoneNumber('6281234567890');

// Message operations
$messageModel = new WablasMessageModel();
$messages = $messageModel->getByDevice(1);
$conversation = $messageModel->getConversation(1, '6281234567890');

// Contact operations
$contactModel = new WablasContactModel();
$contacts = $contactModel->getActiveContacts();
$contact = $contactModel->getByPhoneNumber('6281234567890');
```

## API Endpoints

### Admin Routes (Protected)

- `GET /wablas/` - Dashboard
- `GET /wablas/devices` - Device management
- `GET /wablas/messages` - Message management
- `GET /wablas/contacts` - Contact management
- `GET /wablas/auto-reply` - Auto-reply management
- `GET /wablas/templates` - Template management
- `GET /wablas/reports` - Reports and analytics
- `GET /wablas/settings` - Module settings

### API Routes (For External Integration)

- `POST /api/wablas/messages/send` - Send message
- `POST /api/wablas/messages/bulk` - Send bulk messages
- `POST /api/wablas/messages/schedule` - Schedule message
- `GET /api/wablas/devices` - Get devices
- `GET /api/wablas/contacts` - Get contacts

### Webhook Routes (Public)

- `POST /wablas/webhook/incoming` - Incoming messages
- `POST /wablas/webhook/status` - Message status updates
- `POST /wablas/webhook/device` - Device status updates

## Database Schema

The module creates the following tables:

- `wablas_devices` - WhatsApp devices
- `wablas_messages` - Message history
- `wablas_contacts` - Contact management
- `wablas_schedules` - Scheduled messages
- `wablas_auto_replies` - Auto-reply rules
- `wablas_webhooks` - Webhook configurations
- `wablas_logs` - Activity logs
- `wablas_templates` - Message templates
- `wablas_groups` - Contact groups
- `wablas_campaigns` - Messaging campaigns

## Events and Hooks

The module fires several events that you can listen to:

```php
// In your EventConfig.php
public array $aliases = [
    'WablasMessageSent' => \App\Modules\WablasIntegration\Events\MessageSent::class,
    'WablasMessageReceived' => \App\Modules\WablasIntegration\Events\MessageReceived::class,
    'WablasDeviceConnected' => \App\Modules\WablasIntegration\Events\DeviceConnected::class,
];
```

## Scheduled Tasks

The module includes scheduled tasks for:

- Processing scheduled messages
- Syncing device status
- Cleaning up old logs
- Processing message queues

Add to your cron job:

```bash
# Process scheduled messages every minute
* * * * * php /path/to/your/app/spark wablas:process-schedules

# Sync device status every 5 minutes
*/5 * * * * php /path/to/your/app/spark wablas:sync-devices

# Clean old logs daily
0 2 * * * php /path/to/your/app/spark wablas:cleanup-logs
```

## Security

- All API endpoints require authentication
- Webhook endpoints can be secured with secret tokens
- Input validation and sanitization
- SQL injection protection
- XSS protection
- Rate limiting support

## Troubleshooting

### Common Issues

1. **Device not connecting**: Check your Wablas token and secret key
2. **Messages not sending**: Verify device status and quota limits
3. **Webhooks not working**: Check webhook URLs and firewall settings
4. **Database errors**: Ensure migrations have been run

### Debug Mode

Enable debug logging in your configuration:

```php
public array $messages = [
    'enable_logging' => true,
    'log_level' => 'debug'
];
```

### Log Files

Check the following log files:

- `writable/logs/wablas-*.log` - Module-specific logs
- `writable/logs/codeigniter-*.log` - General application logs

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

## License

This module is open-source software licensed under the MIT License.

## Support

For support and questions:

- Check the documentation
- Review the logs for error messages
- Create an issue in the repository
- Contact the development team

## Changelog

### Version 1.0.0
- Initial release
- Complete Wablas API integration
- Device management
- Message sending and scheduling
- Contact management
- Auto-reply system
- Webhook handling
- Reporting and analytics
