import { useState, useEffect } from 'react';
import {
  Box,
  VStack,
  HStack,
  Heading,
  Text,
  Card,
  CardBody,
  Button,
  useToast,
  Container,
  Grid,
  Stat,
  StatLabel,
  StatNumber,
  StatHelpText,
  StatArrow,
  Badge,
  IconButton,
  Menu,
  MenuButton,
  MenuList,
  MenuItem,
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
  Select,
  Textarea,
  Switch,
  NumberInput,
  NumberInputField,
  NumberInputStepper,
  NumberIncrementStepper,
  NumberDecrementStepper,
  Tabs,
  TabList,
  TabPanels,
  Tab,
  TabPanel,
  Alert,
  AlertIcon,
  AlertTitle,
  AlertDescription,
  FormErrorMessage,
} from '@chakra-ui/react';
import {
  FiPlus,
  FiEdit,
  FiTrash2,
  FiMoreVertical,
  FiWifi,
  FiLock,
  FiUnlock,
  FiZap,
  FiServer,
} from 'react-icons/fi';
import axios from '../lib/axios';

interface Automation {
  id: number;
  name: string;
  type: 'pool_access' | 'proxy' | 'other';
  mqtt_broker: string;
  mqtt_port: number;
  mqtt_username: string;
  mqtt_password: string;
  mqtt_topic_control: string;
  mqtt_topic_status: string;
  esp_device_id: string;
  lock_relay_pin: number;
  status: boolean;
  config: any;
  description: string;
  created_at: string;
  updated_at: string;
}

interface AutomationStats {
  total_automations: number;
  active_automations: number;
  pool_access_automations: number;
  proxy_automations: number;
  total_access_logs: number;
  today_access_logs: number;
  successful_access: number;
  failed_access: number;
}

interface AutomationFormData {
  name: string;
  type: 'pool_access' | 'proxy' | 'other';
  mqtt_broker: string;
  mqtt_port: number;
  mqtt_username: string;
  mqtt_password: string;
  mqtt_topic_control: string;
  mqtt_topic_status: string;
  esp_device_id: string;
  lock_relay_pin: number;
  status: boolean;
  description: string;
}

