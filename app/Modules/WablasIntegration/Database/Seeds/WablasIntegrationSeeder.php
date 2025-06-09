<?php

namespace App\Modules\WablasIntegration\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Wablas Integration Seeder
 * 
 * Seeds sample data for testing and demonstration
 */
class WablasIntegrationSeeder extends Seeder
{
    public function run()
    {
        // Seed sample device
        $this->seedDevices();
        
        // Seed sample contacts
        $this->seedContacts();
        
        // Seed sample groups
        $this->seedGroups();
        
        // Seed sample templates
        $this->seedTemplates();
        
        // Seed sample auto-replies
        $this->seedAutoReplies();
        
        // Seed sample webhooks
        $this->seedWebhooks();
    }
    
    /**
     * Seed sample devices
     */
    protected function seedDevices()
    {
        $data = [
            [
                'device_name' => 'Primary WhatsApp Device',
                'device_serial' => 'DEMO_DEVICE_001',
                'phone_number' => '6281234567890',
                'token' => 'demo_token_replace_with_real',
                'secret_key' => 'demo_secret_replace_with_real',
                'api_url' => 'https://wablas.com',
                'device_type' => 'wablas',
                'device_status' => 1,
                'connection_status' => 'disconnected',
                'quota_limit' => 1000,
                'quota_used' => 0,
                'delay_seconds' => 10,
                'max_retries' => 3,
                'auto_reply_enabled' => 1,
                'incoming_webhook_enabled' => 1,
                'status_webhook_enabled' => 1,
                'device_webhook_enabled' => 1,
                'notes' => 'Demo device for testing purposes',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ];
        
        $this->db->table('wablas_devices')->insertBatch($data);
        echo "Seeded devices\n";
    }
    
    /**
     * Seed sample contacts
     */
    protected function seedContacts()
    {
        $data = [
            [
                'phone_number' => '6281234567891',
                'name' => 'John Doe',
                'nickname' => 'John',
                'email' => 'john.doe@example.com',
                'address' => '123 Main Street, Jakarta',
                'birthday' => '1990-01-15',
                'gender' => 'male',
                'status' => 'active',
                'is_whatsapp_active' => 1,
                'message_count' => 0,
                'tags' => json_encode(['customer', 'vip']),
                'custom_fields' => json_encode(['company' => 'Example Corp', 'position' => 'Manager']),
                'notes' => 'Important customer',
                'source' => 'manual',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'phone_number' => '6281234567892',
                'name' => 'Jane Smith',
                'nickname' => 'Jane',
                'email' => 'jane.smith@example.com',
                'address' => '456 Oak Avenue, Bandung',
                'birthday' => '1985-05-20',
                'gender' => 'female',
                'status' => 'active',
                'is_whatsapp_active' => 1,
                'message_count' => 0,
                'tags' => json_encode(['customer', 'regular']),
                'custom_fields' => json_encode(['company' => 'Tech Solutions', 'position' => 'Developer']),
                'notes' => 'Regular customer',
                'source' => 'manual',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'phone_number' => '6281234567893',
                'name' => 'Bob Johnson',
                'nickname' => 'Bob',
                'email' => 'bob.johnson@example.com',
                'address' => '789 Pine Road, Surabaya',
                'birthday' => '1992-12-10',
                'gender' => 'male',
                'status' => 'active',
                'is_whatsapp_active' => 1,
                'message_count' => 0,
                'tags' => json_encode(['prospect']),
                'custom_fields' => json_encode(['company' => 'Startup Inc', 'position' => 'CEO']),
                'notes' => 'Potential customer',
                'source' => 'manual',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ];
        
        $this->db->table('wablas_contacts')->insertBatch($data);
        echo "Seeded contacts\n";
    }
    
    /**
     * Seed sample groups
     */
    protected function seedGroups()
    {
        $data = [
            [
                'name' => 'VIP Customers',
                'description' => 'High-value customers requiring special attention',
                'color' => '#ff6b6b',
                'is_active' => 1,
                'contact_count' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'name' => 'Regular Customers',
                'description' => 'Standard customers',
                'color' => '#4ecdc4',
                'is_active' => 1,
                'contact_count' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'name' => 'Prospects',
                'description' => 'Potential customers',
                'color' => '#45b7d1',
                'is_active' => 1,
                'contact_count' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ];
        
        $this->db->table('wablas_groups')->insertBatch($data);
        echo "Seeded groups\n";
    }
    
    /**
     * Seed sample templates
     */
    protected function seedTemplates()
    {
        $data = [
            [
                'name' => 'Welcome Message',
                'description' => 'Welcome message for new customers',
                'category' => 'Welcome',
                'message_type' => 'text',
                'content' => 'Hello {name}! Welcome to our service. We\'re excited to have you on board. If you have any questions, feel free to ask!',
                'variables' => json_encode(['name']),
                'is_active' => 1,
                'usage_count' => 0,
                'tags' => json_encode(['welcome', 'onboarding']),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'name' => 'Order Confirmation',
                'description' => 'Order confirmation message',
                'category' => 'Orders',
                'message_type' => 'text',
                'content' => 'Hi {name}, your order #{order_id} has been confirmed. Total amount: {amount}. Expected delivery: {delivery_date}. Thank you for your purchase!',
                'variables' => json_encode(['name', 'order_id', 'amount', 'delivery_date']),
                'is_active' => 1,
                'usage_count' => 0,
                'tags' => json_encode(['order', 'confirmation']),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'name' => 'Birthday Wishes',
                'description' => 'Birthday greeting message',
                'category' => 'Greetings',
                'message_type' => 'text',
                'content' => '🎉 Happy Birthday {name}! 🎂 Wishing you a wonderful day filled with happiness and joy. As a birthday gift, enjoy 20% off your next purchase with code BIRTHDAY20!',
                'variables' => json_encode(['name']),
                'is_active' => 1,
                'usage_count' => 0,
                'tags' => json_encode(['birthday', 'promotion']),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ];
        
        $this->db->table('wablas_templates')->insertBatch($data);
        echo "Seeded templates\n";
    }
    
    /**
     * Seed sample auto-replies
     */
    protected function seedAutoReplies()
    {
        $deviceId = $this->db->table('wablas_devices')->select('id')->get()->getRow()->id ?? 1;
        
        $data = [
            [
                'device_id' => $deviceId,
                'keyword' => 'hello',
                'response_type' => 'text',
                'response_content' => 'Hello! Thank you for contacting us. How can I help you today?',
                'is_exact_match' => 0,
                'is_case_sensitive' => 0,
                'is_active' => 1,
                'priority' => 100,
                'usage_count' => 0,
                'business_hours_only' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'device_id' => $deviceId,
                'keyword' => 'info',
                'response_type' => 'text',
                'response_content' => 'For more information about our services, please visit our website or contact our support team during business hours (9 AM - 5 PM).',
                'is_exact_match' => 0,
                'is_case_sensitive' => 0,
                'is_active' => 1,
                'priority' => 200,
                'usage_count' => 0,
                'business_hours_only' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'device_id' => $deviceId,
                'keyword' => 'price',
                'response_type' => 'text',
                'response_content' => 'For pricing information, please check our website or speak with our sales team. We offer competitive rates and flexible packages.',
                'is_exact_match' => 0,
                'is_case_sensitive' => 0,
                'is_active' => 1,
                'priority' => 150,
                'usage_count' => 0,
                'business_hours_only' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ];
        
        $this->db->table('wablas_auto_replies')->insertBatch($data);
        echo "Seeded auto-replies\n";
    }
    
    /**
     * Seed sample webhooks
     */
    protected function seedWebhooks()
    {
        $data = [
            [
                'name' => 'Incoming Messages',
                'endpoint' => 'wablas/webhook/incoming',
                'type' => 'incoming',
                'method' => 'POST',
                'is_active' => 1,
                'timeout' => 30,
                'retry_attempts' => 3,
                'retry_delay' => 5,
                'success_count' => 0,
                'failure_count' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'name' => 'Message Status Updates',
                'endpoint' => 'wablas/webhook/status',
                'type' => 'status',
                'method' => 'POST',
                'is_active' => 1,
                'timeout' => 30,
                'retry_attempts' => 3,
                'retry_delay' => 5,
                'success_count' => 0,
                'failure_count' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'name' => 'Device Status Updates',
                'endpoint' => 'wablas/webhook/device',
                'type' => 'device',
                'method' => 'POST',
                'is_active' => 1,
                'timeout' => 30,
                'retry_attempts' => 3,
                'retry_delay' => 5,
                'success_count' => 0,
                'failure_count' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ];
        
        $this->db->table('wablas_webhooks')->insertBatch($data);
        echo "Seeded webhooks\n";
    }
}
