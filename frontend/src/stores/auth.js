import { defineStore } from 'pinia';
import axios from 'axios';

// Configurar axios global para que siempre envíe/reciba cookies (necesario para login y refresh)
axios.defaults.withCredentials = true;

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: JSON.parse(localStorage.getItem('auth_user')) || null,
    loading: false,
    error: null,
  }),

  getters: {
    // La autenticación ahora es implícita basada en si tenemos metadatos del usuario.
    // Si la cookie expira, cualquier consulta subsiguiente fallará con un 401.
    isAuthenticated: (state) => !!state.user,
  },

  actions: {
    async login(email, password) {
      this.loading = true;
      this.error = null;
      try {
        const response = await axios.post('/api/auth/login', { email, password });
        
        // La cookie 'auth_token' es inyectada automáticamente por el navegador
        const { vendedor } = response.data;
        
        this.user = vendedor;
        localStorage.setItem('auth_user', JSON.stringify(vendedor));
        return true;
      } catch (err) {
        this.error = err.response?.data?.message || 'Error al iniciar sesión';
        throw err;
      } finally {
        this.loading = false;
      }
    },

    async refreshToken() {
      try {
        // Llama al refresh y el servidor devuelve una cookie 'auth_token' renovada
        await axios.post('/api/auth/refresh', {});
      } catch (err) {
        this.logout();
        throw err;
      }
    },

    logout() {
      this.user = null;
      localStorage.removeItem('auth_user');
      
      // Llamar al backend para expirar la cookie en el servidor
      axios.post('/api/auth/logout', {}).catch(() => {});
    }
  }
});
