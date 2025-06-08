const winston = require('winston');
const path = require('path');
const fs = require('fs');

// Ensure logs directory exists
const logsDir = path.join(__dirname, '../../logs');
if (!fs.existsSync(logsDir)) {
    fs.mkdirSync(logsDir, { recursive: true });
}

// Custom format for console output
const consoleFormat = winston.format.combine(
    winston.format.timestamp({ format: 'YYYY-MM-DD HH:mm:ss' }),
    winston.format.colorize(),
    winston.format.printf(({ timestamp, level, message, ...meta }) => {
        let metaStr = '';
        if (Object.keys(meta).length > 0) {
            metaStr = ' ' + JSON.stringify(meta, null, 2);
        }
        return `${timestamp} [${level}]: ${message}${metaStr}`;
    })
);

// Custom format for file output
const fileFormat = winston.format.combine(
    winston.format.timestamp({ format: 'YYYY-MM-DD HH:mm:ss' }),
    winston.format.errors({ stack: true }),
    winston.format.json()
);

// Create logger instance
const logger = winston.createLogger({
    level: process.env.LOG_LEVEL || 'info',
    format: fileFormat,
    defaultMeta: { service: 'whatsapp-gateway' },
    transports: [
        // File transport for all logs
        new winston.transports.File({
            filename: path.join(logsDir, 'app.log'),
            maxsize: 10485760, // 10MB
            maxFiles: 5,
            tailable: true
        }),
        
        // File transport for errors only
        new winston.transports.File({
            filename: path.join(logsDir, 'error.log'),
            level: 'error',
            maxsize: 10485760, // 10MB
            maxFiles: 5,
            tailable: true
        })
    ],
    
    // Handle uncaught exceptions
    exceptionHandlers: [
        new winston.transports.File({
            filename: path.join(logsDir, 'exceptions.log')
        })
    ],
    
    // Handle unhandled promise rejections
    rejectionHandlers: [
        new winston.transports.File({
            filename: path.join(logsDir, 'rejections.log')
        })
    ]
});

// Add console transport for development
if (process.env.NODE_ENV !== 'production') {
    logger.add(new winston.transports.Console({
        format: consoleFormat
    }));
}

// Add custom methods for better usability
logger.logRequest = (req, res, responseTime) => {
    logger.info('HTTP Request', {
        method: req.method,
        url: req.originalUrl,
        ip: req.ip,
        userAgent: req.get('User-Agent'),
        statusCode: res.statusCode,
        responseTime: `${responseTime}ms`
    });
};

logger.logWhatsAppEvent = (event, data) => {
    logger.info(`WhatsApp Event: ${event}`, data);
};

logger.logMessageSent = (to, messageId, success = true) => {
    logger.info('Message Sent', {
        to: to,
        messageId: messageId,
        success: success,
        timestamp: new Date().toISOString()
    });
};

logger.logMessageReceived = (from, messageId, message) => {
    logger.info('Message Received', {
        from: from,
        messageId: messageId,
        message: message.substring(0, 100), // Truncate long messages
        timestamp: new Date().toISOString()
    });
};

logger.logDatabaseOperation = (operation, table, success = true, error = null) => {
    const logData = {
        operation: operation,
        table: table,
        success: success,
        timestamp: new Date().toISOString()
    };
    
    if (error) {
        logData.error = error.message;
        logger.error('Database Operation Failed', logData);
    } else {
        logger.debug('Database Operation', logData);
    }
};

logger.logWebhook = (url, payload, success = true, error = null) => {
    const logData = {
        url: url,
        event: payload.event,
        success: success,
        timestamp: new Date().toISOString()
    };
    
    if (error) {
        logData.error = error.message;
        logger.error('Webhook Failed', logData);
    } else {
        logger.info('Webhook Sent', logData);
    }
};

logger.logQueueOperation = (operation, messageCount, success = true) => {
    logger.info('Queue Operation', {
        operation: operation,
        messageCount: messageCount,
        success: success,
        timestamp: new Date().toISOString()
    });
};

logger.logConnectionStatus = (status, details = {}) => {
    logger.info('Connection Status', {
        status: status,
        ...details,
        timestamp: new Date().toISOString()
    });
};

logger.logPerformance = (operation, duration, details = {}) => {
    logger.info('Performance', {
        operation: operation,
        duration: `${duration}ms`,
        ...details,
        timestamp: new Date().toISOString()
    });
};

// Export logger
module.exports = logger;
