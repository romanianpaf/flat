import { useState, useEffect } from 'react';
import {
  Box,
  VStack,
  HStack,
  Heading,
  Text,
  Card,
  CardBody,
  Table,
  Thead,
  Tbody,
  Tr,
  Th,
  Td,
  Badge,
  Button,
  Input,
  Select,
  useToast,
  Container,
  Spinner,
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
  Textarea,
  Grid,
  Stat,
  StatLabel,
  StatNumber,
  StatHelpText,
  StatArrow,
  InputGroup,
  InputLeftElement,
} from '@chakra-ui/react';
import {
  FiSearch,
  FiFilter,
  FiDownload,
  FiRefreshCw,
  FiEye,
  FiMoreVertical,
  FiTrash2,
  FiActivity,
  FiAlertCircle,
  FiInfo,
  FiCheckCircle,
} from 'react-icons/fi';
import axios from '../lib/axios';

interface Log {
  id: number;
  user_id: number;
  user_name: string;
  action: string;
  description: string;
  ip_address: string;
  user_agent: string;
  level: 'info' | 'warning' | 'error' | 'success';
  created_at: string;
  metadata?: any;
}

function LogsPage() {
  const [logs, setLogs] = useState<Log[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [searchTerm, setSearchTerm] = useState('');
  const [levelFilter, setLevelFilter] = useState('');
  const [userFilter, setUserFilter] = useState('');
  const [currentPage, setCurrentPage] = useState(1);
  const [totalPages, setTotalPages] = useState(1);
  const [selectedLog, setSelectedLog] = useState<Log | null>(null);
  const { isOpen, onOpen, onClose } = useDisclosure();
  const toast = useToast();

  const fetchLogs = async () => {
    setIsLoading(true);
    try {
      const params = new URLSearchParams({
        page: currentPage.toString(),
        search: searchTerm,
        level: levelFilter,
        user: userFilter,
      });

      const response = await axios.get(`/api/admin/logs?${params}`);
      setLogs(response.data.logs);
      setTotalPages(response.data.total_pages);
    } catch (error) {
      toast({
        title: 'Eroare la încărcarea logurilor',
        description: 'Nu s-au putut încărca logurile',
        status: 'error',
        duration: 5000,
        isClosable: true,
      });
    } finally {
      setIsLoading(false);
    }
  };

  useEffect(() => {
    fetchLogs();
  }, [currentPage, searchTerm, levelFilter, userFilter]);

  const handleSearch = () => {
    setCurrentPage(1);
    fetchLogs();
  };

  const handleClearFilters = () => {
    setSearchTerm('');
    setLevelFilter('');
    setUserFilter('');
    setCurrentPage(1);
  };

  const handleExportLogs = async () => {
    try {
      const response = await axios.get('/api/admin/logs/export', {
        responseType: 'blob',
      });
      
      const url = window.URL.createObjectURL(new Blob([response.data]));
      const link = document.createElement('a');
      link.href = url;
      link.setAttribute('download', `logs-${new Date().toISOString().split('T')[0]}.csv`);
      document.body.appendChild(link);
      link.click();
      link.remove();
      
      toast({
        title: 'Export reușit',
        description: 'Logurile au fost exportate cu succes',
        status: 'success',
        duration: 3000,
        isClosable: true,
      });
    } catch (error) {
      toast({
        title: 'Eroare la export',
        description: 'Nu s-au putut exporta logurile',
        status: 'error',
        duration: 5000,
        isClosable: true,
      });
    }
  };

  const getLevelIcon = (level: string) => {
    switch (level) {
      case 'info':
        return <FiInfo color="blue" />;
      case 'warning':
        return <FiAlertCircle color="orange" />;
      case 'error':
        return <FiAlertCircle color="red" />;
      case 'success':
        return <FiCheckCircle color="green" />;
      default:
        return <FiActivity />;
    }
  };

  const getLevelColor = (level: string) => {
    switch (level) {
      case 'info':
        return 'blue';
      case 'warning':
        return 'orange';
      case 'error':
        return 'red';
      case 'success':
        return 'green';
      default:
        return 'gray';
    }
  };

  const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleString('ro-RO');
  };

  return (
    <Container maxW="7xl" py={8}>
      <VStack spacing={8} align="stretch">
        {/* Header */}
        <Box>
          <Heading size="lg" color="brand.600" mb={2}>
            Logs Sistem
          </Heading>
          <Text color="gray.600">
            Monitorizează toate acțiunile și evenimentele din sistem
          </Text>
        </Box>

        {/* Stats */}
        <Grid templateColumns={{ base: "1fr", md: "repeat(4, 1fr)" }} gap={6}>
          <Card>
            <CardBody>
              <Stat>
                <StatLabel>Total Logs</StatLabel>
                <StatNumber>1,234</StatNumber>
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
                <StatLabel>Astăzi</StatLabel>
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
                <StatLabel>Erori</StatLabel>
                <StatNumber>23</StatNumber>
                <StatHelpText>
                  <StatArrow type="decrease" />
                  2.3%
                </StatHelpText>
              </Stat>
            </CardBody>
          </Card>

          <Card>
            <CardBody>
              <Stat>
                <StatLabel>Utilizatori Activi</StatLabel>
                <StatNumber>8</StatNumber>
                <StatHelpText>
                  <StatArrow type="increase" />
                  1.2%
                </StatHelpText>
              </Stat>
            </CardBody>
          </Card>
        </Grid>

        {/* Filters */}
        <Card>
          <CardBody>
            <VStack spacing={4} align="stretch">
              <Heading size="md" color="brand.600">
                Filtre și Căutare
              </Heading>
              
              <Grid templateColumns={{ base: "1fr", md: "repeat(3, 1fr)" }} gap={4}>
                <InputGroup>
                  <InputLeftElement
                    pointerEvents="none"
                    children={<FiSearch color="gray.300" />}
                  />
                  <Input
                    placeholder="Caută în logs..."
                    value={searchTerm}
                    onChange={(e) => setSearchTerm(e.target.value)}
                  />
                </InputGroup>
                
                <Select
                  placeholder="Filtrează după nivel"
                  value={levelFilter}
                  onChange={(e) => setLevelFilter(e.target.value)}
                >
                  <option value="info">Info</option>
                  <option value="warning">Warning</option>
                  <option value="error">Error</option>
                  <option value="success">Success</option>
                </Select>
                
                <Input
                  placeholder="Filtrează după utilizator"
                  value={userFilter}
                  onChange={(e) => setUserFilter(e.target.value)}
                />
              </Grid>
              
              <HStack spacing={4}>
                <Button
                  leftIcon={<FiSearch />}
                  colorScheme="brand"
                  onClick={handleSearch}
                >
                  Caută
                </Button>
                <Button
                  leftIcon={<FiFilter />}
                  variant="outline"
                  onClick={handleClearFilters}
                >
                  Curăță Filtrele
                </Button>
                <Button
                  leftIcon={<FiDownload />}
                  variant="outline"
                  onClick={handleExportLogs}
                >
                  Export CSV
                </Button>
                <Button
                  leftIcon={<FiRefreshCw />}
                  variant="ghost"
                  onClick={fetchLogs}
                  isLoading={isLoading}
                >
                  Reîmprospătează
                </Button>
              </HStack>
            </VStack>
          </CardBody>
        </Card>

        {/* Logs Table */}
        <Card>
          <CardBody>
            {isLoading ? (
              <Box textAlign="center" py={8}>
                <Spinner size="xl" color="brand.500" />
                <Text mt={4}>Se încarcă logurile...</Text>
              </Box>
            ) : (
              <Box overflowX="auto">
                <Table variant="simple">
                  <Thead>
                    <Tr>
                      <Th>Nivel</Th>
                      <Th>Utilizator</Th>
                      <Th>Acțiune</Th>
                      <Th>Descriere</Th>
                      <Th>IP</Th>
                      <Th>Data</Th>
                      <Th>Acțiuni</Th>
                    </Tr>
                  </Thead>
                  <Tbody>
                    {logs.map((log) => (
                      <Tr key={log.id}>
                        <Td>
                          <HStack spacing={2}>
                            {getLevelIcon(log.level)}
                            <Badge colorScheme={getLevelColor(log.level)}>
                              {log.level.toUpperCase()}
                            </Badge>
                          </HStack>
                        </Td>
                        <Td>
                          <Text fontWeight="medium">{log.user_name}</Text>
                        </Td>
                        <Td>
                          <Text fontSize="sm">{log.action}</Text>
                        </Td>
                        <Td>
                          <Text fontSize="sm" noOfLines={2}>
                            {log.description}
                          </Text>
                        </Td>
                        <Td>
                          <Text fontSize="sm" fontFamily="mono">
                            {log.ip_address}
                          </Text>
                        </Td>
                        <Td>
                          <Text fontSize="sm">
                            {formatDate(log.created_at)}
                          </Text>
                        </Td>
                        <Td>
                          <Menu>
                            <MenuButton
                              as={IconButton}
                              icon={<FiMoreVertical />}
                              variant="ghost"
                              size="sm"
                            />
                            <MenuList>
                              <MenuItem
                                icon={<FiEye />}
                                onClick={() => {
                                  setSelectedLog(log);
                                  onOpen();
                                }}
                              >
                                Vezi Detalii
                              </MenuItem>
                              <MenuItem
                                icon={<FiTrash2 />}
                                color="red.500"
                                onClick={() => {
                                  // Handle delete log
                                  toast({
                                    title: 'Funcționalitate în dezvoltare',
                                    description: 'Ștergerea logurilor va fi disponibilă în curând',
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
                        </Td>
                      </Tr>
                    ))}
                  </Tbody>
                </Table>
              </Box>
            )}
          </CardBody>
        </Card>

        {/* Pagination */}
        {totalPages > 1 && (
          <HStack justify="center" spacing={4}>
            <Button
              variant="outline"
              onClick={() => setCurrentPage(currentPage - 1)}
              isDisabled={currentPage === 1}
            >
              Anterior
            </Button>
            <Text>
              Pagina {currentPage} din {totalPages}
            </Text>
            <Button
              variant="outline"
              onClick={() => setCurrentPage(currentPage + 1)}
              isDisabled={currentPage === totalPages}
            >
              Următor
            </Button>
          </HStack>
        )}
      </VStack>

      {/* Log Details Modal */}
      <Modal isOpen={isOpen} onClose={onClose} size="xl">
        <ModalOverlay />
        <ModalContent>
          <ModalHeader>Detalii Log</ModalHeader>
          <ModalCloseButton />
          <ModalBody pb={6}>
            {selectedLog && (
              <VStack spacing={4} align="stretch">
                <HStack justify="space-between">
                  <Text fontWeight="medium">Nivel:</Text>
                  <Badge colorScheme={getLevelColor(selectedLog.level)}>
                    {selectedLog.level.toUpperCase()}
                  </Badge>
                </HStack>
                
                <HStack justify="space-between">
                  <Text fontWeight="medium">Utilizator:</Text>
                  <Text>{selectedLog.user_name}</Text>
                </HStack>
                
                <HStack justify="space-between">
                  <Text fontWeight="medium">Acțiune:</Text>
                  <Text>{selectedLog.action}</Text>
                </HStack>
                
                <Box>
                  <Text fontWeight="medium" mb={2}>Descriere:</Text>
                  <Text>{selectedLog.description}</Text>
                </Box>
                
                <HStack justify="space-between">
                  <Text fontWeight="medium">IP Address:</Text>
                  <Text fontFamily="mono">{selectedLog.ip_address}</Text>
                </HStack>
                
                <HStack justify="space-between">
                  <Text fontWeight="medium">Data:</Text>
                  <Text>{formatDate(selectedLog.created_at)}</Text>
                </HStack>
                
                <Box>
                  <Text fontWeight="medium" mb={2}>User Agent:</Text>
                  <Text fontSize="sm" fontFamily="mono" bg="gray.50" p={2} borderRadius="md">
                    {selectedLog.user_agent}
                  </Text>
                </Box>
                
                {selectedLog.metadata && (
                  <Box>
                    <Text fontWeight="medium" mb={2}>Metadata:</Text>
                    <Textarea
                      value={JSON.stringify(selectedLog.metadata, null, 2)}
                      isReadOnly
                      fontFamily="mono"
                      fontSize="sm"
                      rows={6}
                    />
                  </Box>
                )}
              </VStack>
            )}
          </ModalBody>
        </ModalContent>
      </Modal>
    </Container>
  );
}

export default LogsPage; 