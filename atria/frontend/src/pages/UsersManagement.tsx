import { useState, useEffect } from 'react';
import {
  Box,
  Button,
  Table,
  Thead,
  Tbody,
  Tr,
  Th,
  Td,
  useDisclosure,
  Modal,
  ModalOverlay,
  ModalContent,
  ModalHeader,
  ModalBody,
  ModalCloseButton,
  FormControl,
  FormLabel,
  Input,
  VStack,
  HStack,
  useToast,
  IconButton,
  Badge,
  Alert,
  AlertIcon,
  Spinner,
  Text,
  Flex,
  Heading,
  Checkbox,
  CheckboxGroup,
  InputGroup,
  InputRightElement,
  Avatar,
} from '@chakra-ui/react';
import { FiEdit, FiTrash2, FiPlus, FiEye, FiEyeOff } from 'react-icons/fi';
import axios from '../lib/axios';
import AdminLayout from '../components/AdminLayout';

interface Role {
  id: number;
  name: string;
  display_name: string;
  description: string | null;
  is_active: boolean;
}

interface User {
  id: number;
  name: string;
  email: string;
  email_verified_at: string | null;
  created_at: string;
  updated_at: string;
  roles: Role[];
}

function UsersManagement() {
  const [users, setUsers] = useState<User[]>([]);
  const [roles, setRoles] = useState<Role[]>([]);
  const [loading, setLoading] = useState(true);
  const [editingUser, setEditingUser] = useState<User | null>(null);
  const [formData, setFormData] = useState<{
    name: string;
    email: string;
    password: string;
    role_ids: string[];
  }>({
    name: '',
    email: '',
    password: '',
    role_ids: [],
  });
  const [submitting, setSubmitting] = useState(false);
  const [showPassword, setShowPassword] = useState(false);
  
  const { isOpen, onOpen, onClose } = useDisclosure();
  const toast = useToast();

  useEffect(() => {
    fetchData();
  }, []);

  const fetchData = async () => {
    try {
      const [usersResponse, rolesResponse] = await Promise.all([
        axios.get('/api/users'),
        axios.get('/api/roles'),
      ]);
      
      setUsers(usersResponse.data.users);
      setRoles(rolesResponse.data.roles);
    } catch (error) {
      toast({
        title: 'Eroare la încărcarea datelor',
        status: 'error',
        duration: 5000,
        isClosable: true,
      });
    } finally {
      setLoading(false);
    }
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setSubmitting(true);

    try {
      const submitData: any = { 
        ...formData,
        role_ids: formData.role_ids.map(id => parseInt(id))
      };
      if (!submitData.password) {
        delete submitData.password;
      }

      if (editingUser) {
        await axios.put(`/api/users/${editingUser.id}`, submitData);
        toast({
          title: 'Utilizator actualizat cu succes',
          status: 'success',
          duration: 3000,
          isClosable: true,
        });
      } else {
        await axios.post('/api/users', submitData);
        toast({
          title: 'Utilizator creat cu succes',
          status: 'success',
          duration: 3000,
          isClosable: true,
        });
      }
      
      fetchData();
      handleClose();
    } catch (error: any) {
      const message = error.response?.data?.message || 'A apărut o eroare';
      toast({
        title: 'Eroare',
        description: message,
        status: 'error',
        duration: 5000,
        isClosable: true,
      });
    } finally {
      setSubmitting(false);
    }
  };

  const handleEdit = (user: User) => {
    setEditingUser(user);
    setFormData({
      name: user.name,
      email: user.email,
      password: '',
      role_ids: user.roles.map(role => role.id.toString()),
    });
    onOpen();
  };

  const handleDelete = async (user: User) => {
    if (!confirm(`Ești sigur că vrei să ștergi utilizatorul "${user.name}"?`)) {
      return;
    }

    try {
      await axios.delete(`/api/users/${user.id}`);
      toast({
        title: 'Utilizator șters cu succes',
        status: 'success',
        duration: 3000,
        isClosable: true,
      });
      fetchData();
    } catch (error: any) {
      const message = error.response?.data?.message || 'A apărut o eroare';
      toast({
        title: 'Eroare la ștergerea utilizatorului',
        description: message,
        status: 'error',
        duration: 5000,
        isClosable: true,
      });
    }
  };

  const handleClose = () => {
    setEditingUser(null);
    setFormData({
      name: '',
      email: '',
      password: '',
      role_ids: [],
    });
    setShowPassword(false);
    onClose();
  };



  if (loading) {
    return (
      <AdminLayout title="Gestionarea Utilizatorilor">
        <Box display="flex" justifyContent="center" alignItems="center" minH="400px">
          <Spinner size="xl" />
        </Box>
      </AdminLayout>
    );
  }

  return (
    <AdminLayout title="Gestionarea Utilizatorilor">
      <Box>
      <Flex justify="space-between" align="center" mb={6}>
        <Heading size="lg">Gestionarea Utilizatorilor</Heading>
        <Button
          leftIcon={<FiPlus />}
          colorScheme="brand"
          onClick={() => {
            setEditingUser(null);
            setFormData({
              name: '',
              email: '',
              password: '',
              role_ids: [],
            });
            onOpen();
          }}
        >
          Adaugă Utilizator
        </Button>
      </Flex>

      {users.length === 0 ? (
        <Alert status="info">
          <AlertIcon />
          Nu există utilizatori în sistem. Creează primul utilizator!
        </Alert>
      ) : (
        <Box bg="white" borderRadius="lg" shadow="sm" overflow="hidden">
          <Table variant="simple">
            <Thead bg="gray.50">
              <Tr>
                <Th>Utilizator</Th>
                <Th>Email</Th>
                <Th>Roluri</Th>
                <Th>Data Creării</Th>
                <Th>Acțiuni</Th>
              </Tr>
            </Thead>
            <Tbody>
              {users.map((user) => (
                <Tr key={user.id}>
                  <Td>
                    <HStack spacing={3}>
                      <Avatar size="sm" name={user.name} />
                      <VStack align="start" spacing={0}>
                        <Text fontWeight="medium">{user.name}</Text>
                        <Text fontSize="sm" color="gray.500">
                          ID: {user.id}
                        </Text>
                      </VStack>
                    </HStack>
                  </Td>
                  <Td>{user.email}</Td>
                  <Td>
                    <HStack spacing={1} wrap="wrap">
                      {user.roles.length > 0 ? (
                        user.roles.map((role) => (
                          <Badge
                            key={role.id}
                            colorScheme={role.is_active ? 'blue' : 'gray'}
                            variant="subtle"
                            fontSize="xs"
                          >
                            {role.display_name}
                          </Badge>
                        ))
                      ) : (
                        <Text fontSize="sm" color="gray.500">
                          Fără roluri
                        </Text>
                      )}
                    </HStack>
                  </Td>
                  <Td>
                    <Text fontSize="sm">
                      {new Date(user.created_at).toLocaleDateString('ro-RO')}
                    </Text>
                  </Td>
                  <Td>
                    <HStack spacing={2}>
                      <IconButton
                        aria-label="Editează utilizator"
                        icon={<FiEdit />}
                        size="sm"
                        variant="ghost"
                        onClick={() => handleEdit(user)}
                      />
                      <IconButton
                        aria-label="Șterge utilizator"
                        icon={<FiTrash2 />}
                        size="sm"
                        variant="ghost"
                        colorScheme="red"
                        onClick={() => handleDelete(user)}
                      />
                    </HStack>
                  </Td>
                </Tr>
              ))}
            </Tbody>
          </Table>
        </Box>
      )}

      <Modal isOpen={isOpen} onClose={handleClose} size="lg">
        <ModalOverlay />
        <ModalContent>
          <ModalHeader>
            {editingUser ? 'Editează Utilizator' : 'Adaugă Utilizator Nou'}
          </ModalHeader>
          <ModalCloseButton />
          <ModalBody pb={6}>
            <Box as="form" onSubmit={handleSubmit}>
              <VStack spacing={4}>
                <FormControl isRequired>
                  <FormLabel>Nume Complet</FormLabel>
                  <Input
                    value={formData.name}
                    onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                    placeholder="Nume Prenume"
                  />
                </FormControl>

                <FormControl isRequired>
                  <FormLabel>Email</FormLabel>
                  <Input
                    type="email"
                    value={formData.email}
                    onChange={(e) => setFormData({ ...formData, email: e.target.value })}
                    placeholder="utilizator@exemplu.com"
                  />
                </FormControl>

                <FormControl isRequired={!editingUser}>
                  <FormLabel>
                    {editingUser ? 'Parolă Nouă (opțional)' : 'Parolă'}
                  </FormLabel>
                  <InputGroup>
                    <Input
                      type={showPassword ? 'text' : 'password'}
                      value={formData.password}
                      onChange={(e) => setFormData({ ...formData, password: e.target.value })}
                      placeholder={editingUser ? 'Lăsați gol pentru a păstra parola actuală' : 'Parolă'}
                    />
                    <InputRightElement>
                      <IconButton
                        aria-label={showPassword ? 'Hide password' : 'Show password'}
                        icon={showPassword ? <FiEyeOff /> : <FiEye />}
                        variant="ghost"
                        size="sm"
                        onClick={() => setShowPassword(!showPassword)}
                      />
                    </InputRightElement>
                  </InputGroup>
                </FormControl>

                <FormControl>
                  <FormLabel>Roluri</FormLabel>
                  <CheckboxGroup
                    value={formData.role_ids}
                    onChange={(values) => setFormData({ ...formData, role_ids: values as string[] })}
                  >
                    <VStack align="start" spacing={2}>
                      {roles.map((role) => (
                        <Checkbox key={role.id} value={role.id.toString()}>
                          <VStack align="start" spacing={0}>
                            <Text fontWeight="medium">{role.display_name}</Text>
                            <Text fontSize="sm" color="gray.600">
                              {role.description}
                            </Text>
                          </VStack>
                        </Checkbox>
                      ))}
                    </VStack>
                  </CheckboxGroup>
                </FormControl>

                <HStack spacing={4} w="full" pt={4}>
                  <Button
                    type="submit"
                    colorScheme="brand"
                    isLoading={submitting}
                    loadingText="Se salvează..."
                    flex={1}
                  >
                    {editingUser ? 'Actualizează' : 'Creează'}
                  </Button>
                  <Button onClick={handleClose} flex={1}>
                    Anulează
                  </Button>
                </HStack>
              </VStack>
            </Box>
          </ModalBody>
        </ModalContent>
      </Modal>
      </Box>
    </AdminLayout>
  );
}

export default UsersManagement; 