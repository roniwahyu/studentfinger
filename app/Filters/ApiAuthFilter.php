<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * API Authentication Filter
 * 
 * Handles authentication for API endpoints
 */
class ApiAuthFilter implements FilterInterface
{
    /**
     * Do whatever processing this filter needs to do.
     * By default it should not return anything during
     * normal execution. However, when an abnormal state
     * is found, it should return an instance of
     * CodeIgniter\HTTP\Response. If it does, script
     * execution will end and that Response will be
     * sent back to the client, allowing for error pages,
     * redirects, etc.
     *
     * @param RequestInterface $request
     * @param array|null       $arguments
     *
     * @return RequestInterface|ResponseInterface|string|void
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        // Get the authorization header
        $authHeader = $request->getHeaderLine('Authorization');
        
        if (empty($authHeader)) {
            // Check for API key in query parameters
            $apiKey = $request->getGet('api_key');
            if (empty($apiKey)) {
                return $this->unauthorizedResponse('Missing API key or authorization header');
            }
            $token = $apiKey;
        } else {
            // Extract token from Bearer authorization
            if (strpos($authHeader, 'Bearer ') === 0) {
                $token = substr($authHeader, 7);
            } else {
                return $this->unauthorizedResponse('Invalid authorization format. Use Bearer token.');
            }
        }
        
        // Validate the token
        if (!$this->validateApiToken($token)) {
            return $this->unauthorizedResponse('Invalid API token');
        }
        
        // Token is valid, continue with the request
        return $request;
    }
    
    /**
     * Allows After filters to inspect and modify the response
     * object as needed. This method does not allow any way
     * to stop execution of other after filters, short of
     * throwing an Exception or Error.
     *
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param array|null        $arguments
     *
     * @return ResponseInterface|void
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Nothing to do after the request
    }
    
    /**
     * Validate API token
     * 
     * @param string $token
     * @return bool
     */
    protected function validateApiToken(string $token): bool
    {
        // For demo purposes, accept any non-empty token
        // In production, you should validate against a database or external service
        
        // Simple validation - token should be at least 10 characters
        if (strlen($token) < 10) {
            return false;
        }
        
        // Check for demo tokens
        $validTokens = [
            'demo_api_token_123456789',
            'test_api_token_987654321',
            'wablas_demo_token_12345'
        ];
        
        if (in_array($token, $validTokens)) {
            return true;
        }
        
        // For development, accept any token starting with 'dev_'
        if (ENVIRONMENT === 'development' && strpos($token, 'dev_') === 0) {
            return true;
        }
        
        // In production, you would typically:
        // 1. Query the database for the token
        // 2. Check if the token is active and not expired
        // 3. Load user/client information associated with the token
        // 4. Set user context for the request
        
        // Example database validation (commented out):
        /*
        $db = \Config\Database::connect();
        $apiKey = $db->table('api_keys')
                    ->where('token', $token)
                    ->where('is_active', 1)
                    ->where('expires_at >', date('Y-m-d H:i:s'))
                    ->get()
                    ->getRow();
        
        if ($apiKey) {
            // Set user context
            $request->apiUser = $apiKey;
            return true;
        }
        */
        
        return false;
    }
    
    /**
     * Return unauthorized response
     * 
     * @param string $message
     * @return ResponseInterface
     */
    protected function unauthorizedResponse(string $message): ResponseInterface
    {
        $response = service('response');
        
        return $response->setJSON([
            'success' => false,
            'error' => $message,
            'code' => 401
        ])->setStatusCode(401);
    }
}
