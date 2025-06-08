import React from 'react';
import {
  Box,
  Card,
  CardContent,
  Typography,
  Button,
  Grid,
  Chip,
  LinearProgress,
  IconButton,
  Alert,
} from '@mui/material';
import {
  Refresh,
  Clear,
  Replay,
  Queue,
  CheckCircle,
  Error,
  Schedule,
} from '@mui/icons-material';
import { motion } from 'framer-motion';
import { useQuery } from 'react-query';
import toast from 'react-hot-toast';
import { useSocket } from '../contexts/SocketContext';
import { apiService } from '../services/apiService';

const MessageQueue: React.FC = () => {
  const { whatsappStatus } = useSocket();

  // Fetch queue statistics
  const { data: queueStats, refetch: refetchStats, isLoading } = useQuery(
    'queueStats',
    () => apiService.getQueueStats(),
    {
      refetchInterval: 5000, // Refetch every 5 seconds
    }
  );

  const stats = queueStats?.data || {
    totalMessages: 0,
    sentToday: 0,
    pendingMessages: 0,
    failedMessages: 0,
    totalContacts: 0,
    isProcessing: false,
    intervalMs: 5000,
    maxRetryAttempts: 3,
  };

  const handleClearQueue = async () => {
    if (window.confirm('Are you sure you want to clear all pending messages?')) {
      try {
        const response = await apiService.clearQueue();
        if (response.success) {
          toast.success('Queue cleared successfully');
          refetchStats();
        } else {
          toast.error(response.message || 'Failed to clear queue');
        }
      } catch (error: any) {
        toast.error(error.response?.data?.message || 'Failed to clear queue');
      }
    }
  };

  const handleRetryFailed = async () => {
    try {
      const response = await apiService.retryFailedMessages();
      if (response.success) {
        toast.success(`${response.data?.count || 0} failed messages reset for retry`);
        refetchStats();
      } else {
        toast.error(response.message || 'Failed to retry messages');
      }
    } catch (error: any) {
      toast.error(error.response?.data?.message || 'Failed to retry messages');
    }
  };

  const getQueueProgress = () => {
    if (stats.totalMessages === 0) return 0;
    return ((stats.totalMessages - stats.pendingMessages) / stats.totalMessages) * 100;
  };

  const getProcessingStatus = () => {
    if (!whatsappStatus.connected) return 'WhatsApp Disconnected';
    if (!stats.isProcessing) return 'Queue Inactive';
    if (stats.pendingMessages === 0) return 'Queue Empty';
    return 'Processing Messages';
  };

  const getProcessingColor = () => {
    if (!whatsappStatus.connected) return 'error';
    if (!stats.isProcessing) return 'warning';
    if (stats.pendingMessages === 0) return 'success';
    return 'info';
  };

  return (
    <Box sx={{ p: 3 }}>
      <motion.div
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ duration: 0.5 }}
      >
        {/* Header */}
        <Box sx={{ mb: 4 }}>
          <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
            <Box>
              <Typography variant="h4" component="h1" gutterBottom>
                Message Queue
              </Typography>
              <Typography variant="body1" color="text.secondary">
                Monitor and manage your message queue
              </Typography>
            </Box>
            <IconButton onClick={() => refetchStats()} disabled={isLoading}>
              <Refresh />
            </IconButton>
          </Box>
        </Box>

        {/* Status Alert */}
        {!whatsappStatus.connected && (
          <Alert severity="warning" sx={{ mb: 3 }}>
            WhatsApp is disconnected. Queue processing is paused until connection is restored.
          </Alert>
        )}

        {/* Queue Statistics */}
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
                  <Queue color="primary" sx={{ fontSize: 40 }} />
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
                  <Schedule color="warning" sx={{ fontSize: 40 }} />
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
                  <CheckCircle color="success" sx={{ fontSize: 40 }} />
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
                  <Error color="error" sx={{ fontSize: 40 }} />
                </Box>
              </CardContent>
            </Card>
          </Grid>
        </Grid>

        {/* Queue Status */}
        <Grid container spacing={3} sx={{ mb: 4 }}>
          <Grid item xs={12} md={8}>
            <Card>
              <CardContent>
                <Typography variant="h6" gutterBottom>
                  Queue Status
                </Typography>

                <Box sx={{ mb: 3 }}>
                  <Box sx={{ display: 'flex', justifyContent: 'space-between', mb: 1 }}>
                    <Typography variant="body2">Processing Status</Typography>
                    <Chip
                      label={getProcessingStatus()}
                      color={getProcessingColor() as any}
                      size="small"
                    />
                  </Box>

                  {stats.pendingMessages > 0 && (
                    <>
                      <LinearProgress
                        variant="determinate"
                        value={getQueueProgress()}
                        sx={{ mb: 1, height: 8, borderRadius: 4 }}
                      />
                      <Typography variant="caption" color="text.secondary">
                        {Math.round(getQueueProgress())}% complete • {stats.pendingMessages} messages remaining
                      </Typography>
                    </>
                  )}
                </Box>

                <Grid container spacing={2}>
                  <Grid item>
                    <Typography variant="body2" color="text.secondary">
                      Processing Interval: {stats.intervalMs / 1000}s
                    </Typography>
                  </Grid>
                  <Grid item>
                    <Typography variant="body2" color="text.secondary">
                      Max Retry Attempts: {stats.maxRetryAttempts}
                    </Typography>
                  </Grid>
                  <Grid item>
                    <Typography variant="body2" color="text.secondary">
                      WhatsApp Status: {whatsappStatus.connected ? 'Connected' : 'Disconnected'}
                    </Typography>
                  </Grid>
                </Grid>
              </CardContent>
            </Card>
          </Grid>

          <Grid item xs={12} md={4}>
            <Card>
              <CardContent>
                <Typography variant="h6" gutterBottom>
                  Queue Actions
                </Typography>

                <Box sx={{ display: 'flex', flexDirection: 'column', gap: 2 }}>
                  <Button
                    variant="outlined"
                    startIcon={<Refresh />}
                    onClick={() => refetchStats()}
                    disabled={isLoading}
                    fullWidth
                  >
                    Refresh Stats
                  </Button>

                  <Button
                    variant="outlined"
                    color="warning"
                    startIcon={<Replay />}
                    onClick={handleRetryFailed}
                    disabled={stats.failedMessages === 0}
                    fullWidth
                  >
                    Retry Failed ({stats.failedMessages})
                  </Button>

                  <Button
                    variant="outlined"
                    color="error"
                    startIcon={<Clear />}
                    onClick={handleClearQueue}
                    disabled={stats.pendingMessages === 0}
                    fullWidth
                  >
                    Clear Queue ({stats.pendingMessages})
                  </Button>
                </Box>
              </CardContent>
            </Card>
          </Grid>
        </Grid>

        {/* Queue Information */}
        <Card>
          <CardContent>
            <Typography variant="h6" gutterBottom>
              How Message Queue Works
            </Typography>
            <Grid container spacing={3}>
              <Grid item xs={12} md={6}>
                <Typography variant="subtitle2" gutterBottom>
                  Automatic Processing
                </Typography>
                <Typography variant="body2" color="text.secondary" paragraph>
                  The queue automatically processes pending messages when WhatsApp is connected. 
                  Messages are sent with configurable delays to avoid rate limiting.
                </Typography>

                <Typography variant="subtitle2" gutterBottom>
                  Retry Mechanism
                </Typography>
                <Typography variant="body2" color="text.secondary" paragraph>
                  Failed messages are automatically retried up to {stats.maxRetryAttempts} times. 
                  After exceeding the retry limit, messages are marked as failed.
                </Typography>
              </Grid>
              <Grid item xs={12} md={6}>
                <Typography variant="subtitle2" gutterBottom>
                  Queue States
                </Typography>
                <Typography variant="body2" color="text.secondary" paragraph>
                  • <strong>Pending:</strong> Waiting to be sent<br />
                  • <strong>Sent:</strong> Successfully delivered<br />
                  • <strong>Failed:</strong> Exceeded retry attempts<br />
                  • <strong>Processing:</strong> Currently being sent
                </Typography>

                <Typography variant="subtitle2" gutterBottom>
                  Best Practices
                </Typography>
                <Typography variant="body2" color="text.secondary">
                  • Keep WhatsApp connected for automatic processing<br />
                  • Monitor failed messages and retry when needed<br />
                  • Clear old pending messages periodically
                </Typography>
              </Grid>
            </Grid>
          </CardContent>
        </Card>
      </motion.div>
    </Box>
  );
};

export default MessageQueue;
