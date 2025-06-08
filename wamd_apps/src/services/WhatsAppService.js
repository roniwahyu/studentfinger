const { 
    default: makeWASocket, 
    DisconnectReason, 
    useMultiFileAuthState,
    fetchLatestBaileysVersion,
    makeCacheableSignalKeyStore,
    Browsers
} = require('@whiskeysockets/baileys');
const qrcode = require('qrcode-terminal');
const QRCode = require('qrcode');
const fs = require('fs');
const path = require('path');
const Logger = require('../utils/Logger');

class WhatsAppService {
    constructor(options = {}) {
        this.databaseService = options.databaseService;
        this.webhookService = options.webhookService;
        this.messageQueue = options.messageQueue;
        this.io = options.io;
        
        this.sock = null;
        this.qrCode = null;
        this.isReady = false;
        this.user = null;
        this.sessionPath = path.join(__dirname, '../../sessions');
        
        // Ensure sessions directory exists
        if (!fs.existsSync(this.sessionPath)) {
            fs.mkdirSync(this.sessionPath, { recursive: true });
        }
    }

    async initialize() {
        try {
            Logger.info('Initializing WhatsApp service...');
            
            const { version, isLatest } = await fetchLatestBaileysVersion();
            Logger.info(`Using Baileys version: ${version}, Latest: ${isLatest}`);

            await this.startConnection();
            
        } catch (error) {
            Logger.error('Failed to initialize WhatsApp service:', error);
            throw error;
        }
    }

    async startConnection() {
        try {
            const { state, saveCreds } = await useMultiFileAuthState(this.sessionPath);
            
            this.sock = makeWASocket({
                auth: {
                    creds: state.creds,
                    keys: makeCacheableSignalKeyStore(state.keys, Logger)
                },
                printQRInTerminal: process.env.WA_PRINT_QR_IN_TERMINAL === 'true',
                browser: Browsers.macOS('Desktop'),
                markOnlineOnConnect: process.env.WA_MARK_ONLINE_ON_CONNECT === 'true',
                generateHighQualityLinkPreview: true,
                syncFullHistory: false,
                defaultQueryTimeoutMs: 60000
            });

            this.setupEventHandlers(saveCreds);
            
        } catch (error) {
            Logger.error('Failed to start WhatsApp connection:', error);
            throw error;
        }
    }

    setupEventHandlers(saveCreds) {
        // Connection updates
        this.sock.ev.on('connection.update', async (update) => {
            const { connection, lastDisconnect, qr } = update;
            
            if (qr) {
                await this.handleQRCode(qr);
            }
            
            if (connection === 'close') {
                await this.handleDisconnection(lastDisconnect);
            } else if (connection === 'open') {
                await this.handleConnection();
            }
        });

        // Credentials update
        this.sock.ev.on('creds.update', saveCreds);

        // Messages
        this.sock.ev.on('messages.upsert', async (m) => {
            await this.handleIncomingMessages(m);
        });

        // Message updates (read, delivered, etc.)
        this.sock.ev.on('messages.update', async (updates) => {
            await this.handleMessageUpdates(updates);
        });

        // Presence updates
        this.sock.ev.on('presence.update', async (update) => {
            Logger.debug('Presence update:', update);
        });

        // Groups updates
        this.sock.ev.on('groups.update', async (updates) => {
            Logger.debug('Groups update:', updates);
        });

        // Contacts update
        this.sock.ev.on('contacts.update', async (updates) => {
            Logger.debug('Contacts update:', updates);
        });
    }

    async handleQRCode(qr) {
        try {
            Logger.info('QR Code received, generating...');
            
            // Print QR in terminal if enabled
            if (process.env.WA_PRINT_QR_IN_TERMINAL === 'true') {
                qrcode.generate(qr, { small: true });
            }
            
            // Generate QR code image
            this.qrCode = await QRCode.toDataURL(qr);
            
            // Save QR code to file
            const qrPath = path.join(__dirname, '../../public/qr.png');
            const qrBuffer = await QRCode.toBuffer(qr);
            fs.writeFileSync(qrPath, qrBuffer);
            
            // Emit QR code via Socket.IO
            if (this.io) {
                this.io.emit('qr_code', {
                    qr: this.qrCode,
                    timestamp: new Date().toISOString()
                });
            }
            
            Logger.info('QR Code generated and saved');
            
        } catch (error) {
            Logger.error('Failed to handle QR code:', error);
        }
    }

    async handleConnection() {
        try {
            this.isReady = true;
            this.user = this.sock.user;
            
            Logger.info('WhatsApp connected successfully', {
                user: this.user?.id,
                name: this.user?.name
            });

            // Clear QR code
            this.qrCode = null;
            const qrPath = path.join(__dirname, '../../public/qr.png');
            if (fs.existsSync(qrPath)) {
                fs.unlinkSync(qrPath);
            }

            // Emit connection status via Socket.IO
            if (this.io) {
                this.io.emit('whatsapp_status', {
                    connected: true,
                    user: this.user,
                    timestamp: new Date().toISOString()
                });
            }

            // Update database
            if (this.databaseService) {
                await this.databaseService.updateDeviceStatus(this.user.id, 'connected');
            }

            // Send webhook notification
            if (this.webhookService) {
                await this.webhookService.sendStatusUpdate('connected', this.user);
            }

            // Start processing message queue
            if (this.messageQueue) {
                this.messageQueue.startProcessing();
            }

        } catch (error) {
            Logger.error('Failed to handle connection:', error);
        }
    }

