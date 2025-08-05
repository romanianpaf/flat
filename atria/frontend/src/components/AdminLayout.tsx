import { useState, useEffect } from 'react';
import { useNavigate, useLocation } from 'react-router-dom';
import {
  Box,
  Flex,
  VStack,
  HStack,
  Text,
  Heading,
  Button,
  useToast,
  Container,
  Card,
  CardBody,
  Avatar,
  Menu,
  MenuButton,
  MenuList,
  MenuItem,
  useColorModeValue,

} from '@chakra-ui/react';
import {
  FiUsers,
  FiSettings,
  FiBarChart,
  FiShield,
  FiLogOut,
  FiChevronDown,
  FiHome,
} from 'react-icons/fi';
import axios from '../lib/axios';

interface AdminLayoutProps {
  children: React.ReactNode;
  title: string;
}

function AdminLayout({ children, title }: AdminLayoutProps) {
  const [user, setUser] = useState<any>(null);
  const [isLoading, setIsLoading] = useState(true);
  const navigate = useNavigate();
  const location = useLocation();
  const toast = useToast();
  const bgColor = useColorModeValue('white', 'gray.800');
  const borderColor = useColorModeValue('gray.200', 'gray.700');

  useEffect(() => {
    const fetchUserInfo = async () => {
      try {
        const response = await axios.get('/api/admin/info');
        setUser(response.data.user);
      } catch (error) {
        toast({
          title: 'Eroare la încărcarea datelor',
          description: 'Te rugăm să te autentifici din nou',
          status: 'error',
          duration: 5000,
          isClosable: true,
        });
        navigate('/');
      } finally {
        setIsLoading(false);
      }
    };

    fetchUserInfo();
  }, [navigate, toast]);

  const handleLogout = async () => {
    try {
      await axios.post('/api/logout');
      localStorage.removeItem('access_token');
      toast({
        title: 'Deconectare reușită',
        status: 'success',
        duration: 3000,
        isClosable: true,
      });
      navigate('/');
    } catch (error) {
      localStorage.removeItem('access_token');
      toast({
        title: 'Deconectare reușită',
        status: 'success',
        duration: 3000,
        isClosable: true,
      });
      navigate('/');
    }
  };

  const isActiveRoute = (path: string) => {
    return location.pathname === path;
  };

  if (isLoading) {
    return (
      <Box minH="100vh" bg="gray.50" display="flex" alignItems="center" justifyContent="center">
        <Text>Se încarcă...</Text>
      </Box>
    );
  }

  return (
    <Box minH="100vh" bg="gray.50">
      {/* Header */}
      <Box bg={bgColor} borderBottom="1px" borderColor={borderColor} px={6} py={4}>
        <Flex justify="space-between" align="center">
          <Heading size="lg" color="brand.600">
            {title}
          </Heading>
          
          <HStack spacing={4}>
            <Avatar size="sm" name={user?.name} />
            <VStack spacing={0} align="start">
              <Text fontWeight="medium" fontSize="sm">
                {user?.name}
              </Text>
              <Text fontSize="xs" color="gray.500">
                Administrator
              </Text>
            </VStack>
            
            <Menu>
              <MenuButton as={Button} variant="ghost" size="sm">
                <FiChevronDown />
              </MenuButton>
              <MenuList>
                <MenuItem icon={<FiSettings />}>Setări</MenuItem>
                <MenuItem icon={<FiLogOut />} onClick={handleLogout}>
                  Deconectare
                </MenuItem>
              </MenuList>
            </Menu>
          </HStack>
        </Flex>
      </Box>

      {/* Main Content */}
      <Container maxW="7xl" py={8}>
        <Flex gap={6}>
          {/* Sidebar */}
          <Box w="280px" flexShrink={0}>
            <VStack spacing={4} align="stretch">
              <Card>
                <CardBody>
                  <VStack spacing={2}>
                    <Button
                      leftIcon={<FiHome />}
                      variant={isActiveRoute('/admin') ? 'solid' : 'ghost'}
                      colorScheme={isActiveRoute('/admin') ? 'brand' : 'gray'}
                      w="full"
                      justifyContent="start"
                      onClick={() => navigate('/admin')}
                    >
                      Dashboard
                    </Button>
                    <Button
                      leftIcon={<FiUsers />}
                      variant={isActiveRoute('/admin/users') ? 'solid' : 'ghost'}
                      colorScheme={isActiveRoute('/admin/users') ? 'brand' : 'gray'}
                      w="full"
                      justifyContent="start"
                      onClick={() => navigate('/admin/users')}
                    >
                      Utilizatori
                    </Button>
                    <Button
                      leftIcon={<FiShield />}
                      variant={isActiveRoute('/admin/roles') ? 'solid' : 'ghost'}
                      colorScheme={isActiveRoute('/admin/roles') ? 'brand' : 'gray'}
                      w="full"
                      justifyContent="start"
                      onClick={() => navigate('/admin/roles')}
                    >
                      Roluri
                    </Button>
                    <Button
                      leftIcon={<FiBarChart />}
                      variant="ghost"
                      w="full"
                      justifyContent="start"
                      isDisabled
                    >
                      Statistici
                    </Button>
                  </VStack>
                </CardBody>
              </Card>
            </VStack>
          </Box>

          {/* Main Content Area */}
          <Box flex={1}>
            {children}
          </Box>
        </Flex>
      </Container>
    </Box>
  );
}

export default AdminLayout; 