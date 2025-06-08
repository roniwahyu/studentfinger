import React, { useState } from 'react';
import {
  Box,
  Card,
  CardContent,
  Typography,
  TextField,
  Button,
  Alert,
  Grid,
  FormControl,
  InputLabel,
  Select,
  MenuItem,
  Chip,
  CircularProgress,
} from '@mui/material';
import {
  Send,
  Schedule,
  Person,
  Message,
} from '@mui/icons-material';
import { motion } from 'framer-motion';
import { useForm, Controller } from 'react-hook-form';
import { useQuery } from 'react-query';
import toast from 'react-hot-toast';
import { useSocket } from '../contexts/SocketContext';
import { apiService } from '../services/apiService';

interface MessageForm {
  phoneNumber: string;
  message: string;
  delay: number;
  template?: string;
}

const MessageSender: React.FC = () => {
  const { whatsappStatus } = useSocket();
  const [loading, setLoading] = useState(false);

  const { control, handleSubmit, reset, setValue, watch } = useForm<MessageForm>({
    defaultValues: {
      phoneNumber: '',
      message: '',
      delay: 0,
      template: '',
    },
  });

  // Fetch templates
  const { data: templatesData } = useQuery(
    'templates',
    () => apiService.getTemplates(),
    {
      onError: (error) => {
        console.error('Failed to fetch templates:', error);
      },
    }
  );

  const templates = templatesData?.data || [];
  const selectedTemplate = watch('template');

  const onSubmit = async (data: MessageForm) => {
    if (!whatsappStatus.connected) {
      toast.error('WhatsApp is not connected. Message will be queued.');
    }

    setLoading(true);
    try {
      const response = await apiService.sendMessage({
        to: data.phoneNumber,
        message: data.message,
        delay: data.delay > 0 ? data.delay : undefined,
      });

      if (response.success) {
        toast.success(
          response.message || 
          (data.delay > 0 ? 'Message scheduled successfully!' : 'Message sent successfully!')
        );
        reset();
      } else {
        toast.error(response.message || 'Failed to send message');
      }
    } catch (error: any) {
      toast.error(error.response?.data?.message || 'Failed to send message');
    } finally {
      setLoading(false);
    }
  };

  const handleTemplateSelect = (templateId: string) => {
    const template = templates.find(t => t.id.toString() === templateId);
    if (template) {
      setValue('message', template.template_content);
    }
  };

  const formatPhoneNumber = (value: string) => {
    // Remove all non-numeric characters
    const cleaned = value.replace(/\D/g, '');
    
    // Format as Indonesian phone number
    if (cleaned.startsWith('0')) {
      return '+62' + cleaned.substring(1);
    } else if (cleaned.startsWith('62')) {
      return '+' + cleaned;
    } else if (cleaned.length > 0) {
      return '+62' + cleaned;
    }
    return value;
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
          <Typography variant="h4" component="h1" gutterBottom>
            Send Message
          </Typography>
          <Typography variant="body1" color="text.secondary">
            Send individual WhatsApp messages
          </Typography>
        </Box>

        {/* Connection Status Alert */}
        {!whatsappStatus.connected && (
          <Alert severity="warning" sx={{ mb: 3 }}>
            WhatsApp is not connected. Messages will be queued and sent when connection is restored.
          </Alert>
        )}

        <Grid container spacing={3}>
          {/* Message Form */}
          <Grid item xs={12} md={8}>
            <Card>
              <CardContent sx={{ p: 4 }}>
                <Typography variant="h6" gutterBottom sx={{ display: 'flex', alignItems: 'center' }}>
                  <Message sx={{ mr: 1 }} />
                  Compose Message
                </Typography>

                <Box component="form" onSubmit={handleSubmit(onSubmit)} sx={{ mt: 3 }}>
                  <Grid container spacing={3}>
                    {/* Phone Number */}
                    <Grid item xs={12}>
                      <Controller
                        name="phoneNumber"
                        control={control}
                        rules={{
                          required: 'Phone number is required',
                          pattern: {
                            value: /^\+?[1-9]\d{1,14}$/,
                            message: 'Please enter a valid phone number',
                          },
                        }}
                        render={({ field, fieldState }) => (
                          <TextField
                            {...field}
                            fullWidth
                            label="Phone Number"
                            placeholder="+62812345678 or 0812345678"
                            error={!!fieldState.error}
                            helperText={fieldState.error?.message || 'Enter phone number with country code'}
                            InputProps={{
                              startAdornment: <Person sx={{ mr: 1, color: 'text.secondary' }} />,
                            }}
                            onChange={(e) => {
                              const formatted = formatPhoneNumber(e.target.value);
                              field.onChange(formatted);
                            }}
                          />
                        )}
                      />
                    </Grid>

                    {/* Template Selection */}
                    {templates.length > 0 && (
                      <Grid item xs={12}>
                        <Controller
                          name="template"
                          control={control}
                          render={({ field }) => (
                            <FormControl fullWidth>
                              <InputLabel>Message Template (Optional)</InputLabel>
                              <Select
                                {...field}
                                label="Message Template (Optional)"
                                onChange={(e) => {
                                  field.onChange(e.target.value);
                                  handleTemplateSelect(e.target.value as string);
                                }}
                              >
                                <MenuItem value="">
                                  <em>None</em>
                                </MenuItem>
                                {templates.map((template) => (
                                  <MenuItem key={template.id} value={template.id.toString()}>
                                    {template.template_name}
                                  </MenuItem>
                                ))}
                              </Select>
                            </FormControl>
                          )}
                        />
                      </Grid>
                    )}

                    {/* Message Content */}
                    <Grid item xs={12}>
                      <Controller
                        name="message"
                        control={control}
                        rules={{
                          required: 'Message is required',
                          minLength: {
                            value: 1,
                            message: 'Message cannot be empty',
                          },
                        }}
                        render={({ field, fieldState }) => (
                          <TextField
                            {...field}
                            fullWidth
                            multiline
                            rows={6}
                            label="Message"
                            placeholder="Type your message here..."
                            error={!!fieldState.error}
                            helperText={
                              fieldState.error?.message || 
                              `${field.value?.length || 0} characters`
                            }
                          />
                        )}
                      />
                    </Grid>

                    {/* Delay */}
                    <Grid item xs={12} sm={6}>
                      <Controller
                        name="delay"
                        control={control}
                        render={({ field }) => (
                          <TextField
                            {...field}
                            fullWidth
                            type="number"
                            label="Delay (seconds)"
                            placeholder="0"
                            helperText="Optional delay before sending (0 = send immediately)"
                            InputProps={{
                              startAdornment: <Schedule sx={{ mr: 1, color: 'text.secondary' }} />,
                            }}
                            inputProps={{ min: 0, max: 3600 }}
                          />
                        )}
                      />
                    </Grid>

                    {/* Submit Button */}
                    <Grid item xs={12}>
                      <Button
                        type="submit"
                        variant="contained"
                        size="large"
                        startIcon={loading ? <CircularProgress size={20} /> : <Send />}
                        disabled={loading}
                        sx={{ minWidth: 150 }}
                      >
                        {loading ? 'Sending...' : 'Send Message'}
                      </Button>
                    </Grid>
                  </Grid>
                </Box>
              </CardContent>
            </Card>
          </Grid>

          {/* Info Panel */}
          <Grid item xs={12} md={4}>
            <Card>
              <CardContent>
                <Typography variant="h6" gutterBottom>
                  Message Info
                </Typography>

                <Box sx={{ mb: 3 }}>
                  <Typography variant="body2" color="text.secondary" gutterBottom>
                    Connection Status
                  </Typography>
                  <Chip
                    label={whatsappStatus.connected ? 'Connected' : 'Disconnected'}
                    color={whatsappStatus.connected ? 'success' : 'warning'}
                    size="small"
                  />
                </Box>

                <Box sx={{ mb: 3 }}>
                  <Typography variant="body2" color="text.secondary" gutterBottom>
                    Phone Number Format
                  </Typography>
                  <Typography variant="body2">
                    • +62812345678 (with country code)
                  </Typography>
                  <Typography variant="body2">
                    • 0812345678 (will be converted)
                  </Typography>
                </Box>

                <Box sx={{ mb: 3 }}>
                  <Typography variant="body2" color="text.secondary" gutterBottom>
                    Message Features
                  </Typography>
                  <Typography variant="body2">
                    • Text messages
                  </Typography>
                  <Typography variant="body2">
                    • Emoji support 😊
                  </Typography>
                  <Typography variant="body2">
                    • Delayed sending
                  </Typography>
                  <Typography variant="body2">
                    • Message templates
                  </Typography>
                </Box>

                {templates.length > 0 && (
                  <Box>
                    <Typography variant="body2" color="text.secondary" gutterBottom>
                      Available Templates
                    </Typography>
                    {templates.slice(0, 3).map((template) => (
                      <Chip
                        key={template.id}
                        label={template.template_name}
                        size="small"
                        sx={{ mr: 1, mb: 1 }}
                        onClick={() => handleTemplateSelect(template.id.toString())}
                        clickable
                      />
                    ))}
                    {templates.length > 3 && (
                      <Typography variant="caption" color="text.secondary">
                        +{templates.length - 3} more templates
                      </Typography>
                    )}
                  </Box>
                )}
              </CardContent>
            </Card>
          </Grid>
        </Grid>
      </motion.div>
    </Box>
  );
};

export default MessageSender;
