<template>
  <div class="min-h-screen bg-slate-950 text-slate-100 flex">
    <!-- Contenedor Global de Notificaciones -->
    <NotificationContainer />

    <!-- Layout con Sidebar para páginas protegidas -->
    <template v-if="requiresLayout">
      <Sidebar />
      <main class="flex-1 ml-64 min-h-screen bg-slate-950 text-slate-100 overflow-x-hidden">

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
