<template>
  <aside class="w-64 bg-slate-950/20 backdrop-blur-xl border-r border-slate-900/60 flex flex-col h-screen fixed left-0 top-0 z-30 shadow-[4px_0_24px_rgba(0,0,0,0.35)]">
    <!-- Header/Logo -->
    <div class="h-20 flex items-center justify-center px-6 border-b border-slate-900/60">
      <img src="/logo_business.webp" alt="CRM Automotriz" class="h-10 object-contain filter brightness-0 invert" />
    </div>

    <!-- Menú de Navegación -->
    <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto">
      <router-link
        to="/"
        class="flex items-center gap-3 px-4 py-3 rounded-lg text-slate-400 hover:text-slate-100 hover:bg-slate-900/40 border border-transparent hover:border-white/5 transition-all font-medium"
        active-class="bg-amber-500/10 border-l-4 border-amber-500 text-amber-400 hover:bg-amber-500/10 font-bold border-t border-b border-r border-white/5 shadow-[inset_0_0_12px_rgba(245,158,11,0.05)]"
      >
        <i class="fas fa-chart-line w-5"></i>
        <span>Dashboard</span>
      </router-link>

      <router-link
        to="/prospectos"
        class="flex items-center gap-3 px-4 py-3 rounded-lg text-slate-400 hover:text-slate-100 hover:bg-slate-900/40 border border-transparent hover:border-white/5 transition-all font-medium"
        active-class="bg-amber-500/10 border-l-4 border-amber-500 text-amber-400 hover:bg-amber-500/10 font-bold border-t border-b border-r border-white/5 shadow-[inset_0_0_12px_rgba(245,158,11,0.05)]"
      >
        <i class="fas fa-users w-5"></i>
        <span>Prospectos</span>
      </router-link>

      <router-link
        to="/ventas"
        class="flex items-center gap-3 px-4 py-3 rounded-lg text-slate-400 hover:text-slate-100 hover:bg-slate-900/40 border border-transparent hover:border-white/5 transition-all font-medium"
        active-class="bg-amber-500/10 border-l-4 border-amber-500 text-amber-400 hover:bg-amber-500/10 font-bold border-t border-b border-r border-white/5 shadow-[inset_0_0_12px_rgba(245,158,11,0.05)]"
      >
        <i class="fas fa-file-invoice-dollar w-5"></i>
        <span>Ventas</span>
      </router-link>

      <router-link
        to="/seguros"
        class="flex items-center gap-3 px-4 py-3 rounded-lg text-slate-400 hover:text-slate-100 hover:bg-slate-900/40 border border-transparent hover:border-white/5 transition-all font-medium"
        active-class="bg-amber-500/10 border-l-4 border-amber-500 text-amber-400 hover:bg-amber-500/10 font-bold border-t border-b border-r border-white/5 shadow-[inset_0_0_12px_rgba(245,158,11,0.05)]"
      >
        <i class="fas fa-shield-alt w-5"></i>
        <span>Seguros Vehiculares</span>
      </router-link>
    </nav>

    <!-- Footer Vendedor & Logout -->
    <div class="p-4 border-t border-slate-900/60 bg-slate-950/20">
      <div v-if="user" class="flex items-center gap-3 mb-4 px-2">
        <div class="w-10 h-10 rounded-full bg-slate-900/50 border border-white/5 flex items-center justify-center text-amber-400 font-bold shadow-inner">
          {{ user.nombre.charAt(0).toUpperCase() }}
        </div>
        <div class="overflow-hidden">
          <p class="text-sm font-bold text-slate-200 truncate">{{ user.nombre }}</p>
          <p class="text-xs text-slate-500 truncate">{{ user.email }}</p>
        </div>
      </div>
      
      <button
        @click="handleLogout"
        class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg border border-slate-900/60 hover:border-red-500/30 text-slate-400 hover:text-red-400 hover:bg-red-500/5 transition-all text-sm font-semibold"
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
