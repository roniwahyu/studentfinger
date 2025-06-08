const express = require('express');
const cors = require('cors');
const bodyParser = require('body-parser');
const { createServer } = require('http');
const { Server } = require('socket.io');
const helmet = require('helmet');
require('dotenv').config();

const WhatsAppService = require('./src/services/WhatsAppService');
const DatabaseService = require('./src/services/DatabaseService');
const WebhookService = require('./src/services/WebhookService');
const MessageQueue = require('./src/services/MessageQueue');
const Logger = require('./src/utils/Logger');
const RateLimiter = require('./src/middleware/RateLimiter');

// Routes
const apiRoutes = require('./src/routes/api');
const webhookRoutes = require('./src/routes/webhook');
const qrRoutes = require('./src/routes/qr');

class WhatsAppGateway {
    constructor() {
        this.app = express();
        this.server = createServer(this.app);
        this.io = new Server(this.server, {
            cors: {
                origin: "*",
                methods: ["GET", "POST"]
            }
        });
        
        this.port = process.env.PORT || 3000;
        this.host = process.env.HOST || 'localhost';
        
        this.whatsappService = null;
        this.databaseService = null;
        this.webhookService = null;
        this.messageQueue = null;
        
        this.setupMiddleware();
        this.setupRoutes();
        this.setupSocketIO();
    }

    setupMiddleware() {
        // Security middleware
        this.app.use(helmet());
        
        // CORS
        this.app.use(cors({
            origin: process.env.NODE_ENV === 'production' 
                ? [process.env.CI_BASE_URL] 
                : true,
            credentials: true
        }));

        // Body parsing
        this.app.use(bodyParser.json({ limit: '10mb' }));
        this.app.use(bodyParser.urlencoded({ extended: true, limit: '10mb' }));

        // Rate limiting
        this.app.use('/api/', RateLimiter);

        // Static files
        this.app.use('/public', express.static('public'));

        // Request logging
        this.app.use((req, res, next) => {
            Logger.info(`${req.method} ${req.path}`, {
                ip: req.ip,
                userAgent: req.get('User-Agent')
            });
            next();
        });
    }

    setupRoutes() {
        // Health check
        this.app.get('/health', (req, res) => {
            res.json({
                status: 'ok',
                timestamp: new Date().toISOString(),
                uptime: process.uptime(),
                whatsapp_connected: this.whatsappService?.isConnected() || false
            });
        });

        // API routes
        this.app.use('/api', apiRoutes);
        this.app.use('/webhook', webhookRoutes);
        this.app.use('/qr', qrRoutes);

        // Root route
        this.app.get('/', (req, res) => {
            res.json({
                name: 'WhatsApp Multi-Device Gateway',
                version: '1.0.0',
                description: 'WhatsApp Gateway for Student Attendance System',
                endpoints: {
                    health: '/health',
                    api: '/api',
                    webhook: '/webhook',
                    qr: '/qr'
                }
            });
        });

        // 404 handler
        this.app.use('*', (req, res) => {
            res.status(404).json({
                error: 'Endpoint not found',
                path: req.originalUrl
            });
        });

        // Error handler
        this.app.use((err, req, res, next) => {
            Logger.error('Express error:', err);
            res.status(500).json({
                error: 'Internal server error',
                message: process.env.NODE_ENV === 'development' ? err.message : 'Something went wrong'
            });
        });
    }

    setupSocketIO() {
        this.io.on('connection', (socket) => {
            Logger.info('Client connected to Socket.IO', { socketId: socket.id });

            socket.on('disconnect', () => {
                Logger.info('Client disconnected from Socket.IO', { socketId: socket.id });
            });

            // Send current WhatsApp status
            socket.emit('whatsapp_status', {
                connected: this.whatsappService?.isConnected() || false,
                user: this.whatsappService?.getUser() || null
            });
        });
    }

    async initialize() {
        try {
            Logger.info('Initializing WhatsApp Gateway...');

            // Initialize database service
            this.databaseService = new DatabaseService();
            await this.databaseService.connect();
            Logger.info('Database service initialized');

            // Initialize webhook service
            this.webhookService = new WebhookService(this.databaseService);
            Logger.info('Webhook service initialized');

            // Initialize message queue
            this.messageQueue = new MessageQueue(this.databaseService);
            Logger.info('Message queue initialized');

            // Initialize WhatsApp service
            this.whatsappService = new WhatsAppService({
                databaseService: this.databaseService,
                webhookService: this.webhookService,
                messageQueue: this.messageQueue,
                io: this.io
            });

            await this.whatsappService.initialize();
            Logger.info('WhatsApp service initialized');

            // Make services available to routes
            this.app.locals.whatsappService = this.whatsappService;
            this.app.locals.databaseService = this.databaseService;
            this.app.locals.webhookService = this.webhookService;
            this.app.locals.messageQueue = this.messageQueue;

            Logger.info('WhatsApp Gateway initialized successfully');

        } catch (error) {
            Logger.error('Failed to initialize WhatsApp Gateway:', error);
            throw error;
        }
    }

    async start() {
        try {
            await this.initialize();

            this.server.listen(this.port, this.host, () => {
                Logger.info(`WhatsApp Gateway server running on http://${this.host}:${this.port}`);
                console.log(`
╔══════════════════════════════════════════════════════════════╗
║                WhatsApp Multi-Device Gateway                 ║
║                                                              ║
║  Server: http://${this.host}:${this.port}                              ║
║  Health: http://${this.host}:${this.port}/health                       ║
║  QR Code: http://${this.host}:${this.port}/qr                          ║
║                                                              ║
║  Status: Ready to connect WhatsApp device                   ║
╚══════════════════════════════════════════════════════════════╝
                `);
            });

            // Graceful shutdown
            process.on('SIGINT', () => this.shutdown());
            process.on('SIGTERM', () => this.shutdown());

        } catch (error) {
            Logger.error('Failed to start WhatsApp Gateway:', error);
            process.exit(1);
        }
    }

    async shutdown() {
        Logger.info('Shutting down WhatsApp Gateway...');

        try {
            // Close WhatsApp connection
            if (this.whatsappService) {
                await this.whatsappService.disconnect();
            }

            // Close database connection
            if (this.databaseService) {
                await this.databaseService.disconnect();
            }

            // Close server
            this.server.close(() => {
                Logger.info('WhatsApp Gateway shut down successfully');
                process.exit(0);
            });

        } catch (error) {
            Logger.error('Error during shutdown:', error);
            process.exit(1);
        }
    }
}

// Start the gateway
const gateway = new WhatsAppGateway();
gateway.start().catch(error => {
    console.error('Failed to start WhatsApp Gateway:', error);
    process.exit(1);
});

module.exports = WhatsAppGateway;
