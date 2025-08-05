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
  Textarea,
  Switch,
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
} from '@chakra-ui/react';
import { FiEdit, FiTrash2, FiPlus } from 'react-icons/fi';
import axios from '../lib/axios';
import AdminLayout from '../components/AdminLayout';

interface Role {
  id: number;
  name: string;
  display_name: string;
  description: string | null;
  is_active: boolean;
  created_at: string;
  updated_at: string;
}

function RolesManagement() {
  const [roles, setRoles] = useState<Role[]>([]);
  const [loading, setLoading] = useState(true);
  const [editingRole, setEditingRole] = useState<Role | null>(null);
  const [formData, setFormData] = useState({
    name: '',
    display_name: '',
    description: '',
    is_active: true,
  });
  const [submitting, setSubmitting] = useState(false);
  
  const { isOpen, onOpen, onClose } = useDisclosure();
  const toast = useToast();

  useEffect(() => {
    fetchRoles();
  }, []);

  const fetchRoles = async () => {
    try {
      const response = await axios.get('/api/roles');
      setRoles(response.data.roles);
    } catch (error) {
      toast({
        title: 'Eroare la încărcarea rolurilor',
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
      if (editingRole) {
        await axios.put(`/api/roles/${editingRole.id}`, formData);
        toast({
          title: 'Rol actualizat cu succes',
          status: 'success',
          duration: 3000,
          isClosable: true,
        });
      } else {
        await axios.post('/api/roles', formData);
        toast({
          title: 'Rol creat cu succes',
          status: 'success',
          duration: 3000,
          isClosable: true,
        });
      }
      
      fetchRoles();
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

  const handleEdit = (role: Role) => {
    setEditingRole(role);
    setFormData({
      name: role.name,
      display_name: role.display_name,
      description: role.description || '',
      is_active: role.is_active,
    });
    onOpen();
  };

  const handleDelete = async (role: Role) => {
    if (!confirm(`Ești sigur că vrei să ștergi rolul "${role.display_name}"?`)) {
      return;
    }

    try {
      await axios.delete(`/api/roles/${role.id}`);
      toast({
        title: 'Rol șters cu succes',
        status: 'success',
        duration: 3000,
        isClosable: true,
      });
      fetchRoles();
    } catch (error: any) {
      const message = error.response?.data?.message || 'A apărut o eroare';
      toast({
        title: 'Eroare la ștergerea rolului',
        description: message,
        status: 'error',
        duration: 5000,
        isClosable: true,
      });
    }
  };

  const handleClose = () => {
    setEditingRole(null);
    setFormData({
      name: '',
      display_name: '',
      description: '',
      is_active: true,
    });
    onClose();
  };

  if (loading) {
    return (
      <AdminLayout title="Gestionarea Rolurilor">
        <Box display="flex" justifyContent="center" alignItems="center" minH="400px">
          <Spinner size="xl" />
        </Box>
      </AdminLayout>
    );
  }

  return (
    <AdminLayout title="Gestionarea Rolurilor">
      <Box>
      <Flex justify="space-between" align="center" mb={6}>
        <Heading size="lg">Gestionarea Rolurilor</Heading>
        <Button
          leftIcon={<FiPlus />}
          colorScheme="brand"
          onClick={() => {
            setEditingRole(null);
            setFormData({
              name: '',
              display_name: '',
              description: '',
              is_active: true,
            });
            onOpen();
          }}
        >
          Adaugă Rol
        </Button>
      </Flex>

      {roles.length === 0 ? (
        <Alert status="info">
          <AlertIcon />
          Nu există roluri în sistem. Creează primul rol!
        </Alert>
      ) : (
        <Box bg="white" borderRadius="lg" shadow="sm" overflow="hidden">
          <Table variant="simple">
            <Thead bg="gray.50">
              <Tr>
                <Th>Nume</Th>
                <Th>Nume Afișat</Th>
                <Th>Descriere</Th>
                <Th>Status</Th>
                <Th>Acțiuni</Th>
              </Tr>
            </Thead>
            <Tbody>
              {roles.map((role) => (
                <Tr key={role.id}>
                  <Td>
                    <Text fontWeight="medium">{role.name}</Text>
                  </Td>
                  <Td>{role.display_name}</Td>
                  <Td>
                    <Text noOfLines={2} color="gray.600">
                      {role.description || '-'}
                    </Text>
                  </Td>
                  <Td>
                    <Badge
                      colorScheme={role.is_active ? 'green' : 'red'}
                      variant="subtle"
                    >
                      {role.is_active ? 'Activ' : 'Inactiv'}
                    </Badge>
                  </Td>
                  <Td>
                    <HStack spacing={2}>
                      <IconButton
                        aria-label="Editează rol"
                        icon={<FiEdit />}
                        size="sm"
                        variant="ghost"
                        onClick={() => handleEdit(role)}
                      />
                      <IconButton
                        aria-label="Șterge rol"
                        icon={<FiTrash2 />}
                        size="sm"
                        variant="ghost"
                        colorScheme="red"
                        onClick={() => handleDelete(role)}
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
            {editingRole ? 'Editează Rol' : 'Adaugă Rol Nou'}
          </ModalHeader>
          <ModalCloseButton />
          <ModalBody pb={6}>
            <Box as="form" onSubmit={handleSubmit}>
              <VStack spacing={4}>
                <FormControl isRequired>
                  <FormLabel>Nume (cod)</FormLabel>
                  <Input
                    value={formData.name}
                    onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                    placeholder="admin"
                  />
                </FormControl>

                <FormControl isRequired>
                  <FormLabel>Nume Afișat</FormLabel>
                  <Input
                    value={formData.display_name}
                    onChange={(e) => setFormData({ ...formData, display_name: e.target.value })}
                    placeholder="Administrator"
                  />
                </FormControl>

                <FormControl>
                  <FormLabel>Descriere</FormLabel>
                  <Textarea
                    value={formData.description}
                    onChange={(e) => setFormData({ ...formData, description: e.target.value })}
                    placeholder="Descrierea rolului..."
                    rows={3}
                  />
                </FormControl>

                <FormControl display="flex" alignItems="center">
                  <FormLabel mb="0">Rol Activ</FormLabel>
                  <Switch
                    isChecked={formData.is_active}
                    onChange={(e) => setFormData({ ...formData, is_active: e.target.checked })}
                  />
                </FormControl>

                <HStack spacing={4} w="full" pt={4}>
                  <Button
                    type="submit"
                    colorScheme="brand"
                    isLoading={submitting}
                    loadingText="Se salvează..."
                    flex={1}
                  >
                    {editingRole ? 'Actualizează' : 'Creează'}
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

export default RolesManagement; 