    async handleDisconnection(lastDisconnect) {
        try {
            const shouldReconnect = lastDisconnect?.error?.output?.statusCode !== DisconnectReason.loggedOut;
            
            Logger.info('WhatsApp disconnected', {
                reason: lastDisconnect?.error?.output?.statusCode,
                shouldReconnect
            });

            this.isReady = false;
            this.user = null;

            // Emit disconnection status via Socket.IO
            if (this.io) {
                this.io.emit('whatsapp_status', {
                    connected: false,
                    reason: lastDisconnect?.error?.output?.statusCode,
                    timestamp: new Date().toISOString()
                });
            }

            // Update database
            if (this.databaseService) {
                await this.databaseService.updateDeviceStatus(null, 'disconnected');
            }

            // Send webhook notification
            if (this.webhookService) {
                await this.webhookService.sendStatusUpdate('disconnected', null);
            }

            // Stop processing message queue
            if (this.messageQueue) {
                this.messageQueue.stopProcessing();
            }

            if (shouldReconnect && process.env.WA_AUTO_RECONNECT === 'true') {
                Logger.info('Attempting to reconnect...');
                setTimeout(() => {
                    this.startConnection();
                }, 5000);
            }

        } catch (error) {
            Logger.error('Failed to handle disconnection:', error);
        }
    }

    async handleIncomingMessages(m) {
        try {
            for (const msg of m.messages) {
                if (msg.key.fromMe) continue; // Skip own messages
                
                const messageData = {
                    id: msg.key.id,
                    from: msg.key.remoteJid,
                    timestamp: msg.messageTimestamp,
                    message: this.extractMessageText(msg),
                    type: Object.keys(msg.message || {})[0]
                };

                Logger.info('Incoming message:', messageData);

                // Store in database
                if (this.databaseService) {
                    await this.databaseService.storeIncomingMessage(messageData);
                }

                // Send webhook notification
                if (this.webhookService) {
                    await this.webhookService.sendIncomingMessage(messageData);
                }

                // Emit via Socket.IO
                if (this.io) {
                    this.io.emit('incoming_message', messageData);
                }
            }
        } catch (error) {
            Logger.error('Failed to handle incoming messages:', error);
        }
    }

    async handleMessageUpdates(updates) {
        try {
            for (const update of updates) {
                Logger.debug('Message update:', update);

                // Update message status in database
                if (this.databaseService && update.key?.id) {
                    await this.databaseService.updateMessageStatus(update.key.id, update.update);
                }

                // Send webhook notification
                if (this.webhookService) {
                    await this.webhookService.sendMessageUpdate(update);
                }
            }
        } catch (error) {
            Logger.error('Failed to handle message updates:', error);
        }
    }

    extractMessageText(msg) {
        if (msg.message?.conversation) {
            return msg.message.conversation;
        } else if (msg.message?.extendedTextMessage?.text) {
            return msg.message.extendedTextMessage.text;
        } else if (msg.message?.imageMessage?.caption) {
            return msg.message.imageMessage.caption;
        } else if (msg.message?.videoMessage?.caption) {
            return msg.message.videoMessage.caption;
        }
        return '';
    }

    async sendMessage(to, message, options = {}) {
        try {
            if (!this.isReady) {
                throw new Error('WhatsApp is not connected');
            }

            const jid = this.formatJid(to);
            const result = await this.sock.sendMessage(jid, { text: message }, options);

            Logger.info('Message sent successfully', {
                to: jid,
                messageId: result.key.id
            });

            return {
                success: true,
                messageId: result.key.id,
                timestamp: result.messageTimestamp
            };

        } catch (error) {
            Logger.error('Failed to send message:', error);
            throw error;
        }
    }

    async sendImage(to, imagePath, caption = '', options = {}) {
        try {
            if (!this.isReady) {
                throw new Error('WhatsApp is not connected');
            }

            const jid = this.formatJid(to);
            const result = await this.sock.sendMessage(jid, {
                image: { url: imagePath },
                caption: caption
            }, options);

            Logger.info('Image sent successfully', {
                to: jid,
                messageId: result.key.id
            });

            return {
                success: true,
                messageId: result.key.id,
                timestamp: result.messageTimestamp
            };

        } catch (error) {
            Logger.error('Failed to send image:', error);
            throw error;
        }
    }

    formatJid(phoneNumber) {
        // Remove all non-numeric characters
        let cleaned = phoneNumber.replace(/\D/g, '');
        
        // Add country code if not present (assuming Indonesia +62)
        if (!cleaned.startsWith('62')) {
            if (cleaned.startsWith('0')) {
                cleaned = '62' + cleaned.substring(1);
            } else {
                cleaned = '62' + cleaned;
            }
        }
        
        return cleaned + '@s.whatsapp.net';
    }

    isConnected() {
        return this.isReady && this.sock?.user;
    }

    getUser() {
        return this.user;
    }

    getQRCode() {
        return this.qrCode;
    }

    async disconnect() {
        try {
            if (this.sock) {
                await this.sock.logout();
                this.sock = null;
            }
            this.isReady = false;
            this.user = null;
            this.qrCode = null;
            
            Logger.info('WhatsApp disconnected successfully');
        } catch (error) {
            Logger.error('Failed to disconnect WhatsApp:', error);
        }
    }
}

module.exports = WhatsAppService;
