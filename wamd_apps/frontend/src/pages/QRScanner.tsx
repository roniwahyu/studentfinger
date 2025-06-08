import React, { useState, useEffect } from 'react';
import {
  Box,
  Card,
  CardContent,
  Typography,
  Button,
  Alert,
  CircularProgress,
  Chip,
  Avatar,
  Stepper,
  Step,
  StepLabel,
  StepContent,
} from '@mui/material';
import {
  QrCode,
  Refresh,
  CheckCircle,
  PhoneAndroid,
  WhatsApp,
  PowerOff,
} from '@mui/icons-material';
import { motion } from 'framer-motion';
import { useSocket } from '../contexts/SocketContext';
import { apiService } from '../services/apiService';
import toast from 'react-hot-toast';

const QRScanner: React.FC = () => {
  const { whatsappStatus, qrCode, connected } = useSocket();
  const [loading, setLoading] = useState(false);
  const [activeStep, setActiveStep] = useState(0);

  useEffect(() => {
    if (whatsappStatus.connected) {
      setActiveStep(3); // Connected step
    } else if (qrCode) {
      setActiveStep(1); // QR code available step
    } else {
      setActiveStep(0); // Initial step
    }
  }, [whatsappStatus.connected, qrCode]);

  const handleRefresh = async () => {
    setLoading(true);
    try {
      // Force refresh by restarting the connection
      await apiService.restartConnection();
      toast.success('Connection refresh initiated');
    } catch (error) {
      toast.error('Failed to refresh connection');
    } finally {
      setLoading(false);
    }
  };

  const handleDisconnect = async () => {
    setLoading(true);
    try {
      await apiService.disconnect();
      toast.success('WhatsApp disconnected');
    } catch (error) {
      toast.error('Failed to disconnect');
    } finally {
      setLoading(false);
    }
  };

  const steps = [
    {
      label: 'Initialize Connection',
      description: 'Starting WhatsApp gateway connection...',
    },
    {
      label: 'Scan QR Code',
      description: 'Use your WhatsApp mobile app to scan the QR code',
    },
    {
      label: 'Authenticating',
      description: 'Verifying your WhatsApp account...',
    },
    {
      label: 'Connected',
      description: 'WhatsApp is now connected and ready to use',
    },
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
            WhatsApp QR Scanner
          </Typography>
          <Typography variant="body1" color="text.secondary">
            Connect your WhatsApp device by scanning the QR code
          </Typography>
        </Box>

        {/* Connection Status Alert */}
        {!connected && (
          <Alert severity="warning" sx={{ mb: 3 }}>
            Gateway connection is offline. Please check your server connection.
          </Alert>
        )}

        <Box sx={{ display: 'flex', gap: 3, flexDirection: { xs: 'column', md: 'row' } }}>
          {/* QR Code Section */}
          <Card sx={{ flex: 1, maxWidth: 500 }}>
            <CardContent sx={{ textAlign: 'center', p: 4 }}>
              <Box sx={{ mb: 3 }}>
                <Avatar
                  sx={{
                    bgcolor: whatsappStatus.connected ? 'success.main' : 'primary.main',
                    width: 64,
                    height: 64,
                    mx: 'auto',
                    mb: 2,
                  }}
                >
                  {whatsappStatus.connected ? <CheckCircle /> : <QrCode />}
                </Avatar>
                <Typography variant="h5" gutterBottom>
                  {whatsappStatus.connected ? 'Connected!' : 'Scan QR Code'}
                </Typography>
                <Chip
                  label={whatsappStatus.connected ? 'Connected' : 'Waiting for scan'}
                  color={whatsappStatus.connected ? 'success' : 'warning'}
                  sx={{ mb: 2 }}
                />
              </Box>

              {/* QR Code Display */}
              <Box
                sx={{
                  minHeight: 300,
                  display: 'flex',
                  alignItems: 'center',
                  justifyContent: 'center',
                  border: '2px dashed',
                  borderColor: 'divider',
                  borderRadius: 2,
                  mb: 3,
                  bgcolor: 'background.default',
                }}
              >
                {whatsappStatus.connected ? (
                  <motion.div
                    initial={{ scale: 0 }}
                    animate={{ scale: 1 }}
                    transition={{ duration: 0.5 }}
                  >
                    <Box sx={{ textAlign: 'center' }}>
                      <CheckCircle sx={{ fontSize: 80, color: 'success.main', mb: 2 }} />
                      <Typography variant="h6" color="success.main">
                        WhatsApp Connected!
                      </Typography>
                      <Typography variant="body2" color="text.secondary">
                        Connected as: {whatsappStatus.user?.name || whatsappStatus.user?.id || 'Unknown'}
                      </Typography>
                    </Box>
                  </motion.div>
                ) : qrCode ? (
                  <motion.div
                    initial={{ opacity: 0 }}
                    animate={{ opacity: 1 }}
                    transition={{ duration: 0.5 }}
                  >
                    <img
                      src={qrCode}
                      alt="WhatsApp QR Code"
                      style={{
                        maxWidth: '100%',
                        maxHeight: 280,
                        borderRadius: 8,
                      }}
                    />
                  </motion.div>
                ) : (
                  <Box sx={{ textAlign: 'center' }}>
                    <CircularProgress size={60} sx={{ mb: 2 }} />
                    <Typography variant="body1" color="text.secondary">
                      {connected ? 'Generating QR Code...' : 'Connecting to gateway...'}
                    </Typography>
                  </Box>
                )}
              </Box>

              {/* Action Buttons */}
              <Box sx={{ display: 'flex', gap: 2, justifyContent: 'center' }}>
                {whatsappStatus.connected ? (
                  <Button
                    variant="outlined"
                    color="error"
                    startIcon={<PowerOff />}
                    onClick={handleDisconnect}
                    disabled={loading}
                  >
                    Disconnect
                  </Button>
                ) : (
                  <Button
                    variant="contained"
                    startIcon={loading ? <CircularProgress size={20} /> : <Refresh />}
                    onClick={handleRefresh}
                    disabled={loading || !connected}
                  >
                    {loading ? 'Refreshing...' : 'Refresh QR Code'}
                  </Button>
                )}
              </Box>
            </CardContent>
          </Card>

          {/* Instructions Section */}
          <Card sx={{ flex: 1 }}>
            <CardContent sx={{ p: 4 }}>
              <Typography variant="h6" gutterBottom>
                Connection Steps
              </Typography>

              <Stepper activeStep={activeStep} orientation="vertical">
                {steps.map((step, index) => (
                  <Step key={step.label}>
                    <StepLabel>
                      <Typography variant="subtitle1">{step.label}</Typography>
                    </StepLabel>
                    <StepContent>
                      <Typography variant="body2" color="text.secondary">
                        {step.description}
                      </Typography>
                    </StepContent>
                  </Step>
                ))}
              </Stepper>

              {!whatsappStatus.connected && (
                <Box sx={{ mt: 4, p: 3, bgcolor: 'background.default', borderRadius: 2 }}>
                  <Typography variant="h6" gutterBottom sx={{ display: 'flex', alignItems: 'center' }}>
                    <PhoneAndroid sx={{ mr: 1 }} />
                    How to Scan
                  </Typography>
                  <Box component="ol" sx={{ pl: 2, m: 0 }}>
                    <Typography component="li" variant="body2" sx={{ mb: 1 }}>
                      Open WhatsApp on your phone
                    </Typography>
                    <Typography component="li" variant="body2" sx={{ mb: 1 }}>
                      Go to <strong>Settings</strong> → <strong>Linked Devices</strong>
                    </Typography>
                    <Typography component="li" variant="body2" sx={{ mb: 1 }}>
                      Tap <strong>"Link a Device"</strong>
                    </Typography>
                    <Typography component="li" variant="body2" sx={{ mb: 1 }}>
                      Point your camera at the QR code above
                    </Typography>
                  </Box>
                </Box>
              )}

              {whatsappStatus.connected && (
                <Box sx={{ mt: 4, p: 3, bgcolor: 'success.light', borderRadius: 2 }}>
                  <Typography variant="h6" gutterBottom sx={{ display: 'flex', alignItems: 'center' }}>
                    <WhatsApp sx={{ mr: 1 }} />
                    Connection Details
                  </Typography>
                  <Typography variant="body2" sx={{ mb: 1 }}>
                    <strong>User:</strong> {whatsappStatus.user?.name || 'N/A'}
                  </Typography>
                  <Typography variant="body2" sx={{ mb: 1 }}>
                    <strong>ID:</strong> {whatsappStatus.user?.id || 'N/A'}
                  </Typography>
                  <Typography variant="body2">
                    <strong>Connected at:</strong> {new Date(whatsappStatus.timestamp).toLocaleString()}
                  </Typography>
                </Box>
              )}
            </CardContent>
          </Card>
        </Box>
      </motion.div>
    </Box>
  );
};

export default QRScanner;
