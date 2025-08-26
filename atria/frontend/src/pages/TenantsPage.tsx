import { useState, useEffect } from 'react';
import {
  Box, VStack, HStack, Heading, Text, Card, CardBody, Button, useToast, Container, Grid, Stat,
  StatLabel, StatNumber, StatHelpText, StatArrow, Badge, IconButton, Menu, MenuButton,
  MenuList, MenuItem, useDisclosure, Modal, ModalOverlay, ModalContent, ModalHeader,
  ModalBody, ModalCloseButton, FormControl, FormLabel, Input, Select, Textarea,
  NumberInput, NumberInputField, NumberInputStepper, NumberIncrementStepper, NumberDecrementStepper,
  Tabs, TabList, TabPanels, Tab, TabPanel, Alert, AlertIcon, AlertTitle, AlertDescription, FormErrorMessage,
  Table, Thead, Tbody, Tr, Th, Td, TableContainer, Avatar, Divider,
} from '@chakra-ui/react';
import {
  FiPlus, FiEdit, FiTrash2, FiMoreVertical, FiUsers, FiZap, FiHome,
  FiEye, FiBarChart,
} from 'react-icons/fi';
import axios from '../lib/axios';

interface Tenant {
  id: number;
  name: string;
  slug: string;
  domain: string;
  description: string;
  contact_email: string;
  contact_phone: string;
  address: string;
  logo_url: string | null;
  settings: any;
  status: 'active' | 'inactive' | 'suspended';
  subscription_plan: 'basic' | 'premium' | 'enterprise';
  subscription_expires_at: string;
  max_users: number;
  max_automations: number;
  features: string[];
  created_at: string;
  updated_at: string;
  users_count: number;
  automations_count: number;
}

interface TenantFormData {
  name: string;
  slug: string;
  domain: string;
  description: string;
  contact_email: string;
  contact_phone: string;
  address: string;
  logo_url: string;
  status: 'active' | 'inactive' | 'suspended';
  subscription_plan: 'basic' | 'premium' | 'enterprise';
  subscription_expires_at: string;
  max_users: number;
  max_automations: number;
  features: string[];
  settings: any;
}

