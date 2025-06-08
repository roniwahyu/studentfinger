const mysql = require('mysql2/promise');
const Logger = require('../utils/Logger');

class DatabaseService {
    constructor() {
        this.connection = null;
        this.config = {
            host: process.env.DB_HOST || 'localhost',
            port: process.env.DB_PORT || 3306,
            user: process.env.DB_USER || 'root',
            password: process.env.DB_PASSWORD || '',
            database: process.env.DB_NAME || 'studentfinger',
            waitForConnections: true,
            connectionLimit: 10,
            queueLimit: 0,
            acquireTimeout: 60000,
            timeout: 60000
        };
    }

    async connect() {
        try {
            this.connection = await mysql.createPool(this.config);
            
            // Test connection
            const [rows] = await this.connection.execute('SELECT 1 as test');
            Logger.info('Database connected successfully');
            
            return this.connection;
        } catch (error) {
            Logger.error('Failed to connect to database:', error);
            throw error;
        }
    }

    async disconnect() {
        try {
            if (this.connection) {
                await this.connection.end();
                this.connection = null;
                Logger.info('Database disconnected successfully');
            }
        } catch (error) {
            Logger.error('Failed to disconnect from database:', error);
        }
    }

    async updateDeviceStatus(deviceId, status) {
        try {
            const query = `
                UPDATE wa_devices 
                SET device_status = ?, updated_at = NOW() 
                WHERE device_name = 'Baileys Gateway' OR id = 1
            `;
            
            const statusValue = status === 'connected' ? 1 : 0;
            await this.connection.execute(query, [statusValue]);
            
            Logger.info('Device status updated', { deviceId, status });
        } catch (error) {
            Logger.error('Failed to update device status:', error);
        }
    }

    async storeIncomingMessage(messageData) {
        try {
            const query = `
                INSERT INTO wa_messages (
                    device_id, phone_number, message, status, 
                    api_response, created_at
                ) VALUES (?, ?, ?, ?, ?, NOW())
            `;
            
            const deviceId = await this.getDeviceId();
            const apiResponse = JSON.stringify({
                messageId: messageData.id,
                type: messageData.type,
                timestamp: messageData.timestamp
            });
            
            await this.connection.execute(query, [
                deviceId,
                messageData.from.replace('@s.whatsapp.net', ''),
                messageData.message,
                3, // Status: Received
                apiResponse
            ]);
            
            Logger.info('Incoming message stored', { messageId: messageData.id });
        } catch (error) {
            Logger.error('Failed to store incoming message:', error);
        }
    }

    async updateMessageStatus(messageId, status) {
        try {
            const query = `
                UPDATE wa_messages 
                SET status = ?, updated_at = NOW() 
                WHERE JSON_EXTRACT(api_response, '$.messageId') = ?
            `;
            
            let statusValue = 0; // Pending
            if (status.status === 'DELIVERED') statusValue = 1; // Sent
            if (status.status === 'READ') statusValue = 1; // Sent
            if (status.status === 'FAILED') statusValue = 2; // Failed
            
            await this.connection.execute(query, [statusValue, messageId]);
            
            Logger.debug('Message status updated', { messageId, status: statusValue });
        } catch (error) {
            Logger.error('Failed to update message status:', error);
        }
    }

    async getDeviceId() {
        try {
            const query = `
                SELECT id FROM wa_devices 
                WHERE device_name = 'Baileys Gateway' 
                ORDER BY id DESC LIMIT 1
            `;
            
            const [rows] = await this.connection.execute(query);
            
            if (rows.length > 0) {
                return rows[0].id;
            }
            
            // Create device if not exists
            return await this.createDevice();
        } catch (error) {
            Logger.error('Failed to get device ID:', error);
            return 1; // Default device ID
        }
    }

    async createDevice() {
        try {
            const query = `
                INSERT INTO wa_devices (
                    device_name, device_token, device_status, 
                    api_url, created_at, updated_at
                ) VALUES (?, ?, ?, ?, NOW(), NOW())
            `;
            
            const [result] = await this.connection.execute(query, [
                'Baileys Gateway',
                'baileys_' + Date.now(),
                1, // Active
                `http://localhost:${process.env.PORT || 3000}`
            ]);
            
            Logger.info('Device created', { deviceId: result.insertId });
            return result.insertId;
        } catch (error) {
            Logger.error('Failed to create device:', error);
            return 1; // Default device ID
        }
    }

