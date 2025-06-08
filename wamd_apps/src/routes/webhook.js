const express = require('express');
const router = express.Router();
const Logger = require('../utils/Logger');

// Middleware to validate webhook requests
const validateWebhook = (req, res, next) => {
    const webhookToken = req.headers['x-webhook-token'] || req.query.token;
    const validToken = process.env.WEBHOOK_TOKEN || 'default_webhook_token';
    
    if (!webhookToken || webhookToken !== validToken) {
        return res.status(401).json({
            error: 'Unauthorized',
            message: 'Invalid or missing webhook token'
        });
    }
    
    next();
};

// Health check for webhook endpoint
router.get('/health', (req, res) => {
    res.json({
        status: 'ok',
        service: 'WhatsApp Webhook',
        timestamp: new Date().toISOString()
    });
});

// Receive incoming messages from external systems
router.post('/incoming', validateWebhook, async (req, res) => {
    try {
        const { from, message, type, timestamp } = req.body;
        
        if (!from || !message) {
            return res.status(400).json({
                error: 'Bad Request',
                message: 'Missing required fields: from, message'
            });
        }
        
        const messageData = {
            from: from,
            message: message,
            type: type || 'text',
            timestamp: timestamp || new Date().toISOString(),
            source: 'webhook'
        };
        
        Logger.info('Webhook incoming message:', messageData);
        
        // Store in database
        const databaseService = req.app.locals.databaseService;
        if (databaseService) {
            await databaseService.storeIncomingMessage(messageData);
        }
        
        // Emit via Socket.IO
        const io = req.app.locals.io;
        if (io) {
            io.emit('incoming_message', messageData);
        }
        
        res.json({
            success: true,
            message: 'Message received and processed',
            messageId: messageData.id
        });
        
    } catch (error) {
        Logger.error('Webhook incoming message error:', error);
        res.status(500).json({
            error: 'Internal Server Error',
            message: error.message
        });
    }
});

// Send message via webhook
router.post('/send', validateWebhook, async (req, res) => {
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
                    Logger.error('Delayed webhook message send failed:', error);
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
        Logger.error('Webhook send message error:', error);
        res.status(500).json({
            error: 'Internal Server Error',
            message: error.message
        });
    }
});

// Bulk send messages via webhook
router.post('/send-bulk', validateWebhook, async (req, res) => {
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
        Logger.error('Webhook bulk send error:', error);
        res.status(500).json({
            error: 'Internal Server Error',
            message: error.message
        });
    }
});

// Get WhatsApp status via webhook
router.get('/status', validateWebhook, (req, res) => {
    try {
        const whatsappService = req.app.locals.whatsappService;
        
        res.json({
            success: true,
            connected: whatsappService?.isConnected() || false,
            user: whatsappService?.getUser() || null,
            timestamp: new Date().toISOString()
        });
        
    } catch (error) {
        Logger.error('Webhook status error:', error);
        res.status(500).json({
            error: 'Internal Server Error',
            message: error.message
        });
    }
});

// Attendance notification webhook (specific for student attendance system)
router.post('/attendance-notification', validateWebhook, async (req, res) => {
    try {
        const { 
            student_id, 
            student_name, 
            parent_phone, 
            attendance_status, 
            timestamp, 
            class_name,
            session_name 
        } = req.body;
        
        if (!student_id || !student_name || !parent_phone || !attendance_status) {
            return res.status(400).json({
                error: 'Bad Request',
                message: 'Missing required fields: student_id, student_name, parent_phone, attendance_status'
            });
        }
        
        // Create attendance notification message
        const statusText = attendance_status === 'present' ? 'hadir' : 'tidak hadir';
        const timeStr = timestamp ? new Date(timestamp).toLocaleString('id-ID') : new Date().toLocaleString('id-ID');
        
        const message = `
🏫 *Notifikasi Kehadiran Siswa*

👤 *Nama:* ${student_name}
📚 *Kelas:* ${class_name || 'N/A'}
📅 *Sesi:* ${session_name || 'N/A'}
⏰ *Waktu:* ${timeStr}
📊 *Status:* ${statusText.toUpperCase()}

${attendance_status === 'present' ? '✅' : '❌'} Siswa ${statusText} pada sesi pembelajaran hari ini.

_Sistem Absensi Digital - Student Finger_
        `.trim();
        
        const whatsappService = req.app.locals.whatsappService;
        const messageQueue = req.app.locals.messageQueue;
        
        if (!whatsappService?.isConnected()) {
            // Add to queue if not connected
            const messageId = await messageQueue.addMessage(parent_phone, message);
            
            return res.json({
                success: true,
                message: 'Attendance notification queued (WhatsApp not connected)',
                messageId: messageId,
                queued: true
            });
        }
        
        // Send immediately if connected
        const result = await whatsappService.sendMessage(parent_phone, message);
        
        // Log attendance notification
        Logger.info('Attendance notification sent:', {
            student_id,
            student_name,
            parent_phone,
            attendance_status,
            messageId: result.messageId
        });
        
        res.json({
            success: true,
            message: 'Attendance notification sent successfully',
            messageId: result.messageId,
            timestamp: result.timestamp
        });
        
    } catch (error) {
        Logger.error('Attendance notification webhook error:', error);
        res.status(500).json({
            error: 'Internal Server Error',
            message: error.message
        });
    }
});

// Test webhook endpoint
router.post('/test', (req, res) => {
    Logger.info('Webhook test received:', req.body);
    
    res.json({
        success: true,
        message: 'Webhook test successful',
        received_data: req.body,
        timestamp: new Date().toISOString()
    });
});

module.exports = router;