function TenantsPage() {
  const [tenants, setTenants] = useState<Tenant[]>([]);
  const [selectedTenant, setSelectedTenant] = useState<Tenant | null>(null);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const { isOpen, onOpen, onClose } = useDisclosure();
  const toast = useToast();

  // Form data state
  const [formData, setFormData] = useState<TenantFormData>({
    name: '',
    slug: '',
    domain: '',
    description: '',
    contact_email: '',
    contact_phone: '',
    address: '',
    logo_url: '',
    status: 'active',
    subscription_plan: 'basic',
    subscription_expires_at: '',
    max_users: 10,
    max_automations: 5,
    features: [],
    settings: {
      timezone: 'Europe/Bucharest',
      language: 'ro',
      date_format: 'd.m.Y',
      time_format: 'H:i',
    },
  });

  // Errors state
  const [errors, setErrors] = useState<Record<string, string>>({});

  const fetchTenants = async () => {
    try {
      const response = await axios.get('/tenants');
      setTenants(response.data.tenants);
    } catch (error) {
      toast({
        title: 'Eroare',
        description: 'Nu s-au putut încărca tenant-urile',
        status: 'error',
        duration: 5000,
        isClosable: true,
      });
    }
  };

  useEffect(() => {
    fetchTenants();
  }, []);

  // Reset form function
  const resetForm = () => {
    setFormData({
      name: '',
      slug: '',
      domain: '',
      description: '',
      contact_email: '',
      contact_phone: '',
      address: '',
      logo_url: '',
      status: 'active',
      subscription_plan: 'basic',
      subscription_expires_at: '',
      max_users: 10,
      max_automations: 5,
      features: [],
      settings: {
        timezone: 'Europe/Bucharest',
        language: 'ro',
        date_format: 'd.m.Y',
        time_format: 'H:i',
      },
    });
    setErrors({});
  };

  // Handle opening modal for add/edit
  const handleOpenModal = (tenant?: Tenant) => {
    if (tenant) {
      setSelectedTenant(tenant);
      setFormData({
        name: tenant.name,
        slug: tenant.slug,
        domain: tenant.domain,
        description: tenant.description,
        contact_email: tenant.contact_email,
        contact_phone: tenant.contact_phone,
        address: tenant.address,
        logo_url: tenant.logo_url || '',
        status: tenant.status,
        subscription_plan: tenant.subscription_plan,
        subscription_expires_at: tenant.subscription_expires_at ? tenant.subscription_expires_at.split('T')[0] : '',
        max_users: tenant.max_users,
        max_automations: tenant.max_automations,
        features: tenant.features,
        settings: tenant.settings,
      });
    } else {
      setSelectedTenant(null);
      resetForm();
    }
    onOpen();
  };

  // Form validation logic
  const validateForm = (): boolean => {
    const newErrors: Record<string, string> = {};

    if (!formData.name.trim()) {
      newErrors.name = 'Numele tenant-ului este obligatoriu';
    }

    if (!formData.slug.trim()) {
      newErrors.slug = 'Slug-ul este obligatoriu';
    } else if (!/^[a-z0-9-]+$/.test(formData.slug)) {
      newErrors.slug = 'Slug-ul poate conține doar litere mici, cifre și cratime';
    }

    if (formData.domain && !/^[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(formData.domain)) {
      newErrors.domain = 'Domain-ul nu este valid';
    }

    if (formData.contact_email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formData.contact_email)) {
      newErrors.contact_email = 'Email-ul nu este valid';
    }

    if (formData.max_users < 1 || formData.max_users > 1000) {
      newErrors.max_users = 'Numărul de utilizatori trebuie să fie între 1 și 1000';
    }

    if (formData.max_automations < 1 || formData.max_automations > 100) {
      newErrors.max_automations = 'Numărul de automatizări trebuie să fie între 1 și 100';
    }

    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  // Handle form submission (create/update)
  const handleSubmit = async () => {
    if (!validateForm()) return;

    setIsSubmitting(true);
    try {
      if (selectedTenant) {
        await axios.put(`/tenants/${selectedTenant.id}`, formData);
        toast({
          title: 'Succes',
          description: 'Tenant actualizat cu succes',
          status: 'success',
          duration: 3000,
          isClosable: true,
        });
      } else {
        await axios.post('/tenants', formData);
        toast({
          title: 'Succes',
          description: 'Tenant creat cu succes',
          status: 'success',
          duration: 3000,
          isClosable: true,
        });
      }
      
      onClose();
      fetchTenants();
    } catch (error: any) {
      toast({
        title: 'Eroare',
        description: error.response?.data?.message || 'A apărut o eroare',
        status: 'error',
        duration: 5000,
        isClosable: true,
      });
    } finally {
      setIsSubmitting(false);
    }
  };

  // Handle tenant deletion
  const handleDeleteTenant = async (tenant: Tenant) => {
    if (!confirm(`Ești sigur că vrei să ștergi tenant-ul "${tenant.name}"?`)) {
      return;
    }

    try {
      await axios.delete(`/tenants/${tenant.id}`);
      toast({
        title: 'Succes',
        description: 'Tenant șters cu succes',
        status: 'success',
        duration: 3000,
        isClosable: true,
      });
      fetchTenants();
    } catch (error: any) {
      toast({
        title: 'Eroare',
        description: error.response?.data?.message || 'A apărut o eroare',
        status: 'error',
        duration: 5000,
        isClosable: true,
      });
    }
  };

  // Helper functions
  const getStatusColor = (status: string) => {
    return {
      active: 'green',
      inactive: 'gray',
      suspended: 'red',
    }[status] || 'gray';
  };

  const getPlanColor = (plan: string) => {
    return {
      basic: 'blue',
      premium: 'purple',
      enterprise: 'orange',
    }[plan] || 'gray';
  };

  const getPlanLabel = (plan: string) => {
    return {
      basic: 'Basic',
      premium: 'Premium',
      enterprise: 'Enterprise',
    }[plan] || plan;
  };

  const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('ro-RO');
  };

  return (
    <Container maxW="7xl" py={8}>
      {/* Header */}
      <VStack spacing={6} align="stretch">
        <HStack justify="space-between">
          <Box>
            <Heading size="lg" color="brand.600">Gestionare Tenant-uri</Heading>
            <Text color="gray.600" mt={2}>
              Administrează toate asociațiile de proprietari din sistem
            </Text>
          </Box>
          <Button leftIcon={<FiPlus />} colorScheme="brand" onClick={() => handleOpenModal()}>
            Adaugă Tenant
          </Button>
        </HStack>

        {/* Statistics */}
        <Grid templateColumns={{ base: "1fr", md: "repeat(4, 1fr)" }} gap={6}>
          <Card>
            <CardBody>
              <Stat>
                <StatLabel>Total Tenant-uri</StatLabel>
                <StatNumber>{tenants.length}</StatNumber>
                <StatHelpText>
                  <StatArrow type="increase" />
                  Toate asociațiile
                </StatHelpText>
              </Stat>
            </CardBody>
          </Card>
          <Card>
            <CardBody>
              <Stat>
                <StatLabel>Active</StatLabel>
                <StatNumber>{tenants.filter(t => t.status === 'active').length}</StatNumber>
                <StatHelpText>Tenant-uri active</StatHelpText>
              </Stat>
            </CardBody>
          </Card>
          <Card>
            <CardBody>
              <Stat>
                <StatLabel>Total Utilizatori</StatLabel>
                <StatNumber>{tenants.reduce((sum, t) => sum + t.users_count, 0)}</StatNumber>
                <StatHelpText>În toate tenant-urile</StatHelpText>
              </Stat>
            </CardBody>
          </Card>
          <Card>
            <CardBody>
              <Stat>
                <StatLabel>Total Automatizări</StatLabel>
                <StatNumber>{tenants.reduce((sum, t) => sum + t.automations_count, 0)}</StatNumber>
                <StatHelpText>Configurate</StatHelpText>
              </Stat>
            </CardBody>
          </Card>
        </Grid>

        {/* Tenants Table */}
        <Card>
          <CardBody>
            <TableContainer>
              <Table variant="simple">
                <Thead>
                  <Tr>
                    <Th>Tenant</Th>
                    <Th>Plan</Th>
                    <Th>Status</Th>
                    <Th>Utilizatori</Th>
                    <Th>Automatizări</Th>
                    <Th>Expiră la</Th>
                    <Th>Acțiuni</Th>
                  </Tr>
                </Thead>
                <Tbody>
                  {tenants.map((tenant) => (
                    <Tr key={tenant.id}>
                      <Td>
                        <VStack align="start" spacing={1}>
                          <HStack>
                            <Avatar size="sm" name={tenant.name} icon={<FiHome />} />
                            <Box>
                              <Text fontWeight="bold">{tenant.name}</Text>
                              <Text fontSize="sm" color="gray.500">{tenant.domain}</Text>
                            </Box>
                          </HStack>
                        </VStack>
                      </Td>
                      <Td>
                        <Badge colorScheme={getPlanColor(tenant.subscription_plan)}>
                          {getPlanLabel(tenant.subscription_plan)}
                        </Badge>
                      </Td>
                      <Td>
                        <Badge colorScheme={getStatusColor(tenant.status)}>
                          {tenant.status === 'active' ? 'Activ' : 
                           tenant.status === 'inactive' ? 'Inactiv' : 'Suspendat'}
                        </Badge>
                      </Td>
                      <Td>
                        <HStack>
                          <FiUsers />
                          <Text>{tenant.users_count} / {tenant.max_users}</Text>
                        </HStack>
                      </Td>
                      <Td>
                        <HStack>
                          <FiZap />
                          <Text>{tenant.automations_count} / {tenant.max_automations}</Text>
                        </HStack>
                      </Td>
                      <Td>
                        <Text fontSize="sm">
                          {tenant.subscription_expires_at ? formatDate(tenant.subscription_expires_at) : 'N/A'}
                        </Text>
                      </Td>
                      <Td>
                        <Menu>
                          <MenuButton as={IconButton} icon={<FiMoreVertical />} variant="ghost" size="sm" />
                          <MenuList>
                            <MenuItem icon={<FiEye />} onClick={() => handleOpenModal(tenant)}>
                              Vizualizează
                            </MenuItem>
                            <MenuItem icon={<FiEdit />} onClick={() => handleOpenModal(tenant)}>
                              Editează
                            </MenuItem>
                            <MenuItem icon={<FiBarChart />}>
                              Statistici
                            </MenuItem>
                            <Divider />
                            <MenuItem icon={<FiTrash2 />} color="red.500" onClick={() => handleDeleteTenant(tenant)}>
                              Șterge
                            </MenuItem>
                          </MenuList>
                        </Menu>
                      </Td>
                    </Tr>
                  ))}
                </Tbody>
              </Table>
            </TableContainer>
          </CardBody>
        </Card>
      </VStack>

      {/* Tenant Modal */}
      <Modal isOpen={isOpen} onClose={onClose} size="xl">
        <ModalOverlay />
        <ModalContent>
          <ModalHeader>
            {selectedTenant ? 'Editează Tenant' : 'Adaugă Tenant'}
          </ModalHeader>
          <ModalCloseButton />
          <ModalBody pb={6}>
            <Tabs>
              <TabList>
                <Tab>Informații Generale</Tab>
                <Tab>Contact</Tab>
                <Tab>Configurare</Tab>
              </TabList>
              <TabPanels>
                {/* Informații Generale Tab Panel */}
                <TabPanel>
                  <VStack spacing={4}>
                    <FormControl isInvalid={!!errors.name}>
                      <FormLabel>Nume Tenant</FormLabel>
                      <Input 
                        value={formData.name} 
                        onChange={(e) => setFormData({ ...formData, name: e.target.value })} 
                        placeholder="Ex: F1 Atria - Asociația Principală" 
                      />
                      <FormErrorMessage>{errors.name}</FormErrorMessage>
                    </FormControl>

                    <FormControl isInvalid={!!errors.slug}>
                      <FormLabel>Slug</FormLabel>
                      <Input 
                        value={formData.slug} 
                        onChange={(e) => setFormData({ ...formData, slug: e.target.value })} 
                        placeholder="Ex: f1-atria-main" 
                      />
                      <FormErrorMessage>{errors.slug}</FormErrorMessage>
                    </FormControl>

                    <FormControl isInvalid={!!errors.domain}>
                      <FormLabel>Domain</FormLabel>
                      <Input 
                        value={formData.domain} 
                        onChange={(e) => setFormData({ ...formData, domain: e.target.value })} 
                        placeholder="Ex: f1.atria.live" 
                      />
                      <FormErrorMessage>{errors.domain}</FormErrorMessage>
                    </FormControl>

                    <FormControl>
                      <FormLabel>Descriere</FormLabel>
                      <Textarea 
                        value={formData.description} 
                        onChange={(e) => setFormData({ ...formData, description: e.target.value })} 
                        placeholder="Descrierea tenant-ului..." 
                        rows={3} 
                      />
                    </FormControl>

                    <FormControl>
                      <FormLabel>Plan Abonament</FormLabel>
                      <Select 
                        value={formData.subscription_plan} 
                        onChange={(e) => setFormData({ ...formData, subscription_plan: e.target.value as any })}
                      >
                        <option value="basic">Basic</option>
                        <option value="premium">Premium</option>
                        <option value="enterprise">Enterprise</option>
                      </Select>
                    </FormControl>

                    <FormControl>
                      <FormLabel>Status</FormLabel>
                      <Select 
                        value={formData.status} 
                        onChange={(e) => setFormData({ ...formData, status: e.target.value as any })}
                      >
                        <option value="active">Activ</option>
                        <option value="inactive">Inactiv</option>
                        <option value="suspended">Suspendat</option>
                      </Select>
                    </FormControl>
                  </VStack>
                </TabPanel>

                {/* Contact Tab Panel */}
                <TabPanel>
                  <VStack spacing={4}>
                    <FormControl isInvalid={!!errors.contact_email}>
                      <FormLabel>Email Contact</FormLabel>
                      <Input 
                        value={formData.contact_email} 
                        onChange={(e) => setFormData({ ...formData, contact_email: e.target.value })} 
                        placeholder="admin@tenant.com" 
                      />
                      <FormErrorMessage>{errors.contact_email}</FormErrorMessage>
                    </FormControl>

                    <FormControl>
                      <FormLabel>Telefon Contact</FormLabel>
                      <Input 
                        value={formData.contact_phone} 
                        onChange={(e) => setFormData({ ...formData, contact_phone: e.target.value })} 
                        placeholder="+40 123 456 789" 
                      />
                    </FormControl>

                    <FormControl>
                      <FormLabel>Adresă</FormLabel>
                      <Textarea 
                        value={formData.address} 
                        onChange={(e) => setFormData({ ...formData, address: e.target.value })} 
                        placeholder="Adresa tenant-ului..." 
                        rows={3} 
                      />
                    </FormControl>

                    <FormControl>
                      <FormLabel>Logo URL</FormLabel>
                      <Input 
                        value={formData.logo_url} 
                        onChange={(e) => setFormData({ ...formData, logo_url: e.target.value })} 
                        placeholder="https://example.com/logo.png" 
                      />
                    </FormControl>
                  </VStack>
                </TabPanel>

                {/* Configurare Tab Panel */}
                <TabPanel>
                  <VStack spacing={4}>
                    <FormControl isInvalid={!!errors.max_users}>
                      <FormLabel>Număr Maxim Utilizatori</FormLabel>
                      <NumberInput 
                        value={formData.max_users} 
                        onChange={(_, value) => setFormData({ ...formData, max_users: value })} 
                        min={1} 
                        max={1000}
                      >
                        <NumberInputField />
                        <NumberInputStepper>
                          <NumberIncrementStepper />
                          <NumberDecrementStepper />
                        </NumberInputStepper>
                      </NumberInput>
                      <FormErrorMessage>{errors.max_users}</FormErrorMessage>
                    </FormControl>

                    <FormControl isInvalid={!!errors.max_automations}>
                      <FormLabel>Număr Maxim Automatizări</FormLabel>
                      <NumberInput 
                        value={formData.max_automations} 
                        onChange={(_, value) => setFormData({ ...formData, max_automations: value })} 
                        min={1} 
                        max={100}
                      >
                        <NumberInputField />
                        <NumberInputStepper>
                          <NumberIncrementStepper />
                          <NumberDecrementStepper />
                        </NumberInputStepper>
                      </NumberInput>
                      <FormErrorMessage>{errors.max_automations}</FormErrorMessage>
                    </FormControl>

                    <FormControl>
                      <FormLabel>Data Expirare Abonament</FormLabel>
                      <Input 
                        type="date" 
                        value={formData.subscription_expires_at} 
                        onChange={(e) => setFormData({ ...formData, subscription_expires_at: e.target.value })} 
                      />
                    </FormControl>

                    <Alert status="info">
                      <AlertIcon />
                      <Box>
                        <AlertTitle>Configurare Avansată</AlertTitle>
                        <AlertDescription>
                          Funcționalitățile și setările avansate pot fi configurate după crearea tenant-ului.
                        </AlertDescription>
                      </Box>
                    </Alert>
                  </VStack>
                </TabPanel>
              </TabPanels>
            </Tabs>

            <HStack spacing={4} mt={6} justify="flex-end">
              <Button variant="ghost" onClick={onClose}>
                Anulează
              </Button>
              <Button 
                colorScheme="brand" 
                onClick={handleSubmit} 
                isLoading={isSubmitting} 
                loadingText="Se salvează..."
              >
                {selectedTenant ? 'Actualizează' : 'Creează'}
              </Button>
            </HStack>
          </ModalBody>
        </ModalContent>
      </Modal>
    </Container>
  );
}

export default TenantsPage; 