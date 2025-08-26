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
import ProfilePage from './pages/ProfilePage';
import AnnouncementsPage from './pages/AnnouncementsPage';
import AccessPage from './pages/AccessPage';
import UserVoicePage from './pages/UserVoicePage';
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
      axios.get('/user')
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

  const isAdminLike = (u: User | null) => {
    const role = u?.role;
    return role === 'sysadmin' || role === 'admin' || role === 'tenantadmin' || role === 'cex' || role === 'tehnic';
  };
  const isSysadmin = (u: User | null) => u?.role === 'sysadmin';

  return (
    <ChakraProvider theme={theme}>
      <BrowserRouter>
        <Navigation user={user} onLogout={handleLogout} />
        <Routes>
          <Route path="/" element={<Home user={user} />} />

          {/* Pagine accesibile tuturor (după login) */}
          <Route path="/profil" element={user ? <ProfilePage user={user} /> : <Navigate to="/" />} />
          <Route path="/anunturi" element={user ? <AnnouncementsPage /> : <Navigate to="/" />} />
          <Route path="/acces" element={user ? <AccessPage /> : <Navigate to="/" />} />
          <Route path="/user-voice" element={user ? <UserVoicePage /> : <Navigate to="/" />} />

          {/* Admin-like routes */}
          <Route path="/admin" element={isAdminLike(user) ? <AdminDashboard /> : <Navigate to="/" />} />
          <Route path="/admin/roles" element={isAdminLike(user) ? <RolesManagement /> : <Navigate to="/" />} />
          <Route path="/admin/users" element={isAdminLike(user) ? <UsersManagement /> : <Navigate to="/" />} />
          <Route path="/admin/logs" element={isAdminLike(user) ? <LogsPage /> : <Navigate to="/" />} />
          <Route path="/admin/automations" element={isAdminLike(user) ? <AutomationPage /> : <Navigate to="/" />} />

          {/* Sysadmin-only */}
          <Route path="/admin/tenants" element={isSysadmin(user) ? <TenantsPage /> : <Navigate to="/" />} />

          <Route path="*" element={<Navigate to="/" />} />
        </Routes>
      </BrowserRouter>
    </ChakraProvider>
  );
}

export default App;
