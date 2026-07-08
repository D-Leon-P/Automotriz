<template>
  <div class="fixed top-4 right-4 z-50 flex flex-col gap-2 max-w-md w-full">
    <transition-group name="notification">
      <div
        v-for="n in notifications"
        :key="n.id"
        :class="[
          'p-4 rounded-lg shadow-lg border flex items-center justify-between text-sm transition-all duration-300 transform',
          n.type === 'success' && 'bg-slate-900 border-green-500/50 text-green-400',
          n.type === 'error' && 'bg-slate-900 border-red-500/50 text-red-400',
          n.type === 'warning' && 'bg-slate-900 border-yellow-500/50 text-yellow-400',
          n.type === 'info' && 'bg-slate-900 border-blue-500/50 text-blue-400',
        ]"
      >
        <div class="flex items-center gap-3">
          <!-- Icono de FontAwesome -->
          <i v-if="n.type === 'success'" class="fas fa-check-circle text-lg"></i>
          <i v-else-if="n.type === 'error'" class="fas fa-times-circle text-lg"></i>
          <i v-else-if="n.type === 'warning'" class="fas fa-exclamation-triangle text-lg"></i>
          <i v-else class="fas fa-info-circle text-lg"></i>
          
          <span>{{ n.message }}</span>
        </div>
        <button @click="remove(n.id)" class="text-slate-400 hover:text-slate-200 ml-4">
          <i class="fas fa-times"></i>
        </button>
      </div>
    </transition-group>
  </div>
</template>

<script>
import { useNotification } from '../composables/useNotification';

export default {
  setup() {
    const { notifications, remove } = useNotification();
    return { notifications, remove };
  },
};
</script>

<style scoped>
.notification-enter-from {
  opacity: 0;
  transform: translateY(-20px) scale(0.9);
}
.notification-leave-to {
  opacity: 0;
  transform: translateX(100px);
}
</style>
