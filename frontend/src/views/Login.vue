<template>
  <div 
    class="min-h-screen w-full flex items-center justify-center bg-slate-950 px-4 relative overflow-hidden font-sans text-slate-100"
    style="background-image: url('/geometric_bg.png'); background-size: cover; background-position: center;"
  >
    <!-- Background overlay for contrast and blur effect -->
    <div class="absolute inset-0 bg-slate-950/80 backdrop-blur-sm pointer-events-none"></div>

    <div class="w-full max-w-md relative z-10 my-8">
      <!-- Main Login Card -->
      <main class="glass-panel p-6 sm:p-8 rounded-2xl flex flex-col items-center">
        <!-- Logo & Header -->
        <div class="mb-6 sm:mb-8 flex flex-col items-center text-center">
          <div class="w-16 h-16 mb-4 rounded-xl flex items-center justify-center p-2 bg-white/5 border border-white/10 shadow-[0_0_20px_rgba(245,158,11,0.15)] text-amber-500 text-3xl">
            <i class="fas fa-car-side"></i>
          </div>
          <h1 class="text-3xl font-extrabold tracking-tight text-white">CRM Automotriz</h1>
          <p class="text-sm text-slate-400 mt-2">Acceda a su entorno de trabajo premium</p>
        </div>

        <!-- Login Form -->
        <form @submit.prevent="handleSubmit" class="w-full space-y-5">
          <!-- Email Input -->
          <div class="space-y-1">
            <label class="block text-xs font-semibold text-slate-400 ml-1 uppercase tracking-wider" for="email">Correo Electrónico</label>
            <div class="relative flex items-center px-4 py-3 bg-slate-900/40 border border-slate-800/80 rounded-xl focus-within:border-amber-500 focus-within:ring-1 focus-within:ring-amber-500/30 transition-all duration-300 group">
              <span class="text-slate-500 group-focus-within:text-amber-500 transition-colors duration-300 mr-3 text-lg">
                <i class="fas fa-envelope"></i>
              </span>
              <input
                id="email"
                v-model="email"
                type="email"
                placeholder="juan.perez@automotriz.com"
                required
                class="w-full bg-transparent border-none p-0 focus:ring-0 text-slate-100 placeholder-slate-600 text-sm h-full outline-none"
              />
            </div>
          </div>

          <!-- Password Input -->
          <div class="space-y-1">
            <div class="flex justify-between items-center ml-1">
              <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider" for="password">Contraseña</label>
            </div>
            <div class="relative flex items-center px-4 py-3 bg-slate-900/40 border border-slate-800/80 rounded-xl focus-within:border-amber-500 focus-within:ring-1 focus-within:ring-amber-500/30 transition-all duration-300 group">
              <span class="text-slate-500 group-focus-within:text-amber-500 transition-colors duration-300 mr-3 text-lg">
                <i class="fas fa-lock"></i>
              </span>
              <input
                id="password"
                v-model="password"
                type="password"
                placeholder="••••••••"
                required
                class="w-full bg-transparent border-none p-0 focus:ring-0 text-slate-100 placeholder-slate-600 text-sm h-full outline-none"
              />
            </div>
          </div>

          <!-- Submit Button -->
          <button
            type="submit"
            :disabled="loading"
            class="w-full mt-6 py-3.5 px-4 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-slate-950 font-bold rounded-xl shadow-[0_4px_14px_0_rgba(245,158,11,0.25)] hover:shadow-[0_6px_20px_rgba(245,158,11,0.4)] hover:-translate-y-0.5 active:translate-y-0 active:shadow-[0_4px_14px_0_rgba(245,158,11,0.25)] transition-all duration-200 flex justify-center items-center gap-2"
          >
            <span v-if="loading" class="animate-spin border-2 border-slate-950 border-t-transparent rounded-full w-5 h-5"></span>
            <span v-else class="flex items-center gap-2">
              Iniciar Sesión
              <i class="fas fa-arrow-right text-xs"></i>
            </span>
          </button>
        </form>
      </main>

      <!-- Helper Panel -->
      <div class="mt-6 p-4 bg-slate-900/40 border border-slate-800/80 backdrop-blur-md rounded-xl flex items-start gap-3 w-full">
        <div class="bg-amber-500/10 p-2 rounded-lg shrink-0 text-amber-500">
          <i class="fas fa-info-circle text-lg"></i>
        </div>
        <div class="w-full">
          <h3 class="text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Credenciales de Prueba</h3>
          <div class="text-xs text-slate-400 flex flex-col gap-1.5 w-full">
            <span class="flex justify-between border-b border-slate-800/80 pb-1.5">Usuario: <strong class="text-amber-400">juan.perez@automotriz.com</strong></span>
            <span class="flex justify-between">Contraseña: <strong class="text-amber-400">password123</strong></span>
          </div>
        </div>
      </div>
    </div>

    <!-- Minimal Footer -->
    <footer class="absolute bottom-6 w-full text-center">
      <p class="text-xs text-slate-600">
        © 2026 CRM Automotriz Premium. Todos los derechos reservados.
      </p>
    </footer>
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
