const axios = require('axios');
const Logger = require('../utils/Logger');

class WebhookService {
    constructor(databaseService) {
        this.databaseService = databaseService;
        this.webhookUrl = process.env.WEBHOOK_URL;
        this.ciBaseUrl = process.env.CI_BASE_URL;
        this.ciApiEndpoint = process.env.CI_API_ENDPOINT || '/wa/webhook';
    }

    async sendStatusUpdate(status, user = null) {
        try {
            const payload = {
                event: 'status_update',
                status: status,
                user: user,
                timestamp: new Date().toISOString(),
                device: 'baileys_gateway'
            };

            await this.sendWebhook(payload);
            
            // Log activity
            if (this.databaseService) {
                await this.databaseService.logActivity('status_update', payload);
            }

        } catch (error) {
            Logger.error('Failed to send status update webhook:', error);
        }
    }

    async sendIncomingMessage(messageData) {
        try {
            const payload = {
                event: 'incoming_message',
                message: messageData,
                timestamp: new Date().toISOString(),
                device: 'baileys_gateway'
            };

            await this.sendWebhook(payload);
            
            // Log activity
            if (this.databaseService) {
                await this.databaseService.logActivity('incoming_message', payload);
            }

        } catch (error) {
            Logger.error('Failed to send incoming message webhook:', error);
        }
    }

    async sendMessageUpdate(updateData) {
        try {
            const payload = {
                event: 'message_update',
                update: updateData,
                timestamp: new Date().toISOString(),
                device: 'baileys_gateway'
            };

            await this.sendWebhook(payload);
            
            // Log activity
            if (this.databaseService) {
                await this.databaseService.logActivity('message_update', payload);
            }

        } catch (error) {
            Logger.error('Failed to send message update webhook:', error);
        }
    }

    async sendWebhook(payload) {
        try {
            const urls = [];
            
            // Add configured webhook URL
            if (this.webhookUrl) {
                urls.push(this.webhookUrl);
            }
            
            // Add CodeIgniter webhook URL
            if (this.ciBaseUrl && this.ciApiEndpoint) {
                urls.push(this.ciBaseUrl + this.ciApiEndpoint);
            }

            // Send to all webhook URLs
            const promises = urls.map(url => this.sendToUrl(url, payload));
            await Promise.allSettled(promises);

        } catch (error) {
            Logger.error('Failed to send webhook:', error);
        }
    }

    async sendToUrl(url, payload) {
        try {
            const response = await axios.post(url, payload, {
                timeout: 10000,
                headers: {
                    'Content-Type': 'application/json',
                    'User-Agent': 'WhatsApp-Baileys-Gateway/1.0'
                }
            });

            Logger.debug('Webhook sent successfully', {
                url: url,
                status: response.status,
                event: payload.event
            });

            return response.data;

        } catch (error) {
            Logger.error('Failed to send webhook to URL:', {
                url: url,
                error: error.message,
                event: payload.event
            });
            throw error;
        }
    }

    async notifyAttendance(attendanceData) {
        try {
            const payload = {
                event: 'attendance_notification',
                attendance: attendanceData,
                timestamp: new Date().toISOString(),
                device: 'baileys_gateway'
            };

            await this.sendWebhook(payload);
            
            // Log activity
            if (this.databaseService) {
                await this.databaseService.logActivity('attendance_notification', payload);
            }

        } catch (error) {
            Logger.error('Failed to send attendance notification webhook:', error);
        }
    }

    async notifyError(errorData) {
        try {
            const payload = {
                event: 'error',
                error: {
                    message: errorData.message,
                    stack: errorData.stack,
                    timestamp: new Date().toISOString()
                },
                device: 'baileys_gateway'
            };

            await this.sendWebhook(payload);
            
            // Log activity
            if (this.databaseService) {
                await this.databaseService.logActivity('error_notification', payload);
            }

        } catch (error) {
            Logger.error('Failed to send error notification webhook:', error);
        }
    }

    setWebhookUrl(url) {
        this.webhookUrl = url;
        Logger.info('Webhook URL updated', { url });
    }

    getWebhookUrl() {
        return this.webhookUrl;
    }
}

module.exports = WebhookService;
