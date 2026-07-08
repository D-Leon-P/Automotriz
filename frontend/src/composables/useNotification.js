import { ref } from 'vue';

const notifications = ref([]);

export function useNotification() {
  const add = (message, type = 'success', duration = 3000) => {
    const id = Date.now() + Math.random().toString(36).substr(2, 5);
    notifications.value.push({ id, message, type });

    setTimeout(() => {
      remove(id);
    }, duration);
  };

  const remove = (id) => {
    notifications.value = notifications.value.filter((n) => n.id !== id);
  };

  return {
    notifications,
    showSuccess: (msg) => add(msg, 'success'),
    showError: (msg) => add(msg, 'error'),
    showWarning: (msg) => add(msg, 'warning'),
    showInfo: (msg) => add(msg, 'info'),
    remove,
  };
}
