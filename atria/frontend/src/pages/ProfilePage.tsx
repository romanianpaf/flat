import { Box, Card, CardBody, Divider, Heading, Text, VStack } from '@chakra-ui/react';

interface ProfilePageProps {
  user?: { name: string; email: string; role: string; tenant_id?: number } | null;
}

export default function ProfilePage({ user }: ProfilePageProps) {
  return (
    <Box maxW="4xl" mx="auto" py={8} px={4}>
      <Heading size="lg" color="brand.600" mb={6}>Profilul Meu</Heading>
      <Card>
        <CardBody>
          <VStack align="start" spacing={3}>
            <Text><strong>Nume:</strong> {user?.name || '-'}</Text>
            <Divider />
            <Text><strong>Email:</strong> {user?.email || '-'}</Text>
            <Divider />
            <Text><strong>Rol:</strong> {user?.role || '-'}</Text>
            <Divider />
            <Text><strong>Tenant ID:</strong> {user?.tenant_id ?? '-'}</Text>
          </VStack>
        </CardBody>
      </Card>
    </Box>
  );
}
