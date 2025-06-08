import React from 'react';
import {
  Drawer,
  List,
  ListItem,
  ListItemButton,
  ListItemIcon,
  ListItemText,
  Divider,
  Box,
  Typography,
} from '@mui/material';
import {
  Dashboard,
  QrCode,
  Send,
  Message,
  Queue,
  Settings,
  WhatsApp,
} from '@mui/icons-material';
import { useNavigate, useLocation } from 'react-router-dom';

interface SidebarProps {
  open: boolean;
  onClose: () => void;
}

const drawerWidth = 240;

const menuItems = [
  {
    text: 'Dashboard',
    icon: <Dashboard />,
    path: '/dashboard',
  },
  {
    text: 'QR Scanner',
    icon: <QrCode />,
    path: '/qr-scanner',
  },
  {
    text: 'Send Message',
    icon: <Send />,
    path: '/send-message',
  },
  {
    text: 'Bulk Message',
    icon: <Message />,
    path: '/bulk-message',
  },
  {
    text: 'Message Queue',
    icon: <Queue />,
    path: '/message-queue',
  },
  {
    text: 'Settings',
    icon: <Settings />,
    path: '/settings',
  },
];

const Sidebar: React.FC<SidebarProps> = ({ open, onClose }) => {
  const navigate = useNavigate();
  const location = useLocation();

  const handleNavigation = (path: string) => {
    navigate(path);
    onClose(); // Close sidebar on mobile after navigation
  };

  const drawer = (
    <Box>
      {/* Logo/Header */}
      <Box
        sx={{
          p: 2,
          display: 'flex',
          alignItems: 'center',
          gap: 1,
          bgcolor: 'primary.main',
          color: 'white',
          mt: 8, // Account for navbar height
        }}
      >
        <WhatsApp />
        <Typography variant="h6" noWrap>
          WA Gateway
        </Typography>
      </Box>

      <Divider />

      {/* Navigation Menu */}
      <List>
        {menuItems.map((item) => {
          const isActive = location.pathname === item.path || 
                          (item.path === '/dashboard' && location.pathname === '/');
          
          return (
            <ListItem key={item.text} disablePadding>
              <ListItemButton
                onClick={() => handleNavigation(item.path)}
                selected={isActive}
                sx={{
                  '&.Mui-selected': {
                    bgcolor: 'primary.light',
                    color: 'primary.contrastText',
                    '&:hover': {
                      bgcolor: 'primary.main',
                    },
                    '& .MuiListItemIcon-root': {
                      color: 'primary.contrastText',
                    },
                  },
                }}
              >
                <ListItemIcon
                  sx={{
                    color: isActive ? 'primary.contrastText' : 'text.secondary',
                  }}
                >
                  {item.icon}
                </ListItemIcon>
                <ListItemText 
                  primary={item.text}
                  primaryTypographyProps={{
                    fontWeight: isActive ? 600 : 400,
                  }}
                />
              </ListItemButton>
            </ListItem>
          );
        })}
      </List>

      <Divider />

      {/* Footer */}
      <Box sx={{ p: 2, mt: 'auto' }}>
        <Typography variant="caption" color="text.secondary" align="center" display="block">
          WhatsApp Multi-Device Gateway
        </Typography>
        <Typography variant="caption" color="text.secondary" align="center" display="block">
          v1.0.0
        </Typography>
      </Box>
    </Box>
  );

  return (
    <Box
      component="nav"
      sx={{ width: { sm: drawerWidth }, flexShrink: { sm: 0 } }}
    >
      {/* Mobile drawer */}
      <Drawer
        variant="temporary"
        open={open}
        onClose={onClose}
        ModalProps={{
          keepMounted: true, // Better open performance on mobile
        }}
        sx={{
          display: { xs: 'block', sm: 'none' },
          '& .MuiDrawer-paper': {
            boxSizing: 'border-box',
            width: drawerWidth,
          },
        }}
      >
        {drawer}
      </Drawer>

      {/* Desktop drawer */}
      <Drawer
        variant="persistent"
        open={open}
        sx={{
          display: { xs: 'none', sm: 'block' },
          '& .MuiDrawer-paper': {
            boxSizing: 'border-box',
            width: drawerWidth,
          },
        }}
      >
        {drawer}
      </Drawer>
    </Box>
  );
};

export default Sidebar;
