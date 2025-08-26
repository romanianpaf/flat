import { useEffect, useState } from 'react';
import { Box, Button, Card, CardBody, Heading, HStack, IconButton, Input, Stack, Text, Textarea, useToast, VStack, Badge } from '@chakra-ui/react';
import { FiThumbsUp, FiPlus } from 'react-icons/fi';
import axios from '../lib/axios';

interface User {
  id: number;
  name: string;
  email: string;
  role: string;
  tenant_id?: number;
}

interface Item {
  id: number;
  title: string;
  description?: string;
  status: 'open' | 'closed';
  votes_count: number;
  created_at: string;
  creator: { id: number; name: string; email: string };
}

export default function UserVoicePage() {
  const toast = useToast();
  const [user, setUser] = useState<User | null>(null);
  const [items, setItems] = useState<Item[]>([]);
  const [userVotes, setUserVotes] = useState<number[]>([]);
  const [loading, setLoading] = useState(true);
  const [creating, setCreating] = useState(false);
  const [newTitle, setNewTitle] = useState('');
  const [newDesc, setNewDesc] = useState('');

  const canCreate = (u: User | null) => !!u && ['locatar', 'cex', 'admin', 'tenantadmin', 'sysadmin'].includes(u.role);
  const canVote = (u: User | null, item: Item) => !!u && u.role !== 'tehnic' && item.status === 'open' && !userVotes.includes(item.id);

  const fetchAll = async () => {
    setLoading(true);
    try {
      const [userRes, listRes] = await Promise.all([
        axios.get('/user'),
        axios.get('/user-voice'),
      ]);
      setUser(userRes.data.user);
      setItems(listRes.data.items);
      setUserVotes(listRes.data.user_votes || []);
    } catch (e: any) {
      toast({ title: 'Eroare la încărcare', status: 'error', duration: 4000, isClosable: true });
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => { fetchAll(); }, []);

  const handleCreate = async () => {
    if (!newTitle.trim()) {
      toast({ title: 'Titlul este obligatoriu', status: 'warning', duration: 3000, isClosable: true });
      return;
    }
    setCreating(true);
    try {
      await axios.post('/user-voice', { title: newTitle, description: newDesc });
      setNewTitle('');
      setNewDesc('');
      await fetchAll();
      toast({ title: 'Propunere creată', status: 'success', duration: 3000, isClosable: true });
    } catch (e: any) {
      const m = e?.response?.data?.message || 'Eroare la creare';
      toast({ title: 'Eroare', description: m, status: 'error', duration: 4000, isClosable: true });
    } finally {
      setCreating(false);
    }
  };

  const handleVote = async (id: number) => {
    try {
      await axios.post(`/user-voice/${id}/vote`);
      await fetchAll();
      toast({ title: 'Vot înregistrat', status: 'success', duration: 2500, isClosable: true });
    } catch (e: any) {
      const m = e?.response?.data?.message || 'Eroare la vot';
      toast({ title: 'Eroare', description: m, status: 'error', duration: 4000, isClosable: true });
    }
  };

  return (
    <Box maxW="6xl" mx="auto" py={8} px={4}>
      <HStack justify="space-between" mb={6}>
        <Heading size="lg" color="brand.600">User Voice</Heading>
        {canCreate(user) && (
          <HStack>
            <Input placeholder="Titlu propunere" value={newTitle} onChange={(e) => setNewTitle(e.target.value)} maxW="sm" />
            <Textarea placeholder="Descriere (opțional)" value={newDesc} onChange={(e) => setNewDesc(e.target.value)} maxW="md" />
            <Button leftIcon={<FiPlus />} colorScheme="brand" onClick={handleCreate} isLoading={creating}>Adaugă</Button>
          </HStack>
        )}
      </HStack>

      <Stack spacing={4}>
        {items.map(item => (
          <Card key={item.id} variant="outline">
            <CardBody>
              <HStack justify="space-between" align="start">
                <VStack align="start" spacing={1} flex={1}>
                  <HStack>
                    <Heading size="md">{item.title}</Heading>
                    <Badge colorScheme={item.status === 'open' ? 'green' : 'gray'}>{item.status}</Badge>
                  </HStack>
                  {item.description && <Text color="gray.700">{item.description}</Text>}
                  <Text fontSize="sm" color="gray.500">Propus de {item.creator?.name || '—'} • {new Date(item.created_at).toLocaleString('ro-RO')}</Text>
                </VStack>
                <HStack>
                  <Badge colorScheme="blue" fontSize="md">{item.votes_count} voturi</Badge>
                  <IconButton aria-label="Votează" icon={<FiThumbsUp />} onClick={() => handleVote(item.id)} isDisabled={!canVote(user, item)} />
                </HStack>
              </HStack>
            </CardBody>
          </Card>
        ))}
        {!items.length && !loading && (
          <Text color="gray.600">Nu există propuneri încă.</Text>
        )}
      </Stack>
    </Box>
  );
}
