import { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import {
  Box,
  Button,
  FormControl,
  FormLabel,
  Input,
  VStack,
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

  useEffect(() => {
    if (user && ['admin','sysadmin','tenantadmin','cex','tehnic'].includes(user.role)) {
      navigate('/admin');
    }
  }, [user, navigate]);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setIsLoading(true);

    try {
      const response = await axios.post('/login', { email, password });
      localStorage.setItem('token', response.data.access_token);
      toast({ title: 'Autentificare reușită', status: 'success', duration: 3000, isClosable: true });
      window.location.reload();
    } catch (error) {
      toast({ title: 'Eroare la autentificare', description: 'Verifică email-ul și parola', status: 'error', duration: 5000, isClosable: true });
    } finally {
      setIsLoading(false);
    }
  };

  // Dacă e locatar sau user simplu: ecran simplu fără acțiuni admin
  if (user && !['admin','sysadmin','tenantadmin','cex','tehnic'].includes(user.role)) {
    return (
      <Container maxW="7xl" py={8}>
        <VStack spacing={8} align="stretch">
          <Box textAlign="center">
            <Heading size="2xl" color="brand.600" mb={4}>Bun venit, {user.name}!</Heading>
            <Text fontSize="lg" color="gray.600">Panoul F1 Atria</Text>
          </Box>
          <Grid templateColumns={{ base: "1fr", md: "repeat(2, 1fr)", lg: "repeat(4, 1fr)" }} gap={6}>
            <Card><CardBody><Stat><StatLabel>Rol curent</StatLabel><StatNumber>{user.role}</StatNumber></Stat></CardBody></Card>
            <Card><CardBody><Stat><StatLabel>Sesiuni Active</StatLabel><StatNumber>3</StatNumber><StatHelpText><StatArrow type="decrease" />1.2%</StatHelpText></Stat></CardBody></Card>
          </Grid>
        </VStack>
      </Container>
    );
  }

  // Login form pentru vizitatori neautentificați
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
      <Box position="absolute" top="0" left="0" right="0" bottom="0" bg="blackAlpha.600" zIndex="1" />
      <Box position="relative" zIndex="2" w="100%" maxW="100vw" display="flex" justifyContent="center" alignItems="center" px={4}>
        <Card shadow="xl" borderRadius="xl" maxW="md" w="full">
          <CardBody p={8}>
            <VStack spacing={6}>
              <Box textAlign="center">
                <Heading size="lg" color="brand.600" mb={2}>Bun venit!</Heading>
                <Text color="gray.600">Autentifică-te pentru a accesa panoul</Text>
              </Box>

              <Box as="form" w="full" onSubmit={handleSubmit}>
                <VStack spacing={4}>
                  <FormControl isRequired>
                    <FormLabel>Email</FormLabel>
                    <Input type="email" value={email} onChange={(e) => setEmail(e.target.value)} placeholder="Utilizator" size="lg" borderRadius="lg" />
                  </FormControl>

                  <FormControl isRequired>
                    <FormLabel>Parolă</FormLabel>
                    <InputGroup size="lg">
                      <Input type={showPassword ? 'text' : 'password'} value={password} onChange={(e) => setPassword(e.target.value)} placeholder="Parolă" borderRadius="lg" />
                      <InputRightElement>
                        <IconButton aria-label={showPassword ? 'Hide password' : 'Show password'} icon={showPassword ? <ViewOffIcon /> : <ViewIcon />} variant="ghost" onClick={() => setShowPassword(!showPassword)} />
                      </InputRightElement>
                    </InputGroup>
                  </FormControl>

                  <Button type="submit" colorScheme="brand" size="lg" w="full" isLoading={isLoading} loadingText="Se autentifică..." borderRadius="lg">Autentificare</Button>
                </VStack>
              </Box>
            </VStack>
          </CardBody>
        </Card>
      </Box>
    </Box>
  );
}

export default Home;
