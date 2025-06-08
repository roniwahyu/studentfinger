import React from 'react';
import {
  Box,
  Grid,
  Card,
  CardContent,
  Typography,
  Chip,
  Avatar,
  LinearProgress,
  IconButton,
  Button,
} from '@mui/material';
import {
  WhatsApp,
  Send,
  Queue,
  Error,
  CheckCircle,
  Refresh,
  QrCode,
  Message,
} from '@mui/icons-material';
import { motion } from 'framer-motion';
import { useQuery } from 'react-query';
import { useNavigate } from 'react-router-dom';
import { useSocket } from '../contexts/SocketContext';
import { apiService } from '../services/apiService';

const Dashboard: React.FC = () => {
  const navigate = useNavigate();
  const { whatsappStatus, connected, messages } = useSocket();

  // Fetch queue statistics
  const { data: queueStats, refetch: refetchStats } = useQuery(
    'queueStats',
    () => apiService.getQueueStats(),
    {
      refetchInterval: 10000, // Refetch every 10 seconds
    }
  );

  const stats = queueStats?.data || {
    totalMessages: 0,
    sentToday: 0,
    pendingMessages: 0,
    failedMessages: 0,
    totalContacts: 0,
  };

  const containerVariants = {
    hidden: { opacity: 0 },
    visible: {
      opacity: 1,
      transition: {
        staggerChildren: 0.1,
      },
    },
  };

  const itemVariants = {
    hidden: { y: 20, opacity: 0 },
    visible: {
      y: 0,
      opacity: 1,
      transition: {
        duration: 0.5,
      },
    },
  };

  return (
    <Box sx={{ p: 3 }}>
      <motion.div
        variants={containerVariants}
        initial="hidden"
        animate="visible"
      >
        {/* Header */}
        <motion.div variants={itemVariants}>
          <Box sx={{ mb: 4 }}>
            <Typography variant="h4" component="h1" gutterBottom>
              WhatsApp Gateway Dashboard
            </Typography>
            <Typography variant="body1" color="text.secondary">
              Monitor your WhatsApp integration and message statistics
            </Typography>
          </Box>
        </motion.div>

        {/* Connection Status */}
        <motion.div variants={itemVariants}>
          <Card sx={{ mb: 3, bgcolor: whatsappStatus.connected ? 'success.light' : 'warning.light' }}>
            <CardContent>
              <Box sx={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
                <Box sx={{ display: 'flex', alignItems: 'center' }}>
                  <Avatar sx={{ bgcolor: whatsappStatus.connected ? 'success.main' : 'warning.main', mr: 2 }}>
                    <WhatsApp />
                  </Avatar>
                  <Box>
                    <Typography variant="h6">
                      WhatsApp Status
                    </Typography>
                    <Typography variant="body2">
                      {whatsappStatus.connected 
                        ? `Connected as ${whatsappStatus.user?.name || whatsappStatus.user?.id || 'Unknown'}`
                        : 'Not Connected'
                      }
                    </Typography>
                  </Box>
                </Box>
                <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
                  <Chip
                    label={whatsappStatus.connected ? 'Connected' : 'Disconnected'}
                    color={whatsappStatus.connected ? 'success' : 'warning'}
                    variant="filled"
                  />
                  {!whatsappStatus.connected && (
                    <Button
                      variant="contained"
                      startIcon={<QrCode />}
                      onClick={() => navigate('/qr-scanner')}
                      size="small"
                    >
                      Connect
                    </Button>
                  )}
                </Box>
              </Box>
            </CardContent>
          </Card>
        </motion.div>

        {/* Statistics Cards */}
        <motion.div variants={itemVariants}>
          <Grid container spacing={3} sx={{ mb: 4 }}>
            <Grid item xs={12} sm={6} md={3}>
              <Card>
                <CardContent>
                  <Box sx={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
                    <Box>
                      <Typography color="text.secondary" gutterBottom>
                        Total Messages
                      </Typography>
                      <Typography variant="h4">
                        {stats.totalMessages.toLocaleString()}
                      </Typography>
                    </Box>
                    <Avatar sx={{ bgcolor: 'primary.main' }}>
                      <Message />
                    </Avatar>
                  </Box>
                </CardContent>
              </Card>
            </Grid>

            <Grid item xs={12} sm={6} md={3}>
              <Card>
                <CardContent>
                  <Box sx={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
                    <Box>
                      <Typography color="text.secondary" gutterBottom>
                        Sent Today
                      </Typography>
                      <Typography variant="h4">
                        {stats.sentToday.toLocaleString()}
                      </Typography>
                    </Box>
                    <Avatar sx={{ bgcolor: 'success.main' }}>
                      <CheckCircle />
                    </Avatar>
                  </Box>
                </CardContent>
              </Card>
            </Grid>

            <Grid item xs={12} sm={6} md={3}>
              <Card>
                <CardContent>
                  <Box sx={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
                    <Box>
                      <Typography color="text.secondary" gutterBottom>
                        Pending
                      </Typography>
                      <Typography variant="h4">
                        {stats.pendingMessages.toLocaleString()}
                      </Typography>
                    </Box>
                    <Avatar sx={{ bgcolor: 'warning.main' }}>
                      <Queue />
                    </Avatar>
                  </Box>
                </CardContent>
              </Card>
            </Grid>

            <Grid item xs={12} sm={6} md={3}>
              <Card>
                <CardContent>
                  <Box sx={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
                    <Box>
                      <Typography color="text.secondary" gutterBottom>
                        Failed
                      </Typography>
                      <Typography variant="h4">
                        {stats.failedMessages.toLocaleString()}
                      </Typography>
                    </Box>
                    <Avatar sx={{ bgcolor: 'error.main' }}>
                      <Error />
                    </Avatar>
                  </Box>
                </CardContent>
              </Card>
            </Grid>
          </Grid>
        </motion.div>

        {/* Quick Actions */}
        <motion.div variants={itemVariants}>
          <Grid container spacing={3} sx={{ mb: 4 }}>
            <Grid item xs={12} md={8}>
              <Card>
                <CardContent>
                  <Box sx={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', mb: 2 }}>
                    <Typography variant="h6">Quick Actions</Typography>
                    <IconButton onClick={() => refetchStats()}>
                      <Refresh />
                    </IconButton>
                  </Box>
                  <Grid container spacing={2}>
                    <Grid item xs={12} sm={6} md={3}>
                      <Button
                        fullWidth
                        variant="contained"
                        startIcon={<Send />}
                        onClick={() => navigate('/send-message')}
                        sx={{ py: 1.5 }}
                      >
                        Send Message
                      </Button>
                    </Grid>
                    <Grid item xs={12} sm={6} md={3}>
                      <Button
                        fullWidth
                        variant="outlined"
                        startIcon={<Message />}
                        onClick={() => navigate('/bulk-message')}
                        sx={{ py: 1.5 }}
                      >
                        Bulk Message
                      </Button>
                    </Grid>
                    <Grid item xs={12} sm={6} md={3}>
                      <Button
                        fullWidth
                        variant="outlined"
                        startIcon={<Queue />}
                        onClick={() => navigate('/message-queue')}
                        sx={{ py: 1.5 }}
                      >
                        View Queue
                      </Button>
                    </Grid>
                    <Grid item xs={12} sm={6} md={3}>
                      <Button
                        fullWidth
                        variant="outlined"
                        startIcon={<QrCode />}
                        onClick={() => navigate('/qr-scanner')}
                        sx={{ py: 1.5 }}
                      >
                        QR Scanner
                      </Button>
                    </Grid>
                  </Grid>
                </CardContent>
              </Card>
            </Grid>

            <Grid item xs={12} md={4}>
              <Card>
                <CardContent>
                  <Typography variant="h6" gutterBottom>
                    System Status
                  </Typography>
                  <Box sx={{ mb: 2 }}>
                    <Box sx={{ display: 'flex', justifyContent: 'space-between', mb: 1 }}>
                      <Typography variant="body2">Gateway Connection</Typography>
                      <Chip
                        label={connected ? 'Online' : 'Offline'}
                        color={connected ? 'success' : 'error'}
                        size="small"
                      />
                    </Box>
                    <Box sx={{ display: 'flex', justifyContent: 'space-between', mb: 1 }}>
                      <Typography variant="body2">WhatsApp Status</Typography>
                      <Chip
                        label={whatsappStatus.connected ? 'Connected' : 'Disconnected'}
                        color={whatsappStatus.connected ? 'success' : 'warning'}
                        size="small"
                      />
                    </Box>
                    <Box sx={{ display: 'flex', justifyContent: 'space-between', mb: 1 }}>
                      <Typography variant="body2">Queue Processing</Typography>
                      <Chip
                        label={queueStats?.data?.isProcessing ? 'Active' : 'Inactive'}
                        color={queueStats?.data?.isProcessing ? 'success' : 'default'}
                        size="small"
                      />
                    </Box>
                  </Box>
                  
                  {stats.pendingMessages > 0 && (
                    <Box>
                      <Typography variant="body2" gutterBottom>
                        Queue Progress
                      </Typography>
                      <LinearProgress
                        variant="determinate"
                        value={((stats.totalMessages - stats.pendingMessages) / stats.totalMessages) * 100}
                        sx={{ mb: 1 }}
                      />
                      <Typography variant="caption" color="text.secondary">
                        {stats.pendingMessages} messages pending
                      </Typography>
                    </Box>
                  )}
                </CardContent>
              </Card>
            </Grid>
          </Grid>
        </motion.div>

        {/* Recent Messages */}
        <motion.div variants={itemVariants}>
          <Card>
            <CardContent>
              <Typography variant="h6" gutterBottom>
                Recent Messages
              </Typography>
              {messages.length === 0 ? (
                <Typography color="text.secondary">
                  No recent messages
                </Typography>
              ) : (
                <Box>
                  {messages.slice(0, 5).map((message, index) => (
                    <Box
                      key={message.id}
                      sx={{
                        p: 2,
                        border: '1px solid',
                        borderColor: 'divider',
                        borderRadius: 1,
                        mb: 1,
                      }}
                    >
                      <Box sx={{ display: 'flex', justifyContent: 'space-between', mb: 1 }}>
                        <Typography variant="subtitle2">
                          {message.from.replace('@s.whatsapp.net', '')}
                        </Typography>
                        <Typography variant="caption" color="text.secondary">
                          {new Date(message.timestamp).toLocaleTimeString()}
                        </Typography>
                      </Box>
                      <Typography variant="body2" color="text.secondary">
                        {message.message.length > 100 
                          ? `${message.message.substring(0, 100)}...`
                          : message.message
                        }
                      </Typography>
                    </Box>
                  ))}
                </Box>
              )}
            </CardContent>
          </Card>
        </motion.div>
      </motion.div>
    </Box>
  );
};

export default Dashboard;
