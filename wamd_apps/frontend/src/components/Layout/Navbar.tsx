import React from 'react';
import {
  AppBar,
  Toolbar,
  Typography,
  IconButton,
  Box,
  Chip,
  Avatar,
} from '@mui/material';
import {
  Menu as MenuIcon,
  WhatsApp,
  Notifications,
} from '@mui/icons-material';
import { useSocket } from '../../contexts/SocketContext';

interface NavbarProps {
  onMenuClick: () => void;
}

const Navbar: React.FC<NavbarProps> = ({ onMenuClick }) => {
  const { whatsappStatus, connected } = useSocket();

  return (
    <AppBar
      position="fixed"
      sx={{
        zIndex: (theme) => theme.zIndex.drawer + 1,
        bgcolor: 'white',
        color: 'text.primary',
        boxShadow: '0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06)',
      }}
    >
      <Toolbar>
        <IconButton
          color="inherit"
          aria-label="open drawer"
          edge="start"
          onClick={onMenuClick}
          sx={{ mr: 2, display: { sm: 'none' } }}
        >
          <MenuIcon />
        </IconButton>

        <Avatar sx={{ bgcolor: 'primary.main', mr: 2 }}>
          <WhatsApp />
        </Avatar>

        <Typography variant="h6" noWrap component="div" sx={{ flexGrow: 1 }}>
          WhatsApp Gateway
        </Typography>

        <Box sx={{ display: 'flex', alignItems: 'center', gap: 2 }}>
          {/* Connection Status */}
          <Chip
            label={connected ? 'Gateway Online' : 'Gateway Offline'}
            color={connected ? 'success' : 'error'}
            size="small"
            variant="outlined"
          />

          {/* WhatsApp Status */}
          <Chip
            label={whatsappStatus.connected ? 'WhatsApp Connected' : 'WhatsApp Disconnected'}
            color={whatsappStatus.connected ? 'success' : 'warning'}
            size="small"
            variant="filled"
          />

          {/* Notifications */}
          <IconButton color="inherit">
            <Notifications />
          </IconButton>
        </Box>
      </Toolbar>
    </AppBar>
  );
};

export default Navbar;
