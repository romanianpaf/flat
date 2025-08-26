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
  Select,
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

interface Tenant {
  id: number;
  name: string;
  slug: string;
  domain: string;
  status: string;
}

interface User {
  id: number;
  name: string;
  email: string;
  email_verified_at: string | null;
  created_at: string;
  updated_at: string;
  roles: Role[];
  tenant_id?: number;
  tenant?: Tenant;
  role: string;
  status: string;
}

function UsersManagement() {
  const [users, setUsers] = useState<User[]>([]);
  const [roles, setRoles] = useState<Role[]>([]);
  const [tenants, setTenants] = useState<Tenant[]>([]);
  const [currentUser, setCurrentUser] = useState<any>(null);
  const [loading, setLoading] = useState(true);
  const [editingUser, setEditingUser] = useState<User | null>(null);
  const [formData, setFormData] = useState<{
    name: string;
    email: string;
    password: string;
    role_ids: string[];
    tenant_id?: number;
    role: string;
    status: string;
  }>({
    name: '',
    email: '',
    password: '',
    role_ids: [],
    tenant_id: undefined,
    role: 'user',
    status: 'active',
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
      const [usersResponse, rolesResponse, tenantsResponse, userResponse] = await Promise.all([
        axios.get('/users'),
        axios.get('/roles'),
        axios.get('/tenants'),
        axios.get('/user'),
      ]);
      
      setUsers(usersResponse.data.users);
      setRoles(rolesResponse.data.roles);
      setTenants(tenantsResponse.data.tenants);
      setCurrentUser(userResponse.data.user);
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
      
      // Setează tenant_id automat pentru utilizatorii non-sysadmin
      if (currentUser?.role === 'sysadmin') {
        // Sysadmin poate selecta tenant-ul
        if (formData.tenant_id) {
          submitData.tenant_id = parseInt(formData.tenant_id.toString());
        } else {
          submitData.tenant_id = null;
        }
      } else {
        // Alți utilizatori (admin, tenantadmin) pot crea doar în tenant-ul lor
        submitData.tenant_id = currentUser?.tenant_id;
      }
      
      if (!submitData.password) {
        delete submitData.password;
      }

      if (editingUser) {
        await axios.put(`/users/${editingUser.id}`, submitData);
        toast({
          title: 'Utilizator actualizat cu succes',
          status: 'success',
          duration: 3000,
          isClosable: true,
        });
      } else {
        await axios.post('/users', submitData);
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
      tenant_id: currentUser?.role === 'sysadmin' ? (user.tenant_id || undefined) : undefined,
      role: user.role || 'user',
      status: user.status || 'active',
    });
    onOpen();
  };

  const handleDelete = async (user: User) => {
    if (!confirm(`Ești sigur că vrei să ștergi utilizatorul "${user.name}"?`)) {
      return;
    }

    try {
      await axios.delete(`/users/${user.id}`);
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
      tenant_id: currentUser?.role === 'sysadmin' ? undefined : undefined,
      role: 'user',
      status: 'active',
    });
    setShowPassword(false);
    onClose();
  };



  if (loading) {
    return (
      <AdminLayout>
        <Box display="flex" justifyContent="center" alignItems="center" minH="400px">
          <Spinner size="xl" />
        </Box>
      </AdminLayout>
    );
  }

  return (
    <AdminLayout>
      <VStack spacing={6} align="stretch">
        <Box>
          <Heading size="lg" color="brand.600">Gestionarea Utilizatorilor</Heading>
          <Text color="gray.600" mt={2}>
            Administrează utilizatorii și accesul lor la sistem
          </Text>
        </Box>

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
              tenant_id: currentUser?.role === 'sysadmin' ? undefined : undefined,
              role: 'user',
              status: 'active',
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
                <Th>Tenant</Th>
                <Th>Rol</Th>
                <Th>Status</Th>
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
                    {user.tenant ? (
                      <Badge colorScheme="blue" variant="subtle">
                        {user.tenant.name}
                      </Badge>
                    ) : (
                      <Badge colorScheme="gray" variant="subtle">
                        Sysadmin
                      </Badge>
                    )}
                  </Td>
                  <Td>
                    <Badge 
                      colorScheme={
                        user.role === 'sysadmin' ? 'red' : 
                        user.role === 'admin' ? 'purple' : 
                        user.role === 'tenantadmin' ? 'orange' : 'blue'
                      }
                      variant="subtle"
                    >
                      {user.role === 'sysadmin' ? 'Sysadmin' :
                       user.role === 'admin' ? 'Admin' :
                       user.role === 'tenantadmin' ? 'Tenant Admin' :
                       user.role === 'cex' ? 'CEx' :
                       user.role === 'tehnic' ? 'Tehnic' :
                       user.role === 'locatar' ? 'Locatar' : user.role}
                    </Badge>
                  </Td>
                  <Td>
                    <Badge 
                      colorScheme={user.status === 'active' ? 'green' : user.status === 'inactive' ? 'gray' : 'red'}
                      variant="subtle"
                    >
                      {user.status === 'active' ? 'Activ' : 
                       user.status === 'inactive' ? 'Inactiv' : 'Suspendat'}
                    </Badge>
                  </Td>
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

                {/* Câmpul de tenant doar pentru sysadmin */}
                {currentUser?.role === 'sysadmin' && (
                  <FormControl isRequired>
                    <FormLabel>Tenant</FormLabel>
                    <Select
                      value={formData.tenant_id || ''}
                      onChange={(e) => setFormData({ ...formData, tenant_id: parseInt(e.target.value) })}
                    >
                      <option value="">Selectați un tenant</option>
                      {tenants.map((tenant) => (
                        <option key={tenant.id} value={tenant.id}>
                          {tenant.name}
                        </option>
                      ))}
                    </Select>
                  </FormControl>
                )}

                <FormControl isRequired>
                  <FormLabel>Rol Principal</FormLabel>
                  <Select
                    value={formData.role}
                    onChange={(e) => setFormData({ ...formData, role: e.target.value })}
                  >
                    <option value="user">Utilizator</option>
                    <option value="locatar">Locatar</option>
                    <option value="tehnic">Tehnic</option>
                    <option value="cex">CEx</option>
                    <option value="tenantadmin">Tenant Admin</option>
                    <option value="admin">Admin</option>
                    <option value="sysadmin">Sysadmin</option>
                  </Select>
                </FormControl>

                <FormControl isRequired>
                  <FormLabel>Status</FormLabel>
                  <Select
                    value={formData.status}
                    onChange={(e) => setFormData({ ...formData, status: e.target.value })}
                  >
                    <option value="active">Activ</option>
                    <option value="inactive">Inactiv</option>
                    <option value="suspended">Suspendat</option>
                  </Select>
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
      </VStack>
    </AdminLayout>
  );
}

export default UsersManagement; 