import {
  Box,
  VStack,
  Card,
  CardBody,
  Stat,
  StatLabel,
  StatNumber,
  StatHelpText,
  Icon,
  Grid,
  Text,
  Heading,
} from '@chakra-ui/react';
import { FiTrendingUp } from 'react-icons/fi';
import AdminLayout from '../components/AdminLayout';

function AdminDashboard() {

  return (
    <AdminLayout>
      <VStack spacing={6} align="stretch">
        <Box>
          <Heading size="lg" color="brand.600">Panou de Administrare</Heading>
          <Text color="gray.600" mt={2}>
            Bine ai venit în panoul de administrare F1 Atria
          </Text>
        </Box>
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
            </VStack>
          </AdminLayout>
        );
      }

export default AdminDashboard;
