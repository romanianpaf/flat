import { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
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
  Stat,
  StatLabel,
  StatNumber,
  StatHelpText,
  Icon,
  Grid,
  GridItem,
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
  FiTrendingUp,
  FiLogOut,
  FiChevronDown,
} from 'react-icons/fi';
import axios from '../lib/axios';

function AdminDashboard() {
  const [user, setUser] = useState<any>(null);
  const [isLoading, setIsLoading] = useState(true);
  const navigate = useNavigate();
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
      toast({
        title: 'Deconectare reușită',
        status: 'success',
        duration: 3000,
        isClosable: true,
      });
      navigate('/');
    } catch (error) {
      toast({
        title: 'Eroare la deconectare',
        status: 'error',
        duration: 3000,
        isClosable: true,
      });
    }
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
            Panou de Administrare
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
        <Grid templateColumns="repeat(12, 1fr)" gap={6}>
          {/* Sidebar */}
          <GridItem colSpan={{ base: 12, lg: 3 }}>
            <VStack spacing={4} align="stretch">
              <Card>
                <CardBody>
                  <VStack spacing={4}>
                    <Button
                      leftIcon={<FiUsers />}
                      variant="ghost"
                      w="full"
                      justifyContent="start"
                      colorScheme="brand"
                    >
                      Utilizatori
                    </Button>
                    <Button
                                             leftIcon={<FiBarChart />}
                      variant="ghost"
                      w="full"
                      justifyContent="start"
                    >
                      Statistici
                    </Button>
                    <Button
                      leftIcon={<FiSettings />}
                      variant="ghost"
                      w="full"
                      justifyContent="start"
                    >
                      Configurări
                    </Button>
                  </VStack>
                </CardBody>
              </Card>
            </VStack>
          </GridItem>

          {/* Main Dashboard */}
          <GridItem colSpan={{ base: 12, lg: 9 }}>
            <VStack spacing={6} align="stretch">
              {/* Stats Cards */}
              <Grid templateColumns={{ base: '1fr', md: 'repeat(3, 1fr)' }} gap={6}>
                <Card>
                  <CardBody>
                    <Stat>
                      <StatLabel>Utilizatori Activi</StatLabel>
                      <StatNumber>1,234</StatNumber>
                      <StatHelpText>
                        <Icon as={FiTrendingUp} color="green.500" mr={1} />
                        +12% față de luna trecută
                      </StatHelpText>
                    </Stat>
                  </CardBody>
                </Card>

                <Card>
                  <CardBody>
                    <Stat>
                      <StatLabel>Cereri Noi</StatLabel>
                      <StatNumber>89</StatNumber>
                      <StatHelpText>
                        <Icon as={FiTrendingUp} color="green.500" mr={1} />
                        +5% față de ieri
                      </StatHelpText>
                    </Stat>
                  </CardBody>
                </Card>

                <Card>
                  <CardBody>
                    <Stat>
                      <StatLabel>Rata de Succes</StatLabel>
                      <StatNumber>98.5%</StatNumber>
                      <StatHelpText>
                        <Icon as={FiTrendingUp} color="green.500" mr={1} />
                        +2.3% față de săptămâna trecută
                      </StatHelpText>
                    </Stat>
                  </CardBody>
                </Card>
              </Grid>

              {/* Main Content Card */}
              <Card>
                <CardBody>
                  <VStack spacing={4} align="stretch">
                    <Heading size="md" color="gray.700">
                      Activități Recente
                    </Heading>
                    <Text color="gray.600">
                      Aici vei vedea activitățile recente și statisticile sistemului.
                      Panoul este în curs de dezvoltare și va fi completat cu funcționalități suplimentare.
                    </Text>
                    
                    <Box p={4} bg="gray.50" borderRadius="lg">
                      <Text fontSize="sm" color="gray.500">
                        Sistemul este funcțional și gata pentru dezvoltare. 
                        Poți adăuga aici componente suplimentare pentru gestionarea utilizatorilor, 
                        statistici detaliate, și alte funcționalități administrative.
                      </Text>
                    </Box>
                  </VStack>
                </CardBody>
              </Card>
            </VStack>
          </GridItem>
        </Grid>
      </Container>
    </Box>
  );
}

export default AdminDashboard;
