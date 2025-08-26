import { Box, Card, CardBody, Grid, GridItem, Heading, Stat, StatHelpText, StatLabel, StatNumber, Text } from '@chakra-ui/react';

export default function AccessPage() {
  return (
    <Box maxW="6xl" mx="auto" py={8} px={4}>
      <Heading size="lg" color="brand.600" mb={6}>Acces</Heading>
      <Grid templateColumns={{ base: '1fr', md: 'repeat(3, 1fr)' }} gap={4}>
        <GridItem>
          <Card><CardBody>
            <Heading size="md" mb={2}>Barieră</Heading>
            <Stat>
              <StatLabel>Status</StatLabel>
              <StatNumber>Online</StatNumber>
              <StatHelpText>Auto-close: 30s</StatHelpText>
            </Stat>
            <Text color="gray.600">Accesul se face pe baza cardului NFC.</Text>
          </CardBody></Card>
        </GridItem>
        <GridItem>
          <Card><CardBody>
            <Heading size="md" mb={2}>Piscină</Heading>
            <Stat>
              <StatLabel>Program</StatLabel>
              <StatNumber>06:00 - 22:00</StatNumber>
              <StatHelpText>Capacitate: 20</StatHelpText>
            </Stat>
            <Text color="gray.600">Accesul este permis locatarilor cu abonament activ.</Text>
          </CardBody></Card>
        </GridItem>
        <GridItem>
          <Card><CardBody>
            <Heading size="md" mb={2}>Imobil</Heading>
            <Stat>
              <StatLabel>Status</StatLabel>
              <StatNumber>Operațional</StatNumber>
              <StatHelpText>Acces 24/7</StatHelpText>
            </Stat>
            <Text color="gray.600">Ușile pot fi accesate din aplicație (curând).</Text>
          </CardBody></Card>
        </GridItem>
      </Grid>
    </Box>
  );
}
