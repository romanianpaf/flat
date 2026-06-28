import axios from 'axios';

const API_URL = import.meta.env.VITE_API_BASE_URL + '/';

// Interceptor pentru răspunsuri
axios.interceptors.response.use(
  (response) => {
    return response;
  },
  async (error) => {
    const originalRequest = error.config;

    // Dacă primim 401 (Unauthorized) și nu am încercat deja să refresh token-ul
    if (error.response?.status === 401 && !originalRequest._retry) {
      originalRequest._retry = true;

      try {
        // Încearcă să faci refresh la token
        const refreshToken = localStorage.getItem('refresh_token');
        if (refreshToken) {
          const response = await axios.post('https://f1.atria.live/oauth/token', {
            grant_type: 'refresh_token',
            refresh_token: refreshToken,
            client_id: import.meta.env.VITE_CLIENT_ID,
            client_secret: import.meta.env.VITE_CLIENT_SECRET,
          });

          const { access_token, refresh_token: newRefreshToken } = response.data;
          
          // Salvează noul token
          localStorage.setItem('token', access_token);
          if (newRefreshToken) {
            localStorage.setItem('refresh_token', newRefreshToken);
          }

          // Reîncarcă profilul userului pentru a păstra roles/permissions
          try {
            const profileResponse = await axios.get(API_URL + 'me', {
              headers: {
                Authorization: `Bearer ${access_token}`,
                Accept: 'application/vnd.api+json',
              },
            });
            
            const profileData = profileResponse.data.data;
            const userData = {
              id: profileData.id,
              first_name: profileData.first_name,
              last_name: profileData.last_name,
              name: `${profileData.first_name || ''} ${profileData.last_name || ''}`.trim(),
              email: profileData.email,
              tenant_id: profileData.tenant_id,
              tenant: profileData.tenant,
              is_system_admin: profileData.is_system_admin || false,
              active_tenant_id: profileData.active_tenant_id ?? null,
              roles: profileData.roles || [],
              permissions: profileData.permissions || [],
            };
            localStorage.setItem('user', JSON.stringify(userData));
          } catch (profileError) {
            console.error('Failed to refresh user profile:', profileError);
          }

          // Reîncearcă request-ul original cu noul token
          originalRequest.headers.Authorization = `Bearer ${access_token}`;
          return axios(originalRequest);
        }
      } catch (refreshError) {
        // Dacă refresh-ul eșuează, șterge token-urile și redirectează
        console.error('Token refresh failed:', refreshError);
        localStorage.removeItem('user');
        localStorage.removeItem('token');
        localStorage.removeItem('refresh_token');
        window.location.href = '/login';
        return Promise.reject(refreshError);
      }
    }

    // 403 (Forbidden) = autentificat dar nu ai permisiune pentru acțiune
    // NU facem logout, doar lăsăm eroarea să ajungă la componentă
    // pentru a afișa mesaj de eroare corespunzător

    return Promise.reject(error);
  }
);

export default axios;
