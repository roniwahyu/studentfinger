const express = require('express');
const router = express.Router();
const Logger = require('../utils/Logger');

// Middleware to check API key
const authenticateAPI = (req, res, next) => {
    const apiKey = req.headers['x-api-key'] || req.query.api_key;
    const validApiKey = process.env.API_KEY;
    
    if (!apiKey || apiKey !== validApiKey) {
        return res.status(401).json({
            error: 'Unauthorized',
            message: 'Invalid or missing API key'
        });
    }
    
    next();
};

// Apply authentication to all API routes
router.use(authenticateAPI);

// Get WhatsApp status
router.get('/status', (req, res) => {
    const whatsappService = req.app.locals.whatsappService;
    
    res.json({
        connected: whatsappService?.isConnected() || false,
        user: whatsappService?.getUser() || null,
        qrCode: whatsappService?.getQRCode() || null,
        timestamp: new Date().toISOString()
    });
});

// Send single message
router.post('/send-message', async (req, res) => {
    try {
        const { to, message, delay } = req.body;
        
        if (!to || !message) {
            return res.status(400).json({
                error: 'Bad Request',
                message: 'Missing required fields: to, message'
            });
        }
        
        const whatsappService = req.app.locals.whatsappService;
        const messageQueue = req.app.locals.messageQueue;
        
        if (!whatsappService?.isConnected()) {
            // Add to queue if not connected
            const messageId = await messageQueue.addMessage(to, message);
            
            return res.json({
                success: true,
                message: 'Message queued (WhatsApp not connected)',
                messageId: messageId,
                queued: true
            });
        }
        
        // Send immediately if connected
        if (delay && delay > 0) {
            setTimeout(async () => {
                try {
                    await whatsappService.sendMessage(to, message);
                } catch (error) {
                    Logger.error('Delayed message send failed:', error);
                }
            }, delay * 1000);
            
            res.json({
                success: true,
                message: 'Message scheduled for delayed sending',
                delay: delay
            });
        } else {
            const result = await whatsappService.sendMessage(to, message);
            
            res.json({
                success: true,
                message: 'Message sent successfully',
                messageId: result.messageId,
                timestamp: result.timestamp
            });
        }
        
    } catch (error) {
        Logger.error('Send message API error:', error);
        res.status(500).json({
            error: 'Internal Server Error',
            message: error.message
        });
    }
});

// Send bulk messages
router.post('/send-bulk', async (req, res) => {
    try {
        const { contacts, message, delay } = req.body;
        
        if (!contacts || !Array.isArray(contacts) || !message) {
            return res.status(400).json({
                error: 'Bad Request',
                message: 'Missing required fields: contacts (array), message'
            });
        }
        
        const messageQueue = req.app.locals.messageQueue;
        const whatsappService = req.app.locals.whatsappService;
        
        const messages = contacts.map(contact => ({
            phoneNumber: typeof contact === 'string' ? contact : contact.phone_number,
            message: message
        }));
        
        if (!whatsappService?.isConnected()) {
            // Add all to queue if not connected
            const firstMessageId = await messageQueue.addBulkMessages(messages);
            
            return res.json({
                success: true,
                message: 'Messages queued (WhatsApp not connected)',
                count: messages.length,
                firstMessageId: firstMessageId,
                queued: true
            });
        }
        
        // Send immediately if connected
        const results = [];
        const messageDelay = delay || 1; // Default 1 second delay between messages
        
        for (let i = 0; i < messages.length; i++) {
            try {
                if (i > 0) {
                    await new Promise(resolve => setTimeout(resolve, messageDelay * 1000));
                }
                
                const result = await whatsappService.sendMessage(
                    messages[i].phoneNumber, 
                    messages[i].message
                );
                
                results.push({
                    phoneNumber: messages[i].phoneNumber,
                    success: true,
                    messageId: result.messageId
                });
                
            } catch (error) {
                results.push({
                    phoneNumber: messages[i].phoneNumber,
                    success: false,
                    error: error.message
                });
            }
        }
        
        const successCount = results.filter(r => r.success).length;
        
        res.json({
            success: true,
            message: 'Bulk messages processed',
            total: messages.length,
            successful: successCount,
            failed: messages.length - successCount,
            results: results
        });
        
    } catch (error) {
        Logger.error('Send bulk messages API error:', error);
        res.status(500).json({
            error: 'Internal Server Error',
            message: error.message
        });
    }
});

// Get queue statistics
router.get('/queue/stats', async (req, res) => {
    try {
        const messageQueue = req.app.locals.messageQueue;
        const stats = await messageQueue.getQueueStats();
        
        res.json({
            success: true,
            data: stats
        });
        
    } catch (error) {
        Logger.error('Queue stats API error:', error);
        res.status(500).json({
            error: 'Internal Server Error',
            message: error.message
        });
    }
});

// Clear message queue
router.post('/queue/clear', async (req, res) => {
    try {
        const messageQueue = req.app.locals.messageQueue;
        await messageQueue.clearQueue();
        
        res.json({
            success: true,
            message: 'Message queue cleared'
        });
        
    } catch (error) {
        Logger.error('Clear queue API error:', error);
        res.status(500).json({
            error: 'Internal Server Error',
            message: error.message
        });
    }
});

// Retry failed messages
router.post('/queue/retry-failed', async (req, res) => {
    try {
        const messageQueue = req.app.locals.messageQueue;
        const count = await messageQueue.retryFailedMessages();
        
        res.json({
            success: true,
            message: 'Failed messages reset for retry',
            count: count
        });
        
    } catch (error) {
        Logger.error('Retry failed messages API error:', error);
        res.status(500).json({
            error: 'Internal Server Error',
            message: error.message
        });
    }
});

// Get contacts
router.get('/contacts', async (req, res) => {
    try {
        const databaseService = req.app.locals.databaseService;
        const contacts = await databaseService.getContacts();
        
        res.json({
            success: true,
            data: contacts
        });
        
    } catch (error) {
        Logger.error('Get contacts API error:', error);
        res.status(500).json({
            error: 'Internal Server Error',
            message: error.message
        });
    }
});

// Get templates
router.get('/templates', async (req, res) => {
    try {
        const databaseService = req.app.locals.databaseService;
        const templates = await databaseService.getTemplates();
        
        res.json({
            success: true,
            data: templates
        });
        
    } catch (error) {
        Logger.error('Get templates API error:', error);
        res.status(500).json({
            error: 'Internal Server Error',
            message: error.message
        });
    }
});

// Disconnect WhatsApp
router.post('/disconnect', async (req, res) => {
    try {
        const whatsappService = req.app.locals.whatsappService;
        await whatsappService.disconnect();
        
        res.json({
            success: true,
            message: 'WhatsApp disconnected successfully'
        });
        
    } catch (error) {
        Logger.error('Disconnect API error:', error);
        res.status(500).json({
            error: 'Internal Server Error',
            message: error.message
        });
    }
});

// Restart WhatsApp connection
router.post('/restart', async (req, res) => {
    try {
        const whatsappService = req.app.locals.whatsappService;
        
        await whatsappService.disconnect();
        await new Promise(resolve => setTimeout(resolve, 2000)); // Wait 2 seconds
        await whatsappService.initialize();
        
        res.json({
            success: true,
            message: 'WhatsApp connection restarted'
        });
        
    } catch (error) {
        Logger.error('Restart API error:', error);
        res.status(500).json({
            error: 'Internal Server Error',
            message: error.message
        });
    }
});

module.exports = router;