function AutomationPage() {
  const [automations, setAutomations] = useState<Automation[]>([]);
  const [stats, setStats] = useState<AutomationStats | null>(null);
  const [selectedAutomation, setSelectedAutomation] = useState<Automation | null>(null);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const { isOpen, onOpen, onClose } = useDisclosure();
  const toast = useToast();

  const [formData, setFormData] = useState<AutomationFormData>({
    name: '',
    type: 'pool_access',
    mqtt_broker: '',
    mqtt_port: 1883,
    mqtt_username: '',
    mqtt_password: '',
    mqtt_topic_control: '',
    mqtt_topic_status: '',
    esp_device_id: '',
    lock_relay_pin: 26,
    status: true,
    description: '',
  });

  const [errors, setErrors] = useState<Record<string, string>>({});

  const fetchAutomations = async () => {
    try {
      const response = await axios.get('/automations');
      setAutomations(response.data.automations);
    } catch (error) {
      toast({
        title: 'Eroare la încărcarea automatizărilor',
        description: 'Nu s-au putut încărca automatizările',
        status: 'error',
        duration: 5000,
        isClosable: true,
      });
    }
  };

  const fetchStats = async () => {
    try {
      const response = await axios.get('/automations/statistics');
      setStats(response.data);
    } catch (error) {
      console.error('Error fetching stats:', error);
    }
  };

  useEffect(() => {
    const loadData = async () => {
      await Promise.all([fetchAutomations(), fetchStats()]);
    };
    loadData();
  }, []);

  const resetForm = () => {
    setFormData({
      name: '',
      type: 'pool_access',
      mqtt_broker: '',
      mqtt_port: 1883,
      mqtt_username: '',
      mqtt_password: '',
      mqtt_topic_control: '',
      mqtt_topic_status: '',
      esp_device_id: '',
      lock_relay_pin: 26,
      status: true,
      description: '',
    });
    setErrors({});
  };

  const handleOpenModal = (automation?: Automation) => {
    if (automation) {
      setSelectedAutomation(automation);
      setFormData({
        name: automation.name,
        type: automation.type,
        mqtt_broker: automation.mqtt_broker || '',
        mqtt_port: automation.mqtt_port || 1883,
        mqtt_username: automation.mqtt_username || '',
        mqtt_password: automation.mqtt_password || '',
        mqtt_topic_control: automation.mqtt_topic_control || '',
        mqtt_topic_status: automation.mqtt_topic_status || '',
        esp_device_id: automation.esp_device_id || '',
        lock_relay_pin: automation.lock_relay_pin || 26,
        status: automation.status,
        description: automation.description || '',
      });
    } else {
      setSelectedAutomation(null);
      resetForm();
    }
    onOpen();
  };

  const validateForm = (): boolean => {
    const newErrors: Record<string, string> = {};

    if (!formData.name.trim()) {
      newErrors.name = 'Numele este obligatoriu';
    }

    if (!formData.mqtt_broker.trim()) {
      newErrors.mqtt_broker = 'Adresa broker-ului MQTT este obligatorie';
    }

    if (formData.mqtt_port < 1 || formData.mqtt_port > 65535) {
      newErrors.mqtt_port = 'Portul trebuie să fie între 1 și 65535';
    }

    if (formData.type === 'pool_access') {
      if (!formData.esp_device_id.trim()) {
        newErrors.esp_device_id = 'ID-ul dispozitivului ESP este obligatoriu';
      }
      if (!formData.mqtt_topic_control.trim()) {
        newErrors.mqtt_topic_control = 'Topic-ul de control este obligatoriu';
      }
      if (!formData.mqtt_topic_status.trim()) {
        newErrors.mqtt_topic_status = 'Topic-ul de status este obligatoriu';
      }
    }

    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handleSubmit = async () => {
    if (!validateForm()) {
      return;
    }

    setIsSubmitting(true);
    try {
      if (selectedAutomation) {
        // Update existing automation
        await axios.put(`/automations/${selectedAutomation.id}`, formData);
        toast({
          title: 'Automatizare actualizată',
          description: 'Automatizarea a fost actualizată cu succes',
          status: 'success',
          duration: 3000,
          isClosable: true,
        });
      } else {
        // Create new automation
        await axios.post('/automations', formData);
        toast({
          title: 'Automatizare creată',
          description: 'Automatizarea a fost creată cu succes',
          status: 'success',
          duration: 3000,
          isClosable: true,
        });
      }
      
      onClose();
      fetchAutomations();
      fetchStats();
    } catch (error) {
      toast({
        title: 'Eroare',
        description: 'A apărut o eroare la salvarea automatizării',
        status: 'error',
        duration: 5000,
        isClosable: true,
      });
    } finally {
      setIsSubmitting(false);
    }
  };

  const handleControlPoolAccess = async (action: 'unlock' | 'lock') => {
    try {
      const response = await axios.post('/automations/pool-access/control', { action });
      toast({
        title: 'Control reușit',
        description: response.data.message,
        status: 'success',
        duration: 3000,
        isClosable: true,
      });
      fetchStats(); // Refresh stats
    } catch (error) {
      toast({
        title: 'Eroare la control',
        description: 'Nu s-a putut executa comanda',
        status: 'error',
        duration: 5000,
        isClosable: true,
      });
    }
  };

  const handleTestMqtt = async (automationId: number) => {
    try {
      const response = await axios.post('/automations/mqtt/test', { automation_id: automationId });
      toast({
        title: 'Test MQTT completat',
        description: response.data.message,
        status: 'success',
        duration: 3000,
        isClosable: true,
      });
    } catch (error) {
      toast({
        title: 'Eroare la test MQTT',
        description: 'Nu s-a putut testa conexiunea MQTT',
        status: 'error',
        duration: 5000,
        isClosable: true,
      });
    }
  };

  const getTypeIcon = (type: string) => {
    switch (type) {
      case 'pool_access':
        return <FiLock />;
      case 'proxy':
        return <FiServer />;
      default:
        return <FiZap />;
    }
  };

  const getTypeColor = (type: string) => {
    switch (type) {
      case 'pool_access':
        return 'blue';
      case 'proxy':
        return 'purple';
      default:
        return 'green';
    }
  };

  const getTypeLabel = (type: string) => {
    switch (type) {
      case 'pool_access':
        return 'Acces Piscină';
      case 'proxy':
        return 'Proxy';
      default:
        return 'Altă';
    }
  };

  return (
    <Container maxW="7xl" py={8}>
      <VStack spacing={8} align="stretch">
        {/* Header */}
        <Box>
          <Heading size="lg" color="brand.600" mb={2}>
            Automatizări
          </Heading>
          <Text color="gray.600">
            Gestionarea sistemelor automate și controlul accesului la piscină
          </Text>
        </Box>

        {/* Stats */}
        {stats && (
          <Grid templateColumns={{ base: "1fr", md: "repeat(4, 1fr)" }} gap={6}>
            <Card>
              <CardBody>
                <Stat>
                  <StatLabel>Total Automatizări</StatLabel>
                  <StatNumber>{stats.total_automations}</StatNumber>
                  <StatHelpText>
                    <StatArrow type="increase" />
                    {stats.active_automations} active
                  </StatHelpText>
                </Stat>
              </CardBody>
            </Card>

            <Card>
              <CardBody>
                <Stat>
                  <StatLabel>Acces Piscină</StatLabel>
                  <StatNumber>{stats.pool_access_automations}</StatNumber>
                  <StatHelpText>
                    <StatArrow type="increase" />
                    Configurate
                  </StatHelpText>
                </Stat>
              </CardBody>
            </Card>

            <Card>
              <CardBody>
                <Stat>
                  <StatLabel>Loguri Acces</StatLabel>
                  <StatNumber>{stats.total_access_logs}</StatNumber>
                  <StatHelpText>
                    <StatArrow type="increase" />
                    {stats.today_access_logs} astăzi
                  </StatHelpText>
                </Stat>
              </CardBody>
            </Card>

            <Card>
              <CardBody>
                <Stat>
                  <StatLabel>Succes Acces</StatLabel>
                  <StatNumber>{stats.successful_access}</StatNumber>
                  <StatHelpText>
                    <StatArrow type="increase" />
                    {((stats.successful_access / stats.total_access_logs) * 100).toFixed(1)}%
                  </StatHelpText>
                </Stat>
              </CardBody>
            </Card>
          </Grid>
        )}

        {/* Pool Access Control */}
        <Card>
          <CardBody>
            <VStack spacing={4} align="stretch">
              <HStack justify="space-between">
                <Box>
                  <Heading size="md" color="brand.600">
                    Control Acces Piscină
                  </Heading>
                  <Text fontSize="sm" color="gray.600">
                    Control direct al zăvorului electromagnetic
                  </Text>
                </Box>
                <HStack spacing={3}>
                  <Button
                    leftIcon={<FiUnlock />}
                    colorScheme="green"
                    onClick={() => handleControlPoolAccess('unlock')}
                  >
                    Deblochează
                  </Button>
                  <Button
                    leftIcon={<FiLock />}
                    colorScheme="red"
                    onClick={() => handleControlPoolAccess('lock')}
                  >
                    Blochează
                  </Button>
                </HStack>
              </HStack>
            </VStack>
          </CardBody>
        </Card>

        {/* Automations List */}
        <Card>
          <CardBody>
            <VStack spacing={4} align="stretch">
              <HStack justify="space-between">
                <Heading size="md" color="brand.600">
                  Automatizări Configurate
                </Heading>
                <Button
                  leftIcon={<FiPlus />}
                  colorScheme="brand"
                  onClick={() => handleOpenModal()}
                >
                  Adaugă Automatizare
                </Button>
              </HStack>

              <Grid templateColumns={{ base: "1fr", md: "repeat(2, 1fr)", lg: "repeat(3, 1fr)" }} gap={6}>
                {automations.map((automation) => (
                  <Card key={automation.id} variant="outline">
                    <CardBody>
                      <VStack spacing={3} align="stretch">
                        <HStack justify="space-between">
                          <HStack spacing={2}>
                            {getTypeIcon(automation.type)}
                            <Badge colorScheme={getTypeColor(automation.type)}>
                              {getTypeLabel(automation.type)}
                            </Badge>
                          </HStack>
                          <Menu>
                            <MenuButton
                              as={IconButton}
                              icon={<FiMoreVertical />}
                              variant="ghost"
                              size="sm"
                            />
                            <MenuList>
                              <MenuItem
                                icon={<FiEdit />}
                                onClick={() => handleOpenModal(automation)}
                              >
                                Editează
                              </MenuItem>
                              <MenuItem
                                icon={<FiWifi />}
                                onClick={() => handleTestMqtt(automation.id)}
                              >
                                Test MQTT
                              </MenuItem>
                              <MenuItem
                                icon={<FiTrash2 />}
                                color="red.500"
                                onClick={() => {
                                  // Handle delete
                                  toast({
                                    title: 'Funcționalitate în dezvoltare',
                                    description: 'Ștergerea automatizărilor va fi disponibilă în curând',
                                    status: 'info',
                                    duration: 3000,
                                    isClosable: true,
                                  });
                                }}
                              >
                                Șterge
                              </MenuItem>
                            </MenuList>
                          </Menu>
                        </HStack>

                        <Box>
                          <Text fontWeight="bold" fontSize="lg">
                            {automation.name}
                          </Text>
                          <Text fontSize="sm" color="gray.600" noOfLines={2}>
                            {automation.description}
                          </Text>
                        </Box>

                        <HStack justify="space-between">
                          <Text fontSize="sm" color="gray.500">
                            Broker: {automation.mqtt_broker}
                          </Text>
                          <Badge colorScheme={automation.status ? 'green' : 'red'}>
                            {automation.status ? 'Activ' : 'Inactiv'}
                          </Badge>
                        </HStack>

                        {automation.type === 'pool_access' && (
                          <HStack spacing={2}>
                            <Button
                              size="sm"
                              leftIcon={<FiUnlock />}
                              colorScheme="green"
                              variant="outline"
                              onClick={() => handleControlPoolAccess('unlock')}
                            >
                              Deblochează
                            </Button>
                            <Button
                              size="sm"
                              leftIcon={<FiLock />}
                              colorScheme="red"
                              variant="outline"
                              onClick={() => handleControlPoolAccess('lock')}
                            >
                              Blochează
                            </Button>
                          </HStack>
                        )}
                      </VStack>
                    </CardBody>
                  </Card>
                ))}
              </Grid>
            </VStack>
          </CardBody>
        </Card>
      </VStack>

      {/* Automation Modal */}
      <Modal isOpen={isOpen} onClose={onClose} size="xl">
        <ModalOverlay />
        <ModalContent>
          <ModalHeader>
            {selectedAutomation ? 'Editează Automatizare' : 'Adaugă Automatizare'}
          </ModalHeader>
          <ModalCloseButton />
          <ModalBody pb={6}>
            <Tabs>
              <TabList>
                <Tab>Informații Generale</Tab>
                <Tab>Configurare MQTT</Tab>
                {formData.type === 'pool_access' && <Tab>Configurare ESP</Tab>}
              </TabList>

              <TabPanels>
                {/* Informații Generale */}
                <TabPanel>
                  <VStack spacing={4}>
                    <FormControl isInvalid={!!errors.name}>
                      <FormLabel>Nume Automatizare</FormLabel>
                      <Input
                        value={formData.name}
                        onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                        placeholder="Ex: Acces Piscină - Zăvor Electromagnetic"
                      />
                      <FormErrorMessage>{errors.name}</FormErrorMessage>
                    </FormControl>

                    <FormControl>
                      <FormLabel>Tip Automatizare</FormLabel>
                      <Select
                        value={formData.type}
                        onChange={(e) => setFormData({ ...formData, type: e.target.value as any })}
                      >
                        <option value="pool_access">Acces Piscină</option>
                        <option value="proxy">Proxy Local</option>
                        <option value="other">Altă</option>
                      </Select>
                    </FormControl>

                    <FormControl>
                      <FormLabel>Descriere</FormLabel>
                      <Textarea
                        value={formData.description}
                        onChange={(e) => setFormData({ ...formData, description: e.target.value })}
                        placeholder="Descrierea automatizării..."
                        rows={3}
                      />
                    </FormControl>

                    <FormControl display="flex" alignItems="center">
                      <FormLabel mb="0">Status Activ</FormLabel>
                      <Switch
                        isChecked={formData.status}
                        onChange={(e) => setFormData({ ...formData, status: e.target.checked })}
                      />
                    </FormControl>
                  </VStack>
                </TabPanel>

                {/* Configurare MQTT */}
                <TabPanel>
                  <VStack spacing={4}>
                    <FormControl isInvalid={!!errors.mqtt_broker}>
                      <FormLabel>Adresă Broker MQTT</FormLabel>
                      <Input
                        value={formData.mqtt_broker}
                        onChange={(e) => setFormData({ ...formData, mqtt_broker: e.target.value })}
                        placeholder="Ex: 192.168.1.100"
                      />
                      <FormErrorMessage>{errors.mqtt_broker}</FormErrorMessage>
                    </FormControl>

                    <FormControl isInvalid={!!errors.mqtt_port}>
                      <FormLabel>Port Broker MQTT</FormLabel>
                                              <NumberInput
                          value={formData.mqtt_port}
                          onChange={(_, value) => setFormData({ ...formData, mqtt_port: value })}
                          min={1}
                          max={65535}
                        >
                        <NumberInputField />
                        <NumberInputStepper>
                          <NumberIncrementStepper />
                          <NumberDecrementStepper />
                        </NumberInputStepper>
                      </NumberInput>
                      <FormErrorMessage>{errors.mqtt_port}</FormErrorMessage>
                    </FormControl>

                    <FormControl>
                      <FormLabel>Username MQTT</FormLabel>
                      <Input
                        value={formData.mqtt_username}
                        onChange={(e) => setFormData({ ...formData, mqtt_username: e.target.value })}
                        placeholder="Username pentru broker MQTT"
                      />
                    </FormControl>

                    <FormControl>
                      <FormLabel>Password MQTT</FormLabel>
                      <Input
                        type="password"
                        value={formData.mqtt_password}
                        onChange={(e) => setFormData({ ...formData, mqtt_password: e.target.value })}
                        placeholder="Password pentru broker MQTT"
                      />
                    </FormControl>

                    <FormControl isInvalid={!!errors.mqtt_topic_control}>
                      <FormLabel>Topic Control</FormLabel>
                      <Input
                        value={formData.mqtt_topic_control}
                        onChange={(e) => setFormData({ ...formData, mqtt_topic_control: e.target.value })}
                        placeholder="Ex: pool/lock/control"
                      />
                      <FormErrorMessage>{errors.mqtt_topic_control}</FormErrorMessage>
                    </FormControl>

                    <FormControl isInvalid={!!errors.mqtt_topic_status}>
                      <FormLabel>Topic Status</FormLabel>
                      <Input
                        value={formData.mqtt_topic_status}
                        onChange={(e) => setFormData({ ...formData, mqtt_topic_status: e.target.value })}
                        placeholder="Ex: pool/lock/status"
                      />
                      <FormErrorMessage>{errors.mqtt_topic_status}</FormErrorMessage>
                    </FormControl>
                  </VStack>
                </TabPanel>

                {/* Configurare ESP (doar pentru pool_access) */}
                {formData.type === 'pool_access' && (
                  <TabPanel>
                    <VStack spacing={4}>
                      <FormControl isInvalid={!!errors.esp_device_id}>
                        <FormLabel>ID Dispozitiv ESP</FormLabel>
                        <Input
                          value={formData.esp_device_id}
                          onChange={(e) => setFormData({ ...formData, esp_device_id: e.target.value })}
                          placeholder="Ex: ESP32_POOL_001"
                        />
                        <FormErrorMessage>{errors.esp_device_id}</FormErrorMessage>
                      </FormControl>

                      <FormControl>
                        <FormLabel>Pin Relay Zăvor</FormLabel>
                        <NumberInput
                          value={formData.lock_relay_pin}
                          onChange={(_, value) => setFormData({ ...formData, lock_relay_pin: value })}
                          min={0}
                          max={40}
                        >
                          <NumberInputField />
                          <NumberInputStepper>
                            <NumberIncrementStepper />
                            <NumberDecrementStepper />
                          </NumberInputStepper>
                        </NumberInput>
                      </FormControl>

                      <Alert status="info">
                        <AlertIcon />
                        <Box>
                          <AlertTitle>Configurare ESP32</AlertTitle>
                          <AlertDescription>
                            Asigură-te că plăcuța ESP32 este configurată să asculte pe topic-urile specificate și să răspundă cu status-ul operațiunilor.
                          </AlertDescription>
                        </Box>
                      </Alert>
                    </VStack>
                  </TabPanel>
                )}
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
                {selectedAutomation ? 'Actualizează' : 'Creează'}
              </Button>
            </HStack>
          </ModalBody>
        </ModalContent>
      </Modal>
    </Container>
  );
}

export default AutomationPage; 