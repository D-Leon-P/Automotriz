<template>
  <div class="p-4 sm:p-8 max-w-7xl mx-auto space-y-8 text-slate-100 font-sans">
    <!-- Encabezado -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h2 class="text-3xl font-extrabold tracking-tight text-white">Roles y Permisos (RBAC)</h2>
        <p class="text-slate-400 mt-1">Configura los roles del sistema y asigna privilegios de vistas u operaciones.</p>
      </div>
      <button
        v-if="canManage"
        @click="openAddModal"
        class="px-4 py-2.5 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-slate-950 rounded-xl text-sm font-bold shadow-lg hover:shadow-amber-500/20 transition-all duration-200 flex items-center gap-2"
      >
        <i class="fas fa-plus"></i>
        <span>Crear Rol</span>
      </button>
    </div>

    <!-- Spinner General de Carga -->
    <div v-if="loading && roles.length === 0" class="flex flex-col items-center justify-center py-20 gap-3">
      <span class="animate-spin border-4 border-amber-500 border-t-transparent rounded-full w-12 h-12"></span>
      <p class="text-slate-500 font-semibold">Cargando roles y privilegios...</p>
    </div>

    <!-- Tabla de Roles -->
    <div v-else class="glass-panel overflow-x-auto rounded-2xl">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="border-b border-white/5 text-slate-400 text-xs font-bold uppercase tracking-wider">
            <th class="p-4 pl-6">ID</th>
            <th class="p-4">Nombre de Rol</th>
            <th class="p-4">Permisos Asignados</th>
            <th v-if="canManage" class="p-4 pr-6 text-right">Acciones</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-white/5">
          <tr v-for="r in roles" :key="r.id" class="hover:bg-slate-900/20 transition-colors duration-200 text-sm">
            <td class="p-4 pl-6 font-mono text-slate-400">#{{ r.id }}</td>
            <td class="p-4 text-slate-200 font-bold capitalize">
              {{ r.nombre }}
            </td>
            <td class="p-4 text-slate-300">
              <div class="flex flex-wrap gap-1.5 max-w-xl">
                <span
                  v-for="p in r.permisos"
                  :key="p.id"
                  class="px-2 py-0.5 bg-slate-900 border border-white/5 rounded text-slate-400 text-xxs lowercase"
                >
                  {{ p.nombre }}
                </span>
                <span v-if="r.permisos.length === 0" class="text-xs text-slate-500 font-medium">Ningún permiso asociado</span>
              </div>
            </td>
            <td v-if="canManage" class="p-4 pr-6 text-right space-x-2">
              <button
                v-if="r.nombre !== 'administrador'"
                @click="openEditModal(r)"
                v-title="'Editar permisos'"
                class="p-2 bg-slate-900/20 border border-white/5 hover:border-sky-500/30 text-slate-400 hover:text-sky-400 rounded-xl transition-all duration-200"
              >
                <i class="fas fa-edit text-xs"></i>
              </button>
              <button
                v-if="r.nombre !== 'administrador' && r.nombre !== 'vendedor'"
                @click="handleDelete(r.id)"
                v-title.right="'Eliminar rol'"
                class="p-2 bg-slate-900/20 border border-white/5 hover:border-red-500/30 text-slate-400 hover:text-red-400 rounded-xl transition-all duration-200"
              >
                <i class="fas fa-trash-alt text-xs"></i>
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal Formulario de Roles y Permisos -->
    <teleport to="body">
      <div v-if="showFormModal" class="fixed inset-0 z-50 flex items-center justify-center px-4 bg-slate-950/80 backdrop-blur-sm">
        <div class="w-full max-w-2xl glass-panel p-6 sm:p-8 rounded-2xl space-y-6">
        <div class="flex justify-between items-center pb-4 border-b border-white/5">
          <h3 class="text-xl font-bold text-white">
            {{ isEditing ? 'Editar Rol y Permisos' : 'Crear Rol' }}
          </h3>
          <button @click="closeFormModal" class="text-slate-400 hover:text-slate-200">
            <i class="fas fa-times text-lg"></i>
          </button>
        </div>

        <form @submit.prevent="saveRol" class="space-y-6">
          <div>
            <label class="block text-xs font-semibold text-slate-400 mb-1 uppercase tracking-wider">Nombre del Rol</label>
            <input
              v-model="form.nombre"
              type="text"
              required
              :disabled="isEditing"
              class="w-full p-2.5 bg-slate-900/20 border border-white/5 rounded-xl text-slate-200 text-sm focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500/30 transition-all duration-300 disabled:opacity-50"
            />
          </div>

          <!-- Cuadrícula de Selección de Permisos -->
          <div class="space-y-4">
            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Asignación de Permisos</label>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 max-h-96 overflow-y-auto pr-2 scrollbar-thin">
              <div
                v-for="(group, category) in groupedPermissions"
                :key="category"
                class="p-4 bg-slate-950/40 border border-white/5 rounded-xl space-y-3"
              >
                <h4 class="text-xs font-extrabold text-amber-500 uppercase tracking-wider border-b border-white/5 pb-1">
                  {{ category }}
                </h4>
                
                <div class="space-y-2">
                  <label
                    v-for="p in group"
                    :key="p.id"
                    class="flex items-start gap-2.5 text-xs text-slate-300 hover:text-slate-100 cursor-pointer select-none"
                  >
                    <input
                      type="checkbox"
                      :value="p.id"
                      v-model="form.permisos"
                      class="mt-0.5 rounded border-white/10 bg-slate-900 text-amber-500 focus:ring-0 focus:ring-offset-0"
                    />
                    <span>{{ formatPermissionName(p.nombre) }}</span>
                  </label>
                </div>
              </div>
            </div>
          </div>

          <div class="flex justify-end gap-3 pt-4 border-t border-white/5">
            <button type="button" @click="closeFormModal" class="px-4 py-2 bg-slate-900/20 border border-white/5 hover:border-slate-800 text-slate-400 hover:text-slate-200 rounded-xl text-sm font-semibold transition-all">
              Cancelar
            </button>
            <button type="submit" class="px-4 py-2 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-slate-950 rounded-xl text-sm font-bold shadow-lg hover:shadow-amber-500/20 transition-all duration-200">
              {{ isEditing ? 'Guardar Cambios' : 'Crear Rol' }}
            </button>
          </div>
        </form>
      </div>
    </div>
    </teleport>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import { useAuthStore } from '../../stores/auth';
