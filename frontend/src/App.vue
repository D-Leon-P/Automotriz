<template>
  <div class="min-h-screen bg-slate-950 text-slate-100 flex">
    <!-- Contenedor Global de Notificaciones -->
    <NotificationContainer />

    <!-- Layout con Sidebar para páginas protegidas -->
    <template v-if="requiresLayout">
      <Sidebar />
      <main class="flex-1 ml-64 min-h-screen bg-slate-950 text-slate-100 overflow-x-hidden">
        <!-- Header Superior Falso/Estético -->
        <header class="h-20 bg-slate-900/10 backdrop-blur-md border-b border-slate-900/60 px-8 flex items-center justify-end sticky top-0 z-20">
          <div class="flex items-center gap-4 text-xs font-semibold text-slate-400">
            <span class="flex items-center gap-1.5"><i class="fas fa-circle text-green-500 text-[8px]"></i> Conectado al Gateway</span>
            <span class="text-slate-700">|</span>
            <span>MySQL Centralizada</span>
          </div>
        </header>
        
        <!-- Contenido de las Vistas -->
        <router-view v-slot="{ Component }">
          <transition name="fade" mode="out-in">
            <component :is="Component" />
          </transition>
        </router-view>
      </main>
    </template>

    <!-- Layout limpio para Login / Invitados -->
    <template v-else>
      <div class="w-full min-h-screen">
        <router-view />
      </div>
    </template>
  </div>
</template>

<script>
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import Sidebar from './components/Sidebar.vue';
import NotificationContainer from './components/NotificationContainer.vue';

export default {
  components: {
    Sidebar,
    NotificationContainer
  },
  setup() {
    const route = useRoute();

    // Determinar si la ruta actual requiere el layout principal con Sidebar
    const requiresLayout = computed(() => {
      return route.meta?.requiresAuth === true;
    });

    return {
      requiresLayout
    };
  }
};
</script>

<style>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
