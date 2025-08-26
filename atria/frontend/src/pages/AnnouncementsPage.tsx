import { Box, Card, CardBody, Heading, Text, VStack } from '@chakra-ui/react';

const sampleAnnouncements = [
  { id: 1, title: 'Revizie instalație apă', content: 'Revizia va avea loc vineri între 10:00-12:00.' },
  { id: 2, title: 'Curățenie spații comune', content: 'Sâmbătă dimineață se face curățenie pe scările A și B.' },
  { id: 3, title: 'Acces piscină', content: 'Piscina este deschisă zilnic 06:00-22:00.' },
];

export default function AnnouncementsPage() {
  return (
    <Box maxW="5xl" mx="auto" py={8} px={4}>
      <Heading size="lg" color="brand.600" mb={6}>Anunțuri</Heading>
      <VStack spacing={4} align="stretch">
        {sampleAnnouncements.map(a => (
          <Card key={a.id}>
            <CardBody>
              <Heading size="md" mb={2}>{a.title}</Heading>
              <Text color="gray.700">{a.content}</Text>
            </CardBody>
          </Card>
        ))}
      </VStack>
    </Box>
  );
}
