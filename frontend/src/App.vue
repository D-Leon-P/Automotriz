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
import { computed, watch, onUnmounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import Sidebar from './components/Sidebar.vue';
import NotificationContainer from './components/NotificationContainer.vue';
import { useAuthStore } from './stores/auth';
import { useDashboardStore } from './stores/dashboard';
import { useNotification } from './composables/useNotification';

export default {
  components: {
    Sidebar,
    NotificationContainer
  },
  setup() {
    const route = useRoute();
    const router = useRouter();
    const authStore = useAuthStore();
    const dashboardStore = useDashboardStore();
    const notification = useNotification();
    let socket = null;
    let reconnectTimeout = null;

    // Determinar si la ruta actual requiere el layout principal con Sidebar
    const requiresLayout = computed(() => {
      return route.meta?.requiresAuth === true;
    });

    const checkRoutePermissions = () => {
      const requiredPermission = route.meta?.permission;
      if (requiredPermission && !authStore.hasPermission(requiredPermission)) {
        notification.showWarning('Tus privilegios de acceso han cambiado. Redireccionando...');
        router.push('/');
      }
    };

    const connectWebSocket = () => {
      if (socket) return;

      const protocol = window.location.protocol === 'https:' ? 'wss:' : 'ws:';
      const wsUrl = `${protocol}//${window.location.host}/ws`;
      console.log('Intentando conectar a WebSocket en:', wsUrl);
      socket = new WebSocket(wsUrl);

      socket.onopen = () => {
        console.log('Conexión WebSocket establecida exitosamente.');
        if (reconnectTimeout) {
          clearTimeout(reconnectTimeout);
          reconnectTimeout = null;
        }
      };

      socket.onmessage = (event) => {
        try {
          const message = JSON.parse(event.data);
          console.log('Evento WebSocket global recibido:', message.event, message.data);

          if (message.event === 'dashboard.refresh') {
            if (authStore.isAuthenticated) {
              dashboardStore.fetchMetrics().catch(() => {});
            }
          } else if (message.event === 'permissions.updated') {
            const data = message.data || {};
            console.log('permissions.updated recibido. Rol usuario:', authStore.user?.rol_id, 'Rol evento:', data.rol_id);
            // Si el rol actualizado coincide con el del usuario actual, refrescar perfil
            if (authStore.user && Number(authStore.user.rol_id) === Number(data.rol_id)) {
              notification.showInfo('Tus permisos de rol han sido actualizados en tiempo real.');
              authStore.fetchProfile().then(() => {
                checkRoutePermissions();
              });
            }
          } else if (message.event === 'employee.updated') {
            const data = message.data || {};
            console.log('employee.updated recibido. Empleado usuario:', authStore.user?.id, 'Empleado evento:', data.empleado_id);
            // Si coincide con nuestro id de empleado, refrescar perfil
            if (authStore.user && Number(authStore.user.id) === Number(data.empleado_id)) {
              notification.showInfo('Tu cuenta de colaborador ha sido actualizada.');
              authStore.fetchProfile().then(() => {
                checkRoutePermissions();
              });
            }
          }
        } catch (e) {
          console.error('Error al procesar mensaje de WebSocket:', e);
        }
      };

      socket.onclose = () => {
        socket = null;
        console.warn('Conexión de WebSocket global cerrada.');
        // Reintentar conexión si sigue autenticado
        if (authStore.isAuthenticated) {
          console.log('Reconectando a WebSocket en 5 segundos...');
          reconnectTimeout = setTimeout(() => {
            connectWebSocket();
          }, 5000);
        }
      };
    };

    const disconnectWebSocket = () => {
      if (reconnectTimeout) {
        clearTimeout(reconnectTimeout);
        reconnectTimeout = null;
      }
      if (socket) {
        socket.close();
        socket = null;
      }
    };

    // Conectar/desconectar en base a la sesión
    watch(() => authStore.isAuthenticated, (isAuth) => {
      if (isAuth) {
        connectWebSocket();
      } else {
        disconnectWebSocket();
      }
    }, { immediate: true });

    onUnmounted(() => {
      disconnectWebSocket();
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
