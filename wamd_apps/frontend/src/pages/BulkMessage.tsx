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
  Chip,
  CircularProgress,
  List,
  ListItem,
  ListItemText,
  IconButton,
  Divider,
} from '@mui/material';
import {
  Send,
  Delete,
  Add,
  Upload,
  Group,
} from '@mui/icons-material';
import { motion } from 'framer-motion';
import { useForm, Controller } from 'react-hook-form';
import toast from 'react-hot-toast';
import { useSocket } from '../contexts/SocketContext';
import { apiService } from '../services/apiService';

interface BulkMessageForm {
  message: string;
  delay: number;
}

const BulkMessage: React.FC = () => {
  const { whatsappStatus } = useSocket();
  const [loading, setLoading] = useState(false);
  const [contacts, setContacts] = useState<string[]>([]);
  const [newContact, setNewContact] = useState('');

  const { control, handleSubmit, reset } = useForm<BulkMessageForm>({
    defaultValues: {
      message: '',
      delay: 1,
    },
  });

  const addContact = () => {
    if (newContact.trim()) {
      const formatted = formatPhoneNumber(newContact.trim());
      if (!contacts.includes(formatted)) {
        setContacts([...contacts, formatted]);
        setNewContact('');
      } else {
        toast.error('Contact already added');
      }
    }
  };

  const removeContact = (index: number) => {
    setContacts(contacts.filter((_, i) => i !== index));
  };

  const handleFileUpload = (event: React.ChangeEvent<HTMLInputElement>) => {
    const file = event.target.files?.[0];
    if (file) {
      const reader = new FileReader();
      reader.onload = (e) => {
        const text = e.target?.result as string;
        const lines = text.split('\n').map(line => line.trim()).filter(line => line);
        const newContacts = lines.map(formatPhoneNumber);
        const uniqueContacts = [...new Set([...contacts, ...newContacts])];
        setContacts(uniqueContacts);
        toast.success(`Added ${newContacts.length} contacts`);
      };
      reader.readAsText(file);
    }
  };

  const formatPhoneNumber = (value: string) => {
    const cleaned = value.replace(/\D/g, '');
    if (cleaned.startsWith('0')) {
      return '+62' + cleaned.substring(1);
    } else if (cleaned.startsWith('62')) {
      return '+' + cleaned;
    } else if (cleaned.length > 0) {
      return '+62' + cleaned;
    }
    return value;
  };

  const onSubmit = async (data: BulkMessageForm) => {
    if (contacts.length === 0) {
      toast.error('Please add at least one contact');
      return;
    }

    if (!whatsappStatus.connected) {
      toast.error('WhatsApp is not connected. Messages will be queued.');
    }

    setLoading(true);
    try {
      const response = await apiService.sendBulkMessage({
        contacts: contacts,
        message: data.message,
        delay: data.delay,
      });

      if (response.success) {
        toast.success(`Bulk message sent to ${contacts.length} contacts!`);
        reset();
        setContacts([]);
      } else {
        toast.error(response.message || 'Failed to send bulk message');
      }
    } catch (error: any) {
      toast.error(error.response?.data?.message || 'Failed to send bulk message');
    } finally {
      setLoading(false);
    }
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
            Bulk Message
          </Typography>
          <Typography variant="body1" color="text.secondary">
            Send messages to multiple contacts at once
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
                  <Group sx={{ mr: 1 }} />
                  Bulk Message Composer
                </Typography>

                <Box component="form" onSubmit={handleSubmit(onSubmit)} sx={{ mt: 3 }}>
                  <Grid container spacing={3}>
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
                            placeholder="Type your bulk message here..."
                            error={!!fieldState.error}
                            helperText={
                              fieldState.error?.message || 
                              `${field.value?.length || 0} characters • Will be sent to ${contacts.length} contacts`
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
                            label="Delay between messages (seconds)"
                            placeholder="1"
                            helperText="Delay between each message to avoid rate limiting"
                            inputProps={{ min: 1, max: 60 }}
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
                        disabled={loading || contacts.length === 0}
                        sx={{ minWidth: 200 }}
                      >
                        {loading ? 'Sending...' : `Send to ${contacts.length} Contacts`}
                      </Button>
                    </Grid>
                  </Grid>
                </Box>
              </CardContent>
            </Card>
          </Grid>

          {/* Contacts Panel */}
          <Grid item xs={12} md={4}>
            <Card>
              <CardContent>
                <Typography variant="h6" gutterBottom>
                  Contacts ({contacts.length})
                </Typography>

                {/* Add Contact */}
                <Box sx={{ mb: 3 }}>
                  <TextField
                    fullWidth
                    size="small"
                    label="Add Contact"
                    placeholder="+62812345678"
                    value={newContact}
                    onChange={(e) => setNewContact(e.target.value)}
                    onKeyPress={(e) => {
                      if (e.key === 'Enter') {
                        e.preventDefault();
                        addContact();
                      }
                    }}
                    InputProps={{
                      endAdornment: (
                        <IconButton onClick={addContact} size="small">
                          <Add />
                        </IconButton>
                      ),
                    }}
                  />
                </Box>

                {/* Upload File */}
                <Box sx={{ mb: 3 }}>
                  <input
                    accept=".txt,.csv"
                    style={{ display: 'none' }}
                    id="upload-contacts"
                    type="file"
                    onChange={handleFileUpload}
                  />
                  <label htmlFor="upload-contacts">
                    <Button
                      variant="outlined"
                      component="span"
                      startIcon={<Upload />}
                      fullWidth
                      size="small"
                    >
                      Upload File
                    </Button>
                  </label>
                  <Typography variant="caption" color="text.secondary" display="block" sx={{ mt: 1 }}>
                    Upload .txt or .csv file with phone numbers
                  </Typography>
                </Box>

                <Divider sx={{ mb: 2 }} />

                {/* Contacts List */}
                {contacts.length === 0 ? (
                  <Typography color="text.secondary" variant="body2">
                    No contacts added yet
                  </Typography>
                ) : (
                  <List dense sx={{ maxHeight: 300, overflow: 'auto' }}>
                    {contacts.map((contact, index) => (
                      <ListItem
                        key={index}
                        secondaryAction={
                          <IconButton
                            edge="end"
                            size="small"
                            onClick={() => removeContact(index)}
                          >
                            <Delete />
                          </IconButton>
                        }
                      >
                        <ListItemText
                          primary={contact}
                          primaryTypographyProps={{ variant: 'body2' }}
                        />
                      </ListItem>
                    ))}
                  </List>
                )}

                {contacts.length > 0 && (
                  <Box sx={{ mt: 2 }}>
                    <Button
                      variant="outlined"
                      color="error"
                      size="small"
                      onClick={() => setContacts([])}
                      fullWidth
                    >
                      Clear All
                    </Button>
                  </Box>
                )}
              </CardContent>
            </Card>

            {/* Info Card */}
            <Card sx={{ mt: 2 }}>
              <CardContent>
                <Typography variant="h6" gutterBottom>
                  Bulk Message Tips
                </Typography>
                <Typography variant="body2" sx={{ mb: 1 }}>
                  • Add delay between messages to avoid rate limiting
                </Typography>
                <Typography variant="body2" sx={{ mb: 1 }}>
                  • Use proper phone number format (+62...)
                </Typography>
                <Typography variant="body2" sx={{ mb: 1 }}>
                  • Upload contacts from .txt or .csv files
                </Typography>
                <Typography variant="body2">
                  • Messages will be queued if WhatsApp is disconnected
                </Typography>
              </CardContent>
            </Card>
          </Grid>
        </Grid>
      </motion.div>
    </Box>
  );
};

export default BulkMessage;
