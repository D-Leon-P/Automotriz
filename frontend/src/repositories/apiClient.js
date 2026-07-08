import axios from 'axios';
import { useAuthStore } from '../stores/auth';

const apiClient = axios.create({
  baseURL: '/api', // Enrutado a través del Gateway NGINX
  withCredentials: true, // Habilita el envío automático de cookies HttpOnly
  headers: {
    'Content-Type': 'application/json',
  },
});

// Interceptor de Respuestas: Manejar expiración del token (401)
apiClient.interceptors.response.use(
  (response) => response,
  async (error) => {
    const originalRequest = error.config;
    const authStore = useAuthStore();

    if (error.response?.status === 401 && !originalRequest._retry) {
      originalRequest._retry = true;
      try {
        // Intentar refrescar la cookie del token (el backend responderá con una nueva cookie HttpOnly)
        await authStore.refreshToken();
        return apiClient(originalRequest);
      } catch (refreshError) {
        authStore.logout();
        window.location.href = '/login';
        return Promise.reject(refreshError);
      }
    }

    return Promise.reject(error);
  }
);

export default apiClient;
