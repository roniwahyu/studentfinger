import React from 'react';
import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';
import { ThemeProvider, createTheme } from '@mui/material/styles';
import { CssBaseline, Box } from '@mui/material';
import { QueryClient, QueryClientProvider } from 'react-query';
import { Toaster } from 'react-hot-toast';

// Components
import Navbar from './components/Layout/Navbar';
import Sidebar from './components/Layout/Sidebar';
import Dashboard from './pages/Dashboard';
import QRScanner from './pages/QRScanner';
import MessageSender from './pages/MessageSender';
import BulkMessage from './pages/BulkMessage';
import MessageQueue from './pages/MessageQueue';
import Settings from './pages/Settings';
import { SocketProvider } from './contexts/SocketContext';

// Create theme
const theme = createTheme({
  palette: {
    mode: 'light',
    primary: {
      main: '#25D366', // WhatsApp green
      light: '#4FE584',
      dark: '#1BA94C',
    },
    secondary: {
      main: '#128C7E', // WhatsApp dark green
      light: '#4FB3A9',
      dark: '#0D6157',
    },
    background: {
      default: '#F8F9FA',
      paper: '#FFFFFF',
    },
    text: {
      primary: '#1F2937',
      secondary: '#6B7280',
    },
  },
  typography: {
    fontFamily: '"Inter", "Roboto", "Helvetica", "Arial", sans-serif',
    h1: {
      fontWeight: 700,
    },
    h2: {
      fontWeight: 600,
    },
    h3: {
      fontWeight: 600,
    },
    h4: {
      fontWeight: 600,
    },
    h5: {
      fontWeight: 500,
    },
    h6: {
      fontWeight: 500,
    },
  },
  shape: {
    borderRadius: 12,
  },
  components: {
    MuiButton: {
      styleOverrides: {
        root: {
          textTransform: 'none',
          borderRadius: 8,
          fontWeight: 500,
        },
      },
    },
    MuiCard: {
      styleOverrides: {
        root: {
          boxShadow: '0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06)',
          borderRadius: 12,
        },
      },
    },
    MuiPaper: {
      styleOverrides: {
        root: {
          boxShadow: '0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06)',
        },
      },
    },
  },
});

// Create query client
const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      refetchOnWindowFocus: false,
      retry: 1,
      staleTime: 5 * 60 * 1000, // 5 minutes
    },
  },
});

const App: React.FC = () => {
  const [sidebarOpen, setSidebarOpen] = React.useState(false);

  const handleSidebarToggle = () => {
    setSidebarOpen(!sidebarOpen);
  };

  return (
    <QueryClientProvider client={queryClient}>
      <ThemeProvider theme={theme}>
        <CssBaseline />
        <SocketProvider>
          <Router>
            <Box sx={{ display: 'flex', minHeight: '100vh' }}>
              <Navbar onMenuClick={handleSidebarToggle} />
              <Sidebar open={sidebarOpen} onClose={() => setSidebarOpen(false)} />
              
              <Box
                component="main"
                sx={{
                  flexGrow: 1,
                  p: 3,
                  mt: 8, // Account for navbar height
                  ml: { sm: sidebarOpen ? '240px' : 0 },
                  transition: 'margin-left 0.3s ease',
                }}
              >
                <Routes>
                  <Route path="/" element={<Dashboard />} />
                  <Route path="/dashboard" element={<Dashboard />} />
                  <Route path="/qr-scanner" element={<QRScanner />} />
                  <Route path="/send-message" element={<MessageSender />} />
                  <Route path="/bulk-message" element={<BulkMessage />} />
                  <Route path="/message-queue" element={<MessageQueue />} />
                  <Route path="/settings" element={<Settings />} />
                </Routes>
              </Box>
            </Box>
          </Router>
          
          <Toaster
            position="top-right"
            toastOptions={{
              duration: 4000,
              style: {
                background: '#363636',
                color: '#fff',
              },
              success: {
                style: {
                  background: '#25D366',
                },
              },
              error: {
                style: {
                  background: '#DC2626',
                },
              },
            }}
          />
        </SocketProvider>
      </ThemeProvider>
    </QueryClientProvider>
  );
};

export default App;
