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

      <!-- Módulo Desplegable Generales -->
      <div v-if="canViewGenerales" class="space-y-1">
        <button
          @click="toggleGenerales"
          class="w-full flex items-center justify-between px-4 py-3 rounded-lg text-slate-400 hover:text-slate-100 hover:bg-slate-900/40 border border-transparent hover:border-white/5 transition-all font-medium focus:outline-none"
        >
          <div class="flex items-center gap-3">
            <i class="fas fa-cogs w-5"></i>
            <span>Generales</span>
          </div>
          <i
            :class="[
              'fas fa-chevron-down text-xxs transition-transform duration-300',
              generalesOpen && 'transform rotate-180 text-amber-500'
            ]"
          ></i>
        </button>

        <transition
          enter-active-class="transition duration-150 ease-out"
          enter-from-class="transform scale-95 opacity-0 -translate-y-2"
          enter-to-class="transform scale-100 opacity-100 translate-y-0"
          leave-active-class="transition duration-100 ease-in"
          leave-from-class="transform scale-100 opacity-100 translate-y-0"
          leave-to-class="transform scale-95 opacity-0 -translate-y-2"
        >
          <div v-if="generalesOpen" class="pl-4 space-y-1.5 border-l border-white/5 ml-6">
            <router-link
              v-if="canViewEmpleados"
              to="/generales/empleados"
              class="flex items-center gap-3 px-4 py-2 rounded-lg text-xs text-slate-400 hover:text-slate-100 hover:bg-slate-900/20 border border-transparent hover:border-white/5 transition-all font-medium"
              active-class="bg-amber-500/10 text-amber-400 font-bold border-l-2 border-amber-500"
            >
              <i class="fas fa-user-tie w-4"></i>
              <span>Colaboradores</span>
            </router-link>

            <router-link
              v-if="canViewClientes"
              to="/generales/clientes"
              class="flex items-center gap-3 px-4 py-2 rounded-lg text-xs text-slate-400 hover:text-slate-100 hover:bg-slate-900/20 border border-transparent hover:border-white/5 transition-all font-medium"
              active-class="bg-amber-500/10 text-amber-400 font-bold border-l-2 border-amber-500"
            >
              <i class="fas fa-address-book w-4"></i>
              <span>Clientes</span>
            </router-link>

            <router-link
              v-if="canViewRoles"
              to="/generales/roles"
              class="flex items-center gap-3 px-4 py-2 rounded-lg text-xs text-slate-400 hover:text-slate-100 hover:bg-slate-900/20 border border-transparent hover:border-white/5 transition-all font-medium"
              active-class="bg-amber-500/10 text-amber-400 font-bold border-l-2 border-amber-500"
            >
              <i class="fas fa-user-shield w-4"></i>
              <span>Roles y Permisos</span>
            </router-link>
          </div>
        </transition>
      </div>
    </nav>

    <!-- Footer Vendedor & Logout -->
    <div class="p-4 border-t border-slate-900/60 bg-slate-950/20">
      <div v-if="user" class="flex items-center gap-3 mb-4 px-2">
        <div class="w-10 h-10 rounded-full bg-slate-900/50 border border-white/5 flex items-center justify-center text-amber-400 font-bold shadow-inner">
          {{ user.nombre.charAt(0).toUpperCase() }}
        </div>
        <div class="overflow-hidden">
          <p class="text-sm font-bold text-slate-200 truncate">{{ user.nombre }}</p>
          <p class="text-[10px] text-amber-500/80 font-bold uppercase tracking-wider mt-0.5 truncate">{{ user.rol?.nombre }}</p>
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
import { ref, computed } from 'vue';
import { useAuthStore } from '../stores/auth';
import { useRouter } from 'vue-router';
import { useNotification } from '../composables/useNotification';

export default {
  setup() {
    const authStore = useAuthStore();
    const router = useRouter();
    const notification = useNotification();

    const user = computed(() => authStore.user);

    const generalesOpen = ref(false);
    const toggleGenerales = () => {
      generalesOpen.value = !generalesOpen.value;
    };

    const canViewGenerales = computed(() => {
      return authStore.hasPermission('ver_empleados') || 
             authStore.hasPermission('ver_roles') || 
             authStore.hasPermission('ver_clientes');
    });

    const canViewEmpleados = computed(() => authStore.hasPermission('ver_empleados'));
    const canViewClientes = computed(() => authStore.hasPermission('ver_clientes'));
    const canViewRoles = computed(() => authStore.hasPermission('ver_roles'));

    const handleLogout = () => {
      authStore.logout();
      notification.showInfo('Sesión cerrada exitosamente.');
      router.push('/login');
    };

    return {
      user,
      generalesOpen,
      toggleGenerales,
      canViewGenerales,
      canViewEmpleados,
      canViewClientes,
      canViewRoles,
      handleLogout
    };
  }
};
</script>
