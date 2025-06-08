import axios, { AxiosResponse } from 'axios';

const API_BASE_URL = process.env.REACT_APP_API_URL || 'http://localhost:3000';
const API_KEY = process.env.REACT_APP_API_KEY || 'your_secure_api_key_here';

// Create axios instance with default config
const api = axios.create({
  baseURL: API_BASE_URL,
  timeout: 30000,
  headers: {
    'Content-Type': 'application/json',
    'X-API-Key': API_KEY,
  },
});

// Request interceptor
api.interceptors.request.use(
  (config) => {
    console.log(`Making ${config.method?.toUpperCase()} request to ${config.url}`);
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// Response interceptor
api.interceptors.response.use(
  (response) => {
    return response;
  },
  (error) => {
    console.error('API Error:', error.response?.data || error.message);
    return Promise.reject(error);
  }
);

// Types
interface ApiResponse<T = any> {
  success: boolean;
  data?: T;
  message?: string;
  error?: string;
}

interface QueueStats {
  totalMessages: number;
  sentToday: number;
  pendingMessages: number;
  failedMessages: number;
  totalContacts: number;
  isProcessing: boolean;
  intervalMs: number;
  maxRetryAttempts: number;
}

interface WhatsAppStatus {
  connected: boolean;
  user?: {
    id: string;
    name?: string;
  } | null;
  qrCode?: string | null;
  timestamp: string;
}

interface SendMessageRequest {
  to: string;
  message: string;
  delay?: number;
}

interface BulkMessageRequest {
  contacts: string[] | { phone_number: string }[];
  message: string;
  delay?: number;
}

interface Contact {
  id: number;
  contact_name: string;
  phone_number: string;
  created_at: string;
}

interface Template {
  id: number;
  template_name: string;
  template_content: string;
  created_at: string;
}

export const apiService = {
  // WhatsApp Status
  async getStatus(): Promise<ApiResponse<WhatsAppStatus>> {
    const response: AxiosResponse<ApiResponse<WhatsAppStatus>> = await api.get('/api/status');
    return response.data;
  },

  // QR Code
  async getQRCode(): Promise<ApiResponse<{ qr_code: string; connected: boolean }>> {
    const response: AxiosResponse<ApiResponse<{ qr_code: string; connected: boolean }>> = await api.get('/qr/json');
    return response.data;
  },

  // Connection Management
  async disconnect(): Promise<ApiResponse> {
    const response: AxiosResponse<ApiResponse> = await api.post('/api/disconnect');
    return response.data;
  },

  async restartConnection(): Promise<ApiResponse> {
    const response: AxiosResponse<ApiResponse> = await api.post('/api/restart');
    return response.data;
  },

  // Message Sending
  async sendMessage(data: SendMessageRequest): Promise<ApiResponse> {
    const response: AxiosResponse<ApiResponse> = await api.post('/api/send-message', data);
    return response.data;
  },

  async sendBulkMessage(data: BulkMessageRequest): Promise<ApiResponse> {
    const response: AxiosResponse<ApiResponse> = await api.post('/api/send-bulk', data);
    return response.data;
  },

  // Queue Management
  async getQueueStats(): Promise<ApiResponse<QueueStats>> {
    const response: AxiosResponse<ApiResponse<QueueStats>> = await api.get('/api/queue/stats');
    return response.data;
  },

  async clearQueue(): Promise<ApiResponse> {
    const response: AxiosResponse<ApiResponse> = await api.post('/api/queue/clear');
    return response.data;
  },

  async retryFailedMessages(): Promise<ApiResponse> {
    const response: AxiosResponse<ApiResponse> = await api.post('/api/queue/retry-failed');
    return response.data;
  },

  // Contacts
  async getContacts(): Promise<ApiResponse<Contact[]>> {
    const response: AxiosResponse<ApiResponse<Contact[]>> = await api.get('/api/contacts');
    return response.data;
  },

  // Templates
  async getTemplates(): Promise<ApiResponse<Template[]>> {
    const response: AxiosResponse<ApiResponse<Template[]>> = await api.get('/api/templates');
    return response.data;
  },

  // Webhook endpoints
  webhook: {
    async sendMessage(data: SendMessageRequest): Promise<ApiResponse> {
      const response: AxiosResponse<ApiResponse> = await api.post('/webhook/send', {
        ...data,
        token: API_KEY, // Add webhook token
      });
      return response.data;
    },

    async sendBulkMessage(data: BulkMessageRequest): Promise<ApiResponse> {
      const response: AxiosResponse<ApiResponse> = await api.post('/webhook/send-bulk', {
        ...data,
        token: API_KEY, // Add webhook token
      });
      return response.data;
    },

    async getStatus(): Promise<ApiResponse<WhatsAppStatus>> {
      const response: AxiosResponse<ApiResponse<WhatsAppStatus>> = await api.get('/webhook/status', {
        params: { token: API_KEY },
      });
      return response.data;
    },

    async sendAttendanceNotification(data: {
      student_id: string;
      student_name: string;
      parent_phone: string;
      attendance_status: 'present' | 'absent';
      timestamp?: string;
      class_name?: string;
      session_name?: string;
    }): Promise<ApiResponse> {
      const response: AxiosResponse<ApiResponse> = await api.post('/webhook/attendance-notification', {
        ...data,
        token: API_KEY,
      });
      return response.data;
    },

    async test(data: any = {}): Promise<ApiResponse> {
      const response: AxiosResponse<ApiResponse> = await api.post('/webhook/test', data);
      return response.data;
    },
  },

  // Health check
  async healthCheck(): Promise<ApiResponse> {
    const response: AxiosResponse<ApiResponse> = await api.get('/health');
    return response.data;
  },
};

export default apiService;
