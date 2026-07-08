<template>
  <aside class="w-64 bg-slate-900 border-r border-slate-800 flex flex-col h-screen fixed left-0 top-0 z-30">
    <!-- Header/Logo -->
    <div class="h-20 flex items-center gap-3 px-6 border-b border-slate-800">
      <div class="w-10 h-10 rounded-lg bg-gradient-to-tr from-brand-dark to-brand-light flex items-center justify-center text-slate-950 font-bold text-xl shadow-lg shadow-brand/20">
        <i class="fas fa-car-side"></i>
      </div>
      <div>
        <h1 class="font-extrabold text-lg bg-gradient-to-r from-slate-50 to-slate-300 bg-clip-text text-transparent">SpectroAuto</h1>
        <p class="text-xs text-slate-500 font-semibold tracking-wider uppercase">CRM & Ventas</p>
      </div>
    </div>

    <!-- Menú de Navegación -->
    <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto">
      <router-link
        to="/"
        class="flex items-center gap-3 px-4 py-3 rounded-lg text-slate-400 hover:text-slate-100 hover:bg-slate-800/50 transition-all font-medium"
        active-class="bg-brand/10 border-l-4 border-brand text-brand-light hover:bg-brand/10 font-bold"
      >
        <i class="fas fa-chart-line w-5"></i>
        <span>Dashboard</span>
      </router-link>

      <router-link
        to="/prospectos"
        class="flex items-center gap-3 px-4 py-3 rounded-lg text-slate-400 hover:text-slate-100 hover:bg-slate-800/50 transition-all font-medium"
        active-class="bg-brand/10 border-l-4 border-brand text-brand-light hover:bg-brand/10 font-bold"
      >
        <i class="fas fa-users w-5"></i>
        <span>Prospectos</span>
      </router-link>

      <router-link
        to="/ventas"
        class="flex items-center gap-3 px-4 py-3 rounded-lg text-slate-400 hover:text-slate-100 hover:bg-slate-800/50 transition-all font-medium"
        active-class="bg-brand/10 border-l-4 border-brand text-brand-light hover:bg-brand/10 font-bold"
      >
        <i class="fas fa-file-invoice-dollar w-5"></i>
        <span>Ventas</span>
      </router-link>

      <router-link
        to="/seguros"
        class="flex items-center gap-3 px-4 py-3 rounded-lg text-slate-400 hover:text-slate-100 hover:bg-slate-800/50 transition-all font-medium"
        active-class="bg-brand/10 border-l-4 border-brand text-brand-light hover:bg-brand/10 font-bold"
      >
        <i class="fas fa-shield-alt w-5"></i>
        <span>Seguros Vehiculares</span>
      </router-link>
    </nav>

    <!-- Footer Vendedor & Logout -->
    <div class="p-4 border-t border-slate-800 bg-slate-900/50">
      <div v-if="user" class="flex items-center gap-3 mb-4 px-2">
        <div class="w-10 h-10 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center text-slate-300 font-bold">
          {{ user.nombre.charAt(0).toUpperCase() }}
        </div>
        <div class="overflow-hidden">
          <p class="text-sm font-bold text-slate-200 truncate">{{ user.nombre }}</p>
          <p class="text-xs text-slate-500 truncate">{{ user.email }}</p>
        </div>
      </div>
      
      <button
        @click="handleLogout"
        class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg border border-slate-800 hover:border-red-500/30 text-slate-400 hover:text-red-400 hover:bg-red-500/5 transition-all text-sm font-semibold"
      >
        <i class="fas fa-sign-out-alt"></i>
        <span>Cerrar Sesión</span>
      </button>
    </div>
  </aside>
</template>

<script>
import { computed } from 'vue';
import { useAuthStore } from '../stores/auth';
import { useRouter } from 'vue-router';
import { useNotification } from '../composables/useNotification';

export default {
  setup() {
    const authStore = useAuthStore();
    const router = useRouter();
    const notification = useNotification();

    const user = computed(() => authStore.user);

    const handleLogout = () => {
      authStore.logout();
      notification.showInfo('Sesión cerrada exitosamente.');
      router.push('/login');
    };

    return {
      user,
      handleLogout
    };
  }
};
</script>
