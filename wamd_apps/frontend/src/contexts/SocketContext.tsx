import React, { createContext, useContext, useEffect, useState } from 'react';
import { io, Socket } from 'socket.io-client';
import toast from 'react-hot-toast';

interface WhatsAppStatus {
  connected: boolean;
  user?: {
    id: string;
    name?: string;
  } | null;
  timestamp: string;
}

interface QRCodeData {
  qr: string;
  timestamp: string;
}

interface IncomingMessage {
  id: string;
  from: string;
  message: string;
  type: string;
  timestamp: string;
}

interface SocketContextType {
  socket: Socket | null;
  connected: boolean;
  whatsappStatus: WhatsAppStatus;
  qrCode: string | null;
  messages: IncomingMessage[];
}

const SocketContext = createContext<SocketContextType>({
  socket: null,
  connected: false,
  whatsappStatus: {
    connected: false,
    user: null,
    timestamp: new Date().toISOString(),
  },
  qrCode: null,
  messages: [],
});

export const useSocket = () => {
  const context = useContext(SocketContext);
  if (!context) {
    throw new Error('useSocket must be used within a SocketProvider');
  }
  return context;
};

interface SocketProviderProps {
  children: React.ReactNode;
}

export const SocketProvider: React.FC<SocketProviderProps> = ({ children }) => {
  const [socket, setSocket] = useState<Socket | null>(null);
  const [connected, setConnected] = useState(false);
  const [whatsappStatus, setWhatsappStatus] = useState<WhatsAppStatus>({
    connected: false,
    user: null,
    timestamp: new Date().toISOString(),
  });
  const [qrCode, setQrCode] = useState<string | null>(null);
  const [messages, setMessages] = useState<IncomingMessage[]>([]);

  useEffect(() => {
    // Initialize socket connection
    const socketInstance = io(process.env.REACT_APP_SOCKET_URL || 'http://localhost:3000', {
      transports: ['websocket', 'polling'],
      timeout: 20000,
    });

    setSocket(socketInstance);

    // Socket event handlers
    socketInstance.on('connect', () => {
      console.log('Connected to server');
      setConnected(true);
      toast.success('Connected to WhatsApp Gateway');
    });

    socketInstance.on('disconnect', () => {
      console.log('Disconnected from server');
      setConnected(false);
      toast.error('Disconnected from WhatsApp Gateway');
    });

    socketInstance.on('connect_error', (error) => {
      console.error('Connection error:', error);
      setConnected(false);
      toast.error('Failed to connect to WhatsApp Gateway');
    });

    // WhatsApp specific events
    socketInstance.on('whatsapp_status', (data: WhatsAppStatus) => {
      console.log('WhatsApp status update:', data);
      setWhatsappStatus(data);
      
      if (data.connected) {
        setQrCode(null); // Clear QR code when connected
        toast.success(`WhatsApp connected as ${data.user?.name || data.user?.id || 'Unknown'}`);
      } else {
        toast.error('WhatsApp disconnected');
      }
    });

    socketInstance.on('qr_code', (data: QRCodeData) => {
      console.log('QR code received');
      setQrCode(data.qr);
      toast('New QR code generated. Please scan with WhatsApp.', {
        icon: '📱',
        duration: 6000,
      });
    });

    socketInstance.on('incoming_message', (data: IncomingMessage) => {
      console.log('Incoming message:', data);
      setMessages(prev => [data, ...prev.slice(0, 49)]); // Keep last 50 messages
      
      // Show notification for new message
      const fromNumber = data.from.replace('@s.whatsapp.net', '');
      toast(`New message from ${fromNumber}`, {
        icon: '💬',
        duration: 3000,
      });
    });

    // Cleanup on unmount
    return () => {
      socketInstance.disconnect();
    };
  }, []);

  const value: SocketContextType = {
    socket,
    connected,
    whatsappStatus,
    qrCode,
    messages,
  };

  return (
    <SocketContext.Provider value={value}>
      {children}
    </SocketContext.Provider>
  );
};
