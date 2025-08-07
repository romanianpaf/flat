import React from 'react';
import { useNavigate } from 'react-router-dom';
import {
  FiMenu, FiHome, FiUsers, FiShield, FiBarChart, FiLogOut, FiActivity, FiZap, FiSettings, FiGrid,
} from 'react-icons/fi';
import {
  Box, Flex, HStack, VStack, Text, Button, IconButton, useDisclosure, useColorModeValue,
  Drawer, DrawerBody, DrawerHeader, DrawerOverlay, DrawerContent, DrawerCloseButton,
  Avatar, Menu, MenuButton, MenuList, MenuItem, useToast, Divider,
} from '@chakra-ui/react';

interface User {
  id: number;
  name: string;
  email: string;
  role: string;
  tenant_id?: number;
}

interface NavigationProps {
  user: User | null;
  onLogout: () => void;
}

interface NavItemProps {
  item: {
    label: string;
    path: string;
    icon: React.ComponentType;
    adminOnly?: boolean;
  };
  onClick?: () => void;
}

const NavItem: React.FC<NavItemProps> = ({ item, onClick }) => {
  const navigate = useNavigate();
  const isActive = window.location.pathname === item.path;

  return (
    <Button
      variant={isActive ? 'solid' : 'ghost'}
      colorScheme={isActive ? 'brand' : 'gray'}
      leftIcon={<item.icon />}
      onClick={() => {
        navigate(item.path);
        onClick?.();
      }}
      size="sm"
    >
      {item.label}
    </Button>
  );
};

const Navigation: React.FC<NavigationProps> = ({ user, onLogout }) => {
  const { isOpen, onOpen, onClose } = useDisclosure();
  const navigate = useNavigate();
  const toast = useToast();
  const bgColor = useColorModeValue('white', 'gray.800');
  const borderColor = useColorModeValue('gray.200', 'gray.700');

  const handleLogout = () => {
    onLogout();
    toast({
      title: 'Deconectat',
      description: 'Ai fost deconectat cu succes',
      status: 'success',
      duration: 3000,
      isClosable: true,
    });
    navigate('/');
  };

  // Structura de navigație cu categorii
  const navigationStructure = [
    {
      category: 'Principal',
      items: [
        { label: 'Acasă', path: '/', icon: FiHome, adminOnly: false },
      ]
    },
    {
      category: 'Administrare',
      items: [
        { label: 'Dashboard', path: '/admin', icon: FiBarChart, adminOnly: true },
        { label: 'Utilizatori', path: '/admin/users', icon: FiUsers, adminOnly: true },
        { label: 'Roluri', path: '/admin/roles', icon: FiShield, adminOnly: true },
        ...(user?.role === 'sysadmin' ? [{ label: 'Tenanți', path: '/admin/tenants', icon: FiGrid, adminOnly: true }] : []),
      ]
    },
    {
      category: 'Sisteme',
      items: [
        { label: 'Automatizări', path: '/admin/automations', icon: FiZap, adminOnly: true },
        { label: 'Logs', path: '/admin/logs', icon: FiActivity, adminOnly: true },
      ]
    },
  ];

  // Funcție pentru a filtra elementele de navigație în funcție de rolul utilizatorului
  const getFilteredNavigation = () => {
    return navigationStructure.map(category => ({
      ...category,
      items: category.items.filter(item => !item.adminOnly || user?.role === 'admin' || user?.role === 'sysadmin')
    })).filter(category => category.items.length > 0);
  };

  const filteredNavigation = getFilteredNavigation();

  // Funcții pentru randarea navigației desktop și mobile
  const renderDesktopNavigation = () => (
    <HStack spacing={6} display={{ base: 'none', md: 'flex' }}>
      {filteredNavigation.map((category, categoryIndex) => (
        <HStack key={category.category} spacing={4}>
          {category.items.map((item) => (
            <NavItem key={item.path} item={item} />
          ))}
          {categoryIndex < filteredNavigation.length - 1 && (
            <Divider orientation="vertical" height="20px" />
          )}
        </HStack>
      ))}
    </HStack>
  );

  const renderMobileNavigation = () => (
    <VStack spacing={4} align="stretch" mt={4}>
      {filteredNavigation.map((category) => (
        <Box key={category.category}>
          <Text fontSize="sm" fontWeight="bold" color="gray.500" mb={2} px={2}>
            {category.category}
          </Text>
          <VStack spacing={1} align="stretch">
            {category.items.map((item) => (
              <NavItem key={item.path} item={item} onClick={onClose} />
            ))}
          </VStack>
          <Divider my={3} />
        </Box>
      ))}
    </VStack>
  );

  return (
    <>
      {/* Desktop Navigation */}
      <Box bg={bgColor} borderBottom="1px" borderColor={borderColor} px={6} py={4}>
        <Flex justify="space-between" align="center">
          <Text fontSize="xl" fontWeight="bold" color="brand.600" cursor="pointer" onClick={() => navigate('/')}>
            F1 Atria
          </Text>
          {renderDesktopNavigation()}
          <HStack spacing={4}>
            {user ? (
              <Menu>
                <MenuButton as={Avatar} size="sm" name={user.name} cursor="pointer" />
                <MenuList>
                  <MenuItem icon={<FiSettings />}>Setări</MenuItem>
                  <MenuItem icon={<FiLogOut />} onClick={handleLogout}>Deconectare</MenuItem>
                </MenuList>
              </Menu>
            ) : (
              <Button colorScheme="brand" onClick={() => navigate('/')}>Autentificare</Button>
            )}
            <IconButton 
              display={{ base: 'flex', md: 'none' }} 
              onClick={onOpen} 
              icon={<FiMenu />} 
              aria-label="Deschide meniul" 
              variant="ghost" 
            />
          </HStack>
        </Flex>
      </Box>

      {/* Mobile Navigation Drawer */}
      <Drawer isOpen={isOpen} placement="left" onClose={onClose}>
        <DrawerOverlay />
        <DrawerContent>
          <DrawerCloseButton />
          <DrawerHeader borderBottomWidth="1px">
            <Text fontSize="xl" fontWeight="bold" color="brand.600">F1 Atria</Text>
          </DrawerHeader>
          <DrawerBody>
            {renderMobileNavigation()}
            {user && (
              <VStack spacing={4} mt={6} pt={4} borderTopWidth="1px">
                <HStack spacing={3}>
                  <Avatar size="sm" name={user.name} />
                  <VStack align="start" spacing={0}>
                    <Text fontSize="sm" fontWeight="medium">{user.name}</Text>
                    <Text fontSize="xs" color="gray.500">{user.email}</Text>
                  </VStack>
                </HStack>
                <Button 
                  leftIcon={<FiLogOut />} 
                  variant="ghost" 
                  size="sm" 
                  onClick={handleLogout}
                  w="full"
                >
                  Deconectare
                </Button>
              </VStack>
            )}
          </DrawerBody>
        </DrawerContent>
      </Drawer>
    </>
  );
};

export default Navigation; 