    async getPendingMessages() {
        try {
            const query = `
                SELECT * FROM wa_messages 
                WHERE status = 0 
                ORDER BY created_at ASC 
                LIMIT 10
            `;
            
            const [rows] = await this.connection.execute(query);
            return rows;
        } catch (error) {
            Logger.error('Failed to get pending messages:', error);
            return [];
        }
    }

    async markMessageAsSent(messageId, apiResponse = null) {
        try {
            const query = `
                UPDATE wa_messages 
                SET status = 1, sent_at = NOW(), api_response = ?, updated_at = NOW() 
                WHERE id = ?
            `;
            
            await this.connection.execute(query, [
                apiResponse ? JSON.stringify(apiResponse) : null,
                messageId
            ]);
            
            Logger.debug('Message marked as sent', { messageId });
        } catch (error) {
            Logger.error('Failed to mark message as sent:', error);
        }
    }

    async markMessageAsFailed(messageId, errorMessage) {
        try {
            const query = `
                UPDATE wa_messages 
                SET status = 2, error_message = ?, updated_at = NOW() 
                WHERE id = ?
            `;
            
            await this.connection.execute(query, [errorMessage, messageId]);
            
            Logger.debug('Message marked as failed', { messageId, errorMessage });
        } catch (error) {
            Logger.error('Failed to mark message as failed:', error);
        }
    }

    async getContacts() {
        try {
            const query = `
                SELECT * FROM wa_contacts 
                ORDER BY contact_name ASC
            `;
            
            const [rows] = await this.connection.execute(query);
            return rows;
        } catch (error) {
            Logger.error('Failed to get contacts:', error);
            return [];
        }
    }

    async getTemplates() {
        try {
            const query = `
                SELECT * FROM wa_templates 
                ORDER BY template_name ASC
            `;
            
            const [rows] = await this.connection.execute(query);
            return rows;
        } catch (error) {
            Logger.error('Failed to get templates:', error);
            return [];
        }
    }

    async logActivity(action, data = {}) {
        try {
            const query = `
                INSERT INTO wa_logs (
                    device_id, action, data, created_at
                ) VALUES (?, ?, ?, NOW())
            `;
            
            const deviceId = await this.getDeviceId();
            
            await this.connection.execute(query, [
                deviceId,
                action,
                JSON.stringify(data)
            ]);
            
            Logger.debug('Activity logged', { action });
        } catch (error) {
            Logger.error('Failed to log activity:', error);
        }
    }

    async getStats() {
        try {
            const queries = {
                totalMessages: 'SELECT COUNT(*) as count FROM wa_messages',
                sentToday: `
                    SELECT COUNT(*) as count FROM wa_messages 
                    WHERE status = 1 AND DATE(created_at) = CURDATE()
                `,
                pendingMessages: 'SELECT COUNT(*) as count FROM wa_messages WHERE status = 0',
                failedMessages: 'SELECT COUNT(*) as count FROM wa_messages WHERE status = 2',
                totalContacts: 'SELECT COUNT(*) as count FROM wa_contacts'
            };

            const stats = {};
            
            for (const [key, query] of Object.entries(queries)) {
                const [rows] = await this.connection.execute(query);
                stats[key] = rows[0].count;
            }
            
            return stats;
        } catch (error) {
            Logger.error('Failed to get stats:', error);
            return {
                totalMessages: 0,
                sentToday: 0,
                pendingMessages: 0,
                failedMessages: 0,
                totalContacts: 0
            };
        }
    }

    async execute(query, params = []) {
        try {
            return await this.connection.execute(query, params);
        } catch (error) {
            Logger.error('Database query failed:', { query, error });
            throw error;
        }
    }

    async query(sql, params = []) {
        try {
            return await this.connection.query(sql, params);
        } catch (error) {
            Logger.error('Database query failed:', { sql, error });
            throw error;
        }
    }
}

module.exports = DatabaseService;
