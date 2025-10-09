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
            client_id: process.env.VUE_APP_CLIENT_ID,
            client_secret: process.env.VUE_APP_CLIENT_SECRET,
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

    // Pentru erori 403 (Forbidden), șterge token-urile și redirectează
    if (error.response?.status === 403) {
      localStorage.removeItem('user');
      localStorage.removeItem('refresh_token');
      window.location.href = '/login';
    }

    return Promise.reject(error);
  }
);

export default axios;
