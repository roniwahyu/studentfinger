import React from 'react';
import {
  Box,
  Card,
  CardContent,
  Typography,
  Grid,
  Chip,
  Divider,
  List,
  ListItem,
  ListItemText,
  ListItemIcon,
} from '@mui/material';
import {
  Info,
  Speed,
  Security,
  Storage,
  Network,
  WhatsApp,
  Api,
} from '@mui/icons-material';
import { motion } from 'framer-motion';
import { useQuery } from 'react-query';
import { useSocket } from '../contexts/SocketContext';
import { apiService } from '../services/apiService';

const Settings: React.FC = () => {
  const { whatsappStatus, connected } = useSocket();

  // Fetch system health
  const { data: healthData } = useQuery(
    'health',
    () => apiService.healthCheck(),
    {
      refetchInterval: 10000,
    }
  );

  const systemInfo = [
    {
      icon: <Network />,
      label: 'Gateway Connection',
      value: connected ? 'Online' : 'Offline',
      status: connected ? 'success' : 'error',
    },
    {
      icon: <WhatsApp />,
      label: 'WhatsApp Status',
      value: whatsappStatus.connected ? 'Connected' : 'Disconnected',
      status: whatsappStatus.connected ? 'success' : 'warning',
    },
    {
      icon: <Api />,
      label: 'API Version',
      value: 'v1.0.0',
      status: 'default',
    },
    {
      icon: <Speed />,
      label: 'Server Uptime',
      value: healthData?.uptime ? `${Math.floor(healthData.uptime / 3600)}h ${Math.floor((healthData.uptime % 3600) / 60)}m` : 'N/A',
      status: 'default',
    },
  ];

  const apiEndpoints = [
    { method: 'GET', path: '/api/status', description: 'Get WhatsApp connection status' },
    { method: 'POST', path: '/api/send-message', description: 'Send single message' },
    { method: 'POST', path: '/api/send-bulk', description: 'Send bulk messages' },
    { method: 'GET', path: '/api/queue/stats', description: 'Get queue statistics' },
    { method: 'POST', path: '/api/disconnect', description: 'Disconnect WhatsApp' },
    { method: 'POST', path: '/api/restart', description: 'Restart connection' },
    { method: 'GET', path: '/qr/json', description: 'Get QR code data' },
    { method: 'POST', path: '/webhook/send', description: 'Send message via webhook' },
  ];

  const features = [
    'Multi-device WhatsApp integration',
    'Real-time QR code scanning',
    'Message queue management',
    'Bulk message sending',
    'Webhook support',
    'Rate limiting protection',
    'Automatic reconnection',
    'Message templates',
    'Attendance notifications',
    'RESTful API',
  ];

  return (
    <Box sx={{ p: 3 }}>
      <motion.div
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ duration: 0.5 }}
      >
        {/* Header */}
        <Box sx={{ mb: 4 }}>
          <Typography variant="h4" component="h1" gutterBottom>
            Settings & Information
          </Typography>
          <Typography variant="body1" color="text.secondary">
            System information and configuration details
          </Typography>
        </Box>

        <Grid container spacing={3}>
          {/* System Status */}
          <Grid item xs={12} md={6}>
            <Card>
              <CardContent>
                <Typography variant="h6" gutterBottom sx={{ display: 'flex', alignItems: 'center' }}>
                  <Info sx={{ mr: 1 }} />
                  System Status
                </Typography>
                
                <List>
                  {systemInfo.map((item, index) => (
                    <ListItem key={index} sx={{ px: 0 }}>
                      <ListItemIcon>
                        {item.icon}
                      </ListItemIcon>
                      <ListItemText
                        primary={item.label}
                        secondary={
                          <Chip
                            label={item.value}
                            color={item.status as any}
                            size="small"
                            sx={{ mt: 0.5 }}
                          />
                        }
                      />
                    </ListItem>
                  ))}
                </List>
              </CardContent>
            </Card>
          </Grid>

          {/* Features */}
          <Grid item xs={12} md={6}>
            <Card>
              <CardContent>
                <Typography variant="h6" gutterBottom>
                  Features
                </Typography>
                <Grid container spacing={1}>
                  {features.map((feature, index) => (
                    <Grid item xs={12} sm={6} key={index}>
                      <Chip
                        label={feature}
                        variant="outlined"
                        size="small"
                        sx={{ mb: 1, width: '100%', justifyContent: 'flex-start' }}
                      />
                    </Grid>
                  ))}
                </Grid>
              </CardContent>
            </Card>
          </Grid>

          {/* API Endpoints */}
          <Grid item xs={12}>
            <Card>
              <CardContent>
                <Typography variant="h6" gutterBottom>
                  API Endpoints
                </Typography>
                <Typography variant="body2" color="text.secondary" sx={{ mb: 2 }}>
                  Available REST API endpoints for integration
                </Typography>
                
                <Grid container spacing={2}>
                  {apiEndpoints.map((endpoint, index) => (
                    <Grid item xs={12} md={6} key={index}>
                      <Box
                        sx={{
                          p: 2,
                          border: '1px solid',
                          borderColor: 'divider',
                          borderRadius: 1,
                          bgcolor: 'background.default',
                        }}
                      >
                        <Box sx={{ display: 'flex', alignItems: 'center', mb: 1 }}>
                          <Chip
                            label={endpoint.method}
                            color={endpoint.method === 'GET' ? 'primary' : 'secondary'}
                            size="small"
                            sx={{ mr: 1, minWidth: 60 }}
                          />
                          <Typography variant="body2" component="code" sx={{ fontFamily: 'monospace' }}>
                            {endpoint.path}
                          </Typography>
                        </Box>
                        <Typography variant="caption" color="text.secondary">
                          {endpoint.description}
                        </Typography>
                      </Box>
                    </Grid>
                  ))}
                </Grid>
              </CardContent>
            </Card>
          </Grid>

          {/* Configuration */}
          <Grid item xs={12} md={6}>
            <Card>
              <CardContent>
                <Typography variant="h6" gutterBottom sx={{ display: 'flex', alignItems: 'center' }}>
                  <Security sx={{ mr: 1 }} />
                  Security & Configuration
                </Typography>
                
                <List dense>
                  <ListItem>
                    <ListItemText
                      primary="API Authentication"
                      secondary="X-API-Key header required for all API calls"
                    />
                  </ListItem>
                  <ListItem>
                    <ListItemText
                      primary="Rate Limiting"
                      secondary="100 requests per minute per IP address"
                    />
                  </ListItem>
                  <ListItem>
                    <ListItemText
                      primary="CORS Policy"
                      secondary="Configured for development and production environments"
                    />
                  </ListItem>
                  <ListItem>
                    <ListItemText
                      primary="Session Storage"
                      secondary="WhatsApp sessions stored locally for persistence"
                    />
                  </ListItem>
                </List>
              </CardContent>
            </Card>
          </Grid>

          {/* Technical Details */}
          <Grid item xs={12} md={6}>
            <Card>
              <CardContent>
                <Typography variant="h6" gutterBottom sx={{ display: 'flex', alignItems: 'center' }}>
                  <Storage sx={{ mr: 1 }} />
                  Technical Details
                </Typography>
                
                <List dense>
                  <ListItem>
                    <ListItemText
                      primary="Backend Framework"
                      secondary="Node.js with Express.js"
                    />
                  </ListItem>
                  <ListItem>
                    <ListItemText
                      primary="WhatsApp Library"
                      secondary="@WhiskeySockets/Baileys v6.6.0"
                    />
                  </ListItem>
                  <ListItem>
                    <ListItemText
                      primary="Real-time Communication"
                      secondary="Socket.IO for live updates"
                    />
                  </ListItem>
                  <ListItem>
                    <ListItemText
                      primary="Database"
                      secondary="MySQL for message queue and logs"
                    />
                  </ListItem>
                  <ListItem>
                    <ListItemText
                      primary="Frontend"
                      secondary="React with TypeScript and Material-UI"
                    />
                  </ListItem>
                </List>
              </CardContent>
            </Card>
          </Grid>

          {/* Environment Information */}
          <Grid item xs={12}>
            <Card>
              <CardContent>
                <Typography variant="h6" gutterBottom>
                  Environment Information
                </Typography>
                
                <Grid container spacing={3}>
                  <Grid item xs={12} sm={6} md={3}>
                    <Typography variant="subtitle2" gutterBottom>
                      Server
                    </Typography>
                    <Typography variant="body2" color="text.secondary">
                      Node.js Runtime
                    </Typography>
                    <Typography variant="body2" color="text.secondary">
                      Express.js Framework
                    </Typography>
                  </Grid>
                  
                  <Grid item xs={12} sm={6} md={3}>
                    <Typography variant="subtitle2" gutterBottom>
                      Database
                    </Typography>
                    <Typography variant="body2" color="text.secondary">
                      MySQL Database
                    </Typography>
                    <Typography variant="body2" color="text.secondary">
                      Connection Pooling
                    </Typography>
                  </Grid>
                  
                  <Grid item xs={12} sm={6} md={3}>
                    <Typography variant="subtitle2" gutterBottom>
                      WhatsApp
                    </Typography>
                    <Typography variant="body2" color="text.secondary">
                      Multi-Device Support
                    </Typography>
                    <Typography variant="body2" color="text.secondary">
                      QR Code Authentication
                    </Typography>
                  </Grid>
                  
                  <Grid item xs={12} sm={6} md={3}>
                    <Typography variant="subtitle2" gutterBottom>
                      Integration
                    </Typography>
                    <Typography variant="body2" color="text.secondary">
                      CodeIgniter 4 Compatible
                    </Typography>
                    <Typography variant="body2" color="text.secondary">
                      Webhook Support
                    </Typography>
                  </Grid>
                </Grid>
              </CardContent>
            </Card>
          </Grid>
        </Grid>
      </motion.div>
    </Box>
  );
};

export default Settings;
