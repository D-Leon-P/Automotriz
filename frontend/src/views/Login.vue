<template>
  <div class="min-h-screen flex items-center justify-center bg-slate-950 px-4 relative overflow-hidden">
    <!-- Círculos decorativos de fondo con difuminado -->
    <div class="absolute -top-40 -left-40 w-96 h-96 rounded-full bg-brand-dark/20 blur-3xl"></div>
    <div class="absolute -bottom-40 -right-40 w-96 h-96 rounded-full bg-brand-light/10 blur-3xl"></div>

    <!-- Panel de Login Glassmorphism -->
    <div class="w-full max-w-md glass-panel p-8 relative z-10">
      <!-- Logo & Encabezado -->
      <div class="text-center mb-8">
        <div class="w-14 h-14 rounded-xl bg-gradient-to-tr from-brand-dark to-brand-light flex items-center justify-center text-slate-950 font-bold text-3xl mx-auto shadow-xl shadow-brand/20 mb-3">
          <i class="fas fa-car-side"></i>
        </div>
        <h2 class="text-2xl font-extrabold text-slate-100">Bienvenido de nuevo</h2>
        <p class="text-sm text-slate-400 mt-1">Ingresa tus credenciales para acceder al CRM Automotriz</p>
      </div>

      <!-- Formulario -->
      <form @submit.prevent="handleSubmit" class="space-y-6">
        <div>
          <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Correo Electrónico</label>
          <div class="relative">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-500">
              <i class="fas fa-envelope"></i>
            </span>
            <input
              id="email"
              v-model="email"
              type="email"
              required
              placeholder="vendedor@automotriz.com"
              class="w-full pl-10 pr-4 py-3 bg-slate-800/80 border border-slate-700/80 rounded-lg text-slate-100 placeholder-slate-500 focus:outline-none focus:border-brand focus:ring-1 focus:ring-brand/30 transition-all text-sm"
            />
          </div>
        </div>

        <div>
          <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Contraseña</label>
          <div class="relative">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-500">
              <i class="fas fa-lock"></i>
            </span>
            <input
              id="password"
              v-model="password"
              type="password"
              required
              placeholder="••••••••"
              class="w-full pl-10 pr-4 py-3 bg-slate-800/80 border border-slate-700/80 rounded-lg text-slate-100 placeholder-slate-500 focus:outline-none focus:border-brand focus:ring-1 focus:ring-brand/30 transition-all text-sm"
            />
          </div>
        </div>

        <button
          type="submit"
          :disabled="loading"
          class="w-full py-3 px-4 bg-gradient-to-r from-brand to-brand-light hover:from-brand-dark hover:to-brand text-slate-950 font-extrabold rounded-lg shadow-lg hover:shadow-brand/20 transition-all duration-300 flex items-center justify-center gap-2"
        >
          <span v-if="loading" class="animate-spin border-2 border-slate-950 border-t-transparent rounded-full w-5 h-5"></span>
          <span v-else>Iniciar Sesión</span>
        </button>
      </form>

      <!-- Credenciales de Prueba -->
      <div class="mt-8 pt-6 border-t border-slate-800/80 text-center">
        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Credenciales de prueba:</p>
        <code class="block text-xs bg-slate-950/80 border border-slate-800 p-2 rounded text-brand-light">
          Usuario: juan.perez@automotriz.com <br/>
          Contraseña: password123
        </code>
      </div>
    </div>
  </div>
</template>

<script>
import { ref } from 'vue';
import { useAuthStore } from '../stores/auth';
import { useRouter } from 'vue-router';
import { useNotification } from '../composables/useNotification';

export default {
  setup() {
    const authStore = useAuthStore();
    const router = useRouter();
    const notification = useNotification();

    const email = ref('juan.perez@automotriz.com');
    const password = ref('password123');
    const loading = ref(false);

    const handleSubmit = async () => {
      loading.value = true;
      try {
        await authStore.login(email.value, password.value);
        notification.showSuccess('¡Inicio de sesión exitoso!');
        router.push('/');
      } catch (err) {
        notification.showError(err.response?.data?.message || 'Error al iniciar sesión. Verifique sus credenciales.');
      } finally {
        loading.value = false;
      }
    };

    return {
      email,
      password,
      loading,
      handleSubmit,
    };
  },
};
</script>