import { generalesService } from '../../services/generalesService';
import { useNotification } from '../../composables/useNotification';

export default {
  name: 'Roles',
  setup() {
    const authStore = useAuthStore();
    const notification = useNotification();

    const roles = ref([]);
    const permisos = ref([]);
    const loading = ref(false);

    // Modal Form
    const showFormModal = ref(false);
    const isEditing = ref(false);
    const form = ref({
      id: null,
      nombre: '',
      permisos: []
    });

    const canManage = computed(() => authStore.hasPermission('gestionar_roles'));

    // Agrupar los permisos por categorías para mostrarlos en la UI de forma organizada
    const groupedPermissions = computed(() => {
      const groups = {
        'Prospectos': [],
        'Ventas': [],
        'Seguros': [],
        'Dashboard': [],
        'Generales': []
      };

      permisos.value.forEach(p => {
        if (p.nombre.includes('prospecto')) {
          groups['Prospectos'].push(p);
        } else if (p.nombre.includes('venta')) {
          groups['Ventas'].push(p);
        } else if (p.nombre.includes('seguro')) {
          groups['Seguros'].push(p);
        } else if (p.nombre.includes('dashboard')) {
          groups['Dashboard'].push(p);
        } else {
          groups['Generales'].push(p);
        }
      });

      return groups;
    });

    const loadData = async () => {
      loading.value = true;
      try {
        const [rolesData, permisosData] = await Promise.all([
          generalesService.getRoles(),
          generalesService.getPermisos()
        ]);
        roles.value = rolesData;
        permisos.value = permisosData;
      } catch (err) {
        notification.showError(err);
      } finally {
        loading.value = false;
      }
    };

    const openAddModal = () => {
      isEditing.value = false;
      form.value = {
        id: null,
        nombre: '',
        permisos: []
      };
      showFormModal.value = true;
    };

    const openEditModal = (r) => {
      isEditing.value = true;
      form.value = {
        id: r.id,
        nombre: r.nombre,
        permisos: r.permisos.map(p => p.id)
      };
      showFormModal.value = true;
    };

    const closeFormModal = () => {
      showFormModal.value = false;
    };

    const saveRol = async () => {
      try {
        if (isEditing.value) {
          await generalesService.updateRol(form.value.id, form.value);
          notification.showSuccess('Rol y privilegios actualizados exitosamente.');
        } else {
          await generalesService.createRol(form.value);
          notification.showSuccess('Rol registrado exitosamente.');
        }
        showFormModal.value = false;
        loadData();
      } catch (err) {
        notification.showError(err);
      }
    };

    const handleDelete = async (id) => {
      if (confirm('¿Estás seguro de que deseas eliminar este rol?')) {
        try {
          await generalesService.deleteRol(id);
          notification.showSuccess('Rol eliminado exitosamente.');
          loadData();
        } catch (err) {
          notification.showError(err);
        }
      }
    };

    const formatPermissionName = (name) => {
      return name
        .replace(/_/g, ' ')
        .replace('ver', 'Ver')
        .replace('gestionar', 'Gestionar')
        .replace('todos', 'todos')
        .replace('propios', 'propios')
        .replace('propias', 'propias')
        .replace('todas', 'todas');
    };

    onMounted(() => {
      loadData();
    });

    return {
      roles,
      permisos,
      loading,
      canManage,
      groupedPermissions,
      showFormModal,
      isEditing,
      form,
      openAddModal,
      openEditModal,
      closeFormModal,
      saveRol,
      handleDelete,
      formatPermissionName
    };
  }
};
</script>

<style scoped>
/* Custom Webkit scrollbar for list containers */
.scrollbar-thin::-webkit-scrollbar {
  width: 5px;
}
.scrollbar-thin::-webkit-scrollbar-track {
  background: transparent;
}
.scrollbar-thin::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.1);
  border-radius: 9999px;
}
.scrollbar-thin::-webkit-scrollbar-thumb:hover {
  background: rgba(255, 255, 255, 0.2);
}
</style>
