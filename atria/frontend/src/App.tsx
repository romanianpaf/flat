import { useState, useEffect } from 'react';
import { ChakraProvider } from '@chakra-ui/react';
import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import { theme } from './theme';
import Navigation from './components/Navigation';
import Home from './pages/Home';
import AdminDashboard from './pages/AdminDashboard';
import UsersManagement from './pages/UsersManagement';
import RolesManagement from './pages/RolesManagement';
import LogsPage from './pages/LogsPage';
import AutomationPage from './pages/AutomationPage';
import TenantsPage from './pages/TenantsPage';
import axios from './lib/axios';

interface User {
  id: number;
  name: string;
  email: string;
  role: string;
  tenant_id?: number;
}

function App() {
  const [user, setUser] = useState<User | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const token = localStorage.getItem('token');
    if (token) {
      axios.get('/api/user')
        .then(response => {
          setUser(response.data.user);
        })
        .catch(() => {
          localStorage.removeItem('token');
        })
        .finally(() => {
          setLoading(false);
        });
    } else {
      setLoading(false);
    }
  }, []);

  const handleLogout = () => {
    localStorage.removeItem('token');
    setUser(null);
  };

  if (loading) {
    return <div>Loading...</div>;
  }

  return (
    <ChakraProvider theme={theme}>
      <BrowserRouter>
        <Navigation user={user} onLogout={handleLogout} />
        <Routes>
          <Route path="/" element={<Home user={user} />} />
          <Route path="/admin" element={<AdminDashboard />} />
          <Route path="/admin/roles" element={<RolesManagement />} />
          <Route path="/admin/users" element={<UsersManagement />} />
          <Route path="/admin/logs" element={<LogsPage />} />
          <Route path="/admin/automations" element={<AutomationPage />} />
          <Route path="/admin/tenants" element={<TenantsPage />} />
          <Route path="*" element={<Navigate to="/" />} />
        </Routes>
      </BrowserRouter>
    </ChakraProvider>
  );
}

export default App;
