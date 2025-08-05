import { useState } from 'react';
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
} from '@chakra-ui/react';
import { ViewIcon, ViewOffIcon } from '@chakra-ui/icons';
import axios from '../lib/axios';

function Home() {
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
      await axios.post('/api/login', { email, password });
      toast({
        title: 'Autentificare reușită',
        status: 'success',
        duration: 3000,
        isClosable: true,
      });
      navigate('/admin');
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

  return (
    <Box 
      minH="100vh" 
      w="100vw"
      bgImage="url('/assets/images/atria-faza1-parc.jpeg')"
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
      <Box position="relative" zIndex="2" w="100%" display="flex" justifyContent="center" alignItems="center">
        <Card shadow="xl" borderRadius="xl" maxW="md" w="full" mx={4}>
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
