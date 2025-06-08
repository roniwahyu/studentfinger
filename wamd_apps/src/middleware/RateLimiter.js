const { RateLimiterMemory } = require('rate-limiter-flexible');
const Logger = require('../utils/Logger');

// Create rate limiter instance
const rateLimiter = new RateLimiterMemory({
    keyGenerator: (req) => req.ip, // Use IP address as key
    points: parseInt(process.env.RATE_LIMIT_MAX_REQUESTS) || 100, // Number of requests
    duration: parseInt(process.env.RATE_LIMIT_WINDOW_MS) / 1000 || 60, // Per 60 seconds
    blockDuration: 60, // Block for 60 seconds if limit exceeded
});

// Rate limiting middleware
const rateLimiterMiddleware = async (req, res, next) => {
    try {
        await rateLimiter.consume(req.ip);
        next();
    } catch (rejRes) {
        const secs = Math.round(rejRes.msBeforeNext / 1000) || 1;
        
        Logger.warn('Rate limit exceeded', {
            ip: req.ip,
            path: req.path,
            retryAfter: secs
        });
        
        res.set('Retry-After', String(secs));
        res.status(429).json({
            error: 'Too Many Requests',
            message: `Rate limit exceeded. Try again in ${secs} seconds.`,
            retryAfter: secs
        });
    }
};

module.exports = rateLimiterMiddleware;
