import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import {
  Box,
  Button,
  FormControl,
  FormLabel,
  Input,
  VStack,
  HStack,
  Heading,
  Text,
  useToast,
  Card,
  CardBody,
  InputGroup,
  InputRightElement,
  IconButton,
  Container,
  Grid,
  Stat,
  StatLabel,
  StatNumber,
  StatHelpText,
  StatArrow,
} from '@chakra-ui/react';
import { ViewIcon, ViewOffIcon } from '@chakra-ui/icons';
import { FiUsers, FiShield, FiActivity, FiBarChart } from 'react-icons/fi';
import axios from '../lib/axios';
import atriaImage from '../assets/images/atria-faza1-parc.jpeg';

interface HomeProps {
  user?: any;
}

function Home({ user }: HomeProps) {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [showPassword, setShowPassword] = useState(false);
  const [isLoading, setIsLoading] = useState(false);
  const navigate = useNavigate();
  const toast = useToast();

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setIsLoading(true);

    try {
      const response = await axios.post('/api/login', { email, password });
      
      // Save token to localStorage
      localStorage.setItem('token', response.data.access_token);
      
      toast({
        title: 'Autentificare reușită',
        status: 'success',
        duration: 3000,
        isClosable: true,
      });
      
      // Reload page to update user state
      window.location.reload();
    } catch (error) {
      toast({
        title: 'Eroare la autentificare',
        description: 'Verifică email-ul și parola',
        status: 'error',
        duration: 5000,
        isClosable: true,
      });
    } finally {
      setIsLoading(false);
    }
  };

  // If user is logged in, show dashboard
  if (user) {
    return (
      <Container maxW="7xl" py={8}>
        <VStack spacing={8} align="stretch">
          {/* Welcome Section */}
          <Box textAlign="center">
            <Heading size="2xl" color="brand.600" mb={4}>
              Bun venit, {user.name}!
            </Heading>
            <Text fontSize="lg" color="gray.600">
              Panoul de administrare F1 Atria
            </Text>
          </Box>

          {/* Quick Stats */}
          <Grid templateColumns={{ base: "1fr", md: "repeat(2, 1fr)", lg: "repeat(4, 1fr)" }} gap={6}>
            <Card>
              <CardBody>
                <Stat>
                  <StatLabel>Utilizatori</StatLabel>
                  <StatNumber>24</StatNumber>
                  <StatHelpText>
                    <StatArrow type="increase" />
                    12.5%
                  </StatHelpText>
                </Stat>
              </CardBody>
            </Card>

            <Card>
              <CardBody>
                <Stat>
                  <StatLabel>Roluri</StatLabel>
                  <StatNumber>5</StatNumber>
                  <StatHelpText>
                    <StatArrow type="increase" />
                    2.3%
                  </StatHelpText>
                </Stat>
              </CardBody>
            </Card>

            <Card>
              <CardBody>
                <Stat>
                  <StatLabel>Logs Astăzi</StatLabel>
                  <StatNumber>156</StatNumber>
                  <StatHelpText>
                    <StatArrow type="increase" />
                    8.1%
                  </StatHelpText>
                </Stat>
              </CardBody>
            </Card>

            <Card>
              <CardBody>
                <Stat>
                  <StatLabel>Sesiuni Active</StatLabel>
                  <StatNumber>3</StatNumber>
                  <StatHelpText>
                    <StatArrow type="decrease" />
                    1.2%
                  </StatHelpText>
                </Stat>
              </CardBody>
            </Card>
          </Grid>

          {/* Quick Actions */}
          <Grid templateColumns={{ base: "1fr", md: "repeat(2, 1fr)" }} gap={6}>
            <Card>
              <CardBody>
                <VStack spacing={4} align="stretch">
                  <Heading size="md" color="brand.600">
                    Acțiuni Rapide
                  </Heading>
                  <VStack spacing={3} align="stretch">
                    <Button
                      leftIcon={<FiUsers />}
                      colorScheme="brand"
                      variant="outline"
                      onClick={() => navigate('/admin/users')}
                      justifyContent="start"
                    >
                      Gestionare Utilizatori
                    </Button>
                    <Button
                      leftIcon={<FiShield />}
                      colorScheme="brand"
                      variant="outline"
                      onClick={() => navigate('/admin/roles')}
                      justifyContent="start"
                    >
                      Gestionare Roluri
                    </Button>
                    <Button
                      leftIcon={<FiActivity />}
                      colorScheme="brand"
                      variant="outline"
                      onClick={() => navigate('/admin/logs')}
                      justifyContent="start"
                    >
                      Vizualizare Logs
                    </Button>
                    <Button
                      leftIcon={<FiBarChart />}
                      colorScheme="brand"
                      variant="outline"
                      onClick={() => navigate('/admin')}
                      justifyContent="start"
                    >
                      Dashboard Complet
                    </Button>
                  </VStack>
                </VStack>
              </CardBody>
            </Card>

            <Card>
              <CardBody>
                <VStack spacing={4} align="stretch">
                  <Heading size="md" color="brand.600">
                    Informații Sistem
                  </Heading>
                  <VStack spacing={3} align="stretch">
                    <HStack justify="space-between">
                      <Text fontWeight="medium">Versiune:</Text>
                      <Text>1.0.0</Text>
                    </HStack>
                    <HStack justify="space-between">
                      <Text fontWeight="medium">Ultima actualizare:</Text>
                      <Text>Azi, 14:30</Text>
                    </HStack>
                    <HStack justify="space-between">
                      <Text fontWeight="medium">Status:</Text>
                      <Text color="green.500">Operațional</Text>
                    </HStack>
                    <HStack justify="space-between">
                      <Text fontWeight="medium">Rol curent:</Text>
                      <Text color="brand.600">{user.role === 'admin' ? 'Administrator' : 'Utilizator'}</Text>
                    </HStack>
                  </VStack>
                </VStack>
              </CardBody>
            </Card>
          </Grid>
        </VStack>
      </Container>
    );
  }

  // Login form for non-authenticated users
  return (
    <Box 
      minH="100vh" 
      w="100vw"
      bgImage={`url(${atriaImage})`}
      bgSize="cover"
      bgPosition="center"
      bgRepeat="no-repeat"
      display="flex" 
      alignItems="center" 
      justifyContent="center"
      position="relative"
      overflow="hidden"
    >
      <Box
        position="absolute"
        top="0"
        left="0"
        right="0"
        bottom="0"
        bg="blackAlpha.600"
        zIndex="1"
      />
      <Box 
        position="relative" 
        zIndex="2" 
        w="100%" 
        maxW="100vw"
        display="flex" 
        justifyContent="center" 
        alignItems="center" 
        px={4}
      >
        <Card shadow="xl" borderRadius="xl" maxW="md" w="full">
          <CardBody p={8}>
            <VStack spacing={6}>
              <Box textAlign="center">
                <Heading size="lg" color="brand.600" mb={2}>
                  Bun venit!
                </Heading>
                <Text color="gray.600">
                  Autentifică-te pentru a accesa panoul de administrare
                </Text>
              </Box>

              <Box as="form" w="full" onSubmit={handleSubmit}>
                <VStack spacing={4}>
                  <FormControl isRequired>
                    <FormLabel>Email</FormLabel>
                    <Input
                      type="email"
                      value={email}
                      onChange={(e) => setEmail(e.target.value)}
                      placeholder="Utilizator"
                      size="lg"
                      borderRadius="lg"
                    />
                  </FormControl>

                  <FormControl isRequired>
                    <FormLabel>Parolă</FormLabel>
                    <InputGroup size="lg">
                      <Input
                        type={showPassword ? 'text' : 'password'}
                        value={password}
                        onChange={(e) => setPassword(e.target.value)}
                        placeholder="Parolă"
                        borderRadius="lg"
                      />
                      <InputRightElement>
                        <IconButton
                          aria-label={showPassword ? 'Hide password' : 'Show password'}
                          icon={showPassword ? <ViewOffIcon /> : <ViewIcon />}
                          variant="ghost"
                          onClick={() => setShowPassword(!showPassword)}
                        />
                      </InputRightElement>
                    </InputGroup>
                  </FormControl>

                  <Button
                    type="submit"
                    colorScheme="brand"
                    size="lg"
                    w="full"
                    isLoading={isLoading}
                    loadingText="Se autentifică..."
                    borderRadius="lg"
                  >
                    Autentificare
                  </Button>
                </VStack>
              </Box>

              <Text fontSize="sm" color="gray.500" textAlign="center">
                Folosește credențialele de administrator pentru a accesa sistemul
              </Text>
            </VStack>
          </CardBody>
        </Card>
      </Box>
    </Box>
  );
}

export default Home;
