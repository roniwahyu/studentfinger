const Logger = require('../utils/Logger');

class MessageQueue {
    constructor(databaseService) {
        this.databaseService = databaseService;
        this.whatsappService = null;
        this.isProcessing = false;
        this.processingInterval = null;
        this.intervalMs = parseInt(process.env.QUEUE_PROCESSING_INTERVAL) || 5000;
        this.maxRetryAttempts = parseInt(process.env.MAX_RETRY_ATTEMPTS) || 3;
        this.messageDelay = parseInt(process.env.MESSAGE_DELAY_MS) || 1000;
    }

    setWhatsAppService(whatsappService) {
        this.whatsappService = whatsappService;
    }

    startProcessing() {
        if (this.isProcessing) {
            Logger.warn('Message queue processing is already running');
            return;
        }

        this.isProcessing = true;
        Logger.info('Starting message queue processing', {
            interval: this.intervalMs,
            maxRetryAttempts: this.maxRetryAttempts
        });

        this.processingInterval = setInterval(async () => {
            await this.processQueue();
        }, this.intervalMs);
    }

    stopProcessing() {
        if (!this.isProcessing) {
            return;
        }

        this.isProcessing = false;
        
        if (this.processingInterval) {
            clearInterval(this.processingInterval);
            this.processingInterval = null;
        }

        Logger.info('Message queue processing stopped');
    }

    async processQueue() {
        try {
            if (!this.whatsappService || !this.whatsappService.isConnected()) {
                Logger.debug('WhatsApp not connected, skipping queue processing');
                return;
            }

            const pendingMessages = await this.databaseService.getPendingMessages();
            
            if (pendingMessages.length === 0) {
                Logger.debug('No pending messages in queue');
                return;
            }

            Logger.info(`Processing ${pendingMessages.length} pending messages`);

            for (const message of pendingMessages) {
                await this.processMessage(message);
                
                // Add delay between messages to avoid rate limiting
                if (this.messageDelay > 0) {
                    await this.sleep(this.messageDelay);
                }
            }

        } catch (error) {
            Logger.error('Error processing message queue:', error);
        }
    }

    async processMessage(message) {
        try {
            Logger.debug('Processing message', {
                id: message.id,
                phone: message.phone_number,
                attempt: message.retry_count || 0
            });

            // Check retry limit
            const retryCount = message.retry_count || 0;
            if (retryCount >= this.maxRetryAttempts) {
                Logger.warn('Message exceeded retry limit', {
                    id: message.id,
                    retryCount: retryCount
                });
                
                await this.databaseService.markMessageAsFailed(
                    message.id, 
                    'Exceeded maximum retry attempts'
                );
                return;
            }

            // Send message
            const result = await this.whatsappService.sendMessage(
                message.phone_number,
                message.message
            );

            if (result.success) {
                await this.databaseService.markMessageAsSent(message.id, result);
                Logger.info('Message sent successfully', {
                    id: message.id,
                    messageId: result.messageId
                });
            } else {
                throw new Error('Failed to send message');
            }

        } catch (error) {
            Logger.error('Failed to process message:', {
                id: message.id,
                error: error.message
            });

            // Increment retry count
            const retryCount = (message.retry_count || 0) + 1;
            
            if (retryCount >= this.maxRetryAttempts) {
                await this.databaseService.markMessageAsFailed(message.id, error.message);
            } else {
                // Update retry count for next attempt
                await this.updateRetryCount(message.id, retryCount);
            }
        }
    }

    async updateRetryCount(messageId, retryCount) {
        try {
            const query = `
                UPDATE wa_messages 
                SET retry_count = ?, updated_at = NOW() 
                WHERE id = ?
            `;
            
            await this.databaseService.execute(query, [retryCount, messageId]);
            
        } catch (error) {
            Logger.error('Failed to update retry count:', error);
        }
    }

    async addMessage(phoneNumber, message, options = {}) {
        try {
            const query = `
                INSERT INTO wa_messages (
                    device_id, phone_number, message, status, 
                    created_at, updated_at
                ) VALUES (?, ?, ?, 0, NOW(), NOW())
            `;
            
            const deviceId = await this.databaseService.getDeviceId();
            
            const [result] = await this.databaseService.execute(query, [
                deviceId,
                phoneNumber,
                message
            ]);

            Logger.info('Message added to queue', {
                id: result.insertId,
                phone: phoneNumber
            });

            return result.insertId;

        } catch (error) {
            Logger.error('Failed to add message to queue:', error);
            throw error;
        }
    }

    async addBulkMessages(messages) {
        try {
            const deviceId = await this.databaseService.getDeviceId();
            const values = [];
            const placeholders = [];

            for (const msg of messages) {
                values.push(deviceId, msg.phoneNumber, msg.message);
                placeholders.push('(?, ?, ?, 0, NOW(), NOW())');
            }

            const query = `
                INSERT INTO wa_messages (
                    device_id, phone_number, message, status, 
                    created_at, updated_at
                ) VALUES ${placeholders.join(', ')}
            `;

            const [result] = await this.databaseService.execute(query, values);

            Logger.info('Bulk messages added to queue', {
                count: messages.length,
                firstId: result.insertId
            });

            return result.insertId;

        } catch (error) {
            Logger.error('Failed to add bulk messages to queue:', error);
            throw error;
        }
    }

    async getQueueStats() {
        try {
            const stats = await this.databaseService.getStats();
            
            return {
                ...stats,
                isProcessing: this.isProcessing,
                intervalMs: this.intervalMs,
                maxRetryAttempts: this.maxRetryAttempts
            };

        } catch (error) {
            Logger.error('Failed to get queue stats:', error);
            return {
                totalMessages: 0,
                pendingMessages: 0,
                failedMessages: 0,
                isProcessing: this.isProcessing
            };
        }
    }

    async clearQueue() {
        try {
            const query = 'DELETE FROM wa_messages WHERE status = 0';
            await this.databaseService.execute(query);
            
            Logger.info('Message queue cleared');
            
        } catch (error) {
            Logger.error('Failed to clear queue:', error);
            throw error;
        }
    }

    async retryFailedMessages() {
        try {
            const query = `
                UPDATE wa_messages 
                SET status = 0, retry_count = 0, error_message = NULL, updated_at = NOW() 
                WHERE status = 2
            `;
            
            const [result] = await this.databaseService.execute(query);
            
            Logger.info('Failed messages reset for retry', {
                count: result.affectedRows
            });
            
            return result.affectedRows;

        } catch (error) {
            Logger.error('Failed to retry failed messages:', error);
            throw error;
        }
    }

    sleep(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    isRunning() {
        return this.isProcessing;
    }
}

module.exports = MessageQueue;
