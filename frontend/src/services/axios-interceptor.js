import axios from 'axios';

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
          
          // Salvează noile token-uri
          localStorage.setItem('user', JSON.stringify(access_token));
          if (newRefreshToken) {
            localStorage.setItem('refresh_token', newRefreshToken);
          }

          // Reîncearcă request-ul original cu noul token
          originalRequest.headers.Authorization = `Bearer ${access_token}`;
          return axios(originalRequest);
        }
      } catch (refreshError) {
        // Dacă refresh-ul eșuează, șterge token-urile și redirectează
        console.error('Token refresh failed:', refreshError);
        localStorage.removeItem('user');
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
