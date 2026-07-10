<template>
  <div class="p-4 sm:p-8 max-w-7xl mx-auto space-y-8 text-slate-100 font-sans">
    <!-- Encabezado -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h2 class="text-3xl font-extrabold tracking-tight text-white">Gestión de Colaboradores</h2>
        <p class="text-slate-400 mt-1">Administra los usuarios y personal con acceso al sistema.</p>
      </div>
      <button
        v-if="canManage"
        @click="openAddModal"
        class="px-4 py-2.5 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-slate-950 rounded-xl text-sm font-bold shadow-lg hover:shadow-amber-500/20 transition-all duration-200 flex items-center gap-2"
      >
        <i class="fas fa-plus"></i>
        <span>Registrar Colaborador</span>
      </button>
    </div>

    <!-- Spinner General de Carga -->
    <div v-if="loading && empleados.length === 0" class="flex flex-col items-center justify-center py-20 gap-3">
      <span class="animate-spin border-4 border-amber-500 border-t-transparent rounded-full w-12 h-12"></span>
      <p class="text-slate-500 font-semibold">Cargando personal del sistema...</p>
    </div>

    <div v-else-if="empleados.length === 0" class="glass-panel p-16 flex flex-col items-center text-center rounded-2xl">
      <div class="w-16 h-16 rounded-full bg-slate-900 border border-slate-800 flex items-center justify-center text-slate-500 text-2xl mb-4">
        <i class="fas fa-user-tie"></i>
      </div>
      <h3 class="text-lg font-bold text-slate-300">No hay colaboradores registrados</h3>
      <p class="text-sm text-slate-500 max-w-sm mt-1">Aún no se han registrado colaboradores en la base de datos.</p>
    </div>

    <!-- Tabla de Empleados -->
    <div v-else class="glass-panel overflow-x-auto rounded-2xl">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="border-b border-white/5 text-slate-400 text-xs font-bold uppercase tracking-wider">
            <th class="p-4 pl-6">ID</th>
            <th class="p-4">Nombre Completo</th>
            <th class="p-4">Email / Usuario</th>
            <th class="p-4">Rol Asignado</th>
            <th class="p-4">Fecha de Registro</th>
            <th v-if="canManage" class="p-4 pr-6 text-right">Acciones</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-white/5">
          <tr v-for="e in empleados" :key="e.id" class="hover:bg-slate-900/20 transition-colors duration-200 text-sm">
            <td class="p-4 pl-6 font-mono text-slate-400">#{{ e.id }}</td>
            <td class="p-4 text-slate-200 font-bold flex items-center gap-3">
              <div class="w-8 h-8 rounded-full bg-slate-900 border border-white/5 flex items-center justify-center text-amber-500 font-bold text-xs">
                {{ e.nombre.charAt(0).toUpperCase() }}
              </div>
              <span>{{ e.nombre }}</span>
            </td>
            <td class="p-4 text-slate-300">{{ e.email }}</td>
            <td class="p-4">
              <span
                :class="[
                  'px-3 py-1 rounded-full text-xs font-bold capitalize border inline-block',
                  e.rol?.nombre === 'administrador' ? 'bg-red-500/10 border-red-500/20 text-red-400' : 'bg-blue-500/10 border-blue-500/20 text-blue-400'
                ]"
              >
                {{ e.rol?.nombre || 'Ninguno' }}
              </span>
            </td>
            <td class="p-4 text-slate-400">{{ formatDate(e.created_at) }}</td>
            <td v-if="canManage" class="p-4 pr-6 text-right space-x-2">
              <button
                @click="openEditModal(e)"
                v-title="'Editar datos'"
                class="p-2 bg-slate-900/20 border border-white/5 hover:border-sky-500/30 text-slate-400 hover:text-sky-400 rounded-xl transition-all duration-200"
              >
                <i class="fas fa-edit text-xs"></i>
              </button>
              <button
                v-if="currentUser.id !== e.id"
                @click="handleDelete(e.id)"
                v-title.right="'Eliminar colaborador'"
                class="p-2 bg-slate-900/20 border border-white/5 hover:border-red-500/30 text-slate-400 hover:text-red-400 rounded-xl transition-all duration-200"
              >
                <i class="fas fa-trash-alt text-xs"></i>
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal Formulario -->
    <div v-if="showFormModal" class="fixed inset-0 z-50 flex items-center justify-center px-4 bg-slate-950/80 backdrop-blur-sm">
      <div class="w-full max-w-lg glass-panel p-6 sm:p-8 rounded-2xl space-y-6">
        <div class="flex justify-between items-center pb-4 border-b border-white/5">
          <h3 class="text-xl font-bold text-white">
            {{ isEditing ? 'Editar Colaborador' : 'Registrar Colaborador' }}
          </h3>
          <button @click="closeFormModal" class="text-slate-400 hover:text-slate-200">
            <i class="fas fa-times text-lg"></i>
          </button>
        </div>

        <form @submit.prevent="saveEmpleado" class="space-y-4">
          <div>
            <label class="block text-xs font-semibold text-slate-400 mb-1 uppercase tracking-wider">Nombre Completo</label>
            <input v-model="form.nombre" type="text" required class="w-full p-2.5 bg-slate-900/20 border border-white/5 rounded-xl text-slate-200 text-sm focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500/30 transition-all duration-300" />
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-400 mb-1 uppercase tracking-wider">Email (Usuario)</label>
            <input v-model="form.email" type="email" required class="w-full p-2.5 bg-slate-900/20 border border-white/5 rounded-xl text-slate-200 text-sm focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500/30 transition-all duration-300" />
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-400 mb-1 uppercase tracking-wider">
              Contraseña {{ isEditing ? '(dejar vacío para mantener actual)' : '' }}
            </label>
            <input v-model="form.password" type="password" :required="!isEditing" class="w-full p-2.5 bg-slate-900/20 border border-white/5 rounded-xl text-slate-200 text-sm focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500/30 transition-all duration-300" />
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-400 mb-1 uppercase tracking-wider">Rol de Sistema</label>
            <CustomSelect
              v-model="form.rol_id"
              :options="rolOptions"
              placeholder="Seleccionar rol..."
              :disabled="isEditing && currentUser.id === form.id && currentUser.rol?.nombre === 'administrador'"
            />
            <p v-if="isEditing && currentUser.id === form.id" class="text-xxs text-slate-500 mt-1">No puedes cambiar tu propio rol de administrador.</p>
          </div>

          <div class="flex justify-end gap-3 pt-4 border-t border-white/5">
            <button type="button" @click="closeFormModal" class="px-4 py-2 bg-slate-900/20 border border-white/5 hover:border-slate-800 text-slate-400 hover:text-slate-200 rounded-xl text-sm font-semibold transition-all">
              Cancelar
            </button>
            <button type="submit" class="px-4 py-2 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-slate-950 rounded-xl text-sm font-bold shadow-lg hover:shadow-amber-500/20 transition-all duration-200">
              {{ isEditing ? 'Guardar Cambios' : 'Registrar' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import { useAuthStore } from '../../stores/auth';
import { generalesService } from '../../services/generalesService';
import { useNotification } from '../../composables/useNotification';
import CustomSelect from '../../components/CustomSelect.vue';

export default {
  name: 'Empleados',
  components: {
    CustomSelect
  },
  setup() {
    const authStore = useAuthStore();
    const notification = useNotification();

    const empleados = ref([]);
    const roles = ref([]);
    const loading = ref(false);

    // Modal Form
    const showFormModal = ref(false);
    const isEditing = ref(false);
    const form = ref({
      id: null,
      nombre: '',
      email: '',
      password: '',
      rol_id: ''
    });

    const currentUser = computed(() => authStore.user);
    const canManage = computed(() => authStore.hasPermission('gestionar_empleados'));

    const rolOptions = computed(() => {
      return roles.value.map(r => ({
        value: r.id,
        label: r.nombre.charAt(0).toUpperCase() + r.nombre.slice(1)
      }));
    });

    const loadData = async () => {
      loading.value = true;
      try {
        const [empData, rolData] = await Promise.all([
          generalesService.getEmpleados(),
          generalesService.getRoles()
        ]);
        empleados.value = empData;
        roles.value = rolData;
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
        email: '',
        password: '',
        rol_id: ''
      };
      showFormModal.value = true;
    };

    const openEditModal = (e) => {
      isEditing.value = true;
      form.value = {
        id: e.id,
        nombre: e.nombre,
        email: e.email,
        password: '',
        rol_id: e.rol_id
      };
      showFormModal.value = true;
    };

    const closeFormModal = () => {
      showFormModal.value = false;
    };

    const saveEmpleado = async () => {
      try {
        if (isEditing.value) {
          await generalesService.updateEmpleado(form.value.id, form.value);
          notification.showSuccess('Colaborador actualizado exitosamente.');
        } else {
          await generalesService.createEmpleado(form.value);
          notification.showSuccess('Colaborador registrado exitosamente.');
        }
        showFormModal.value = false;
        loadData();
      } catch (err) {
        notification.showError(err);
      }
    };

    const handleDelete = async (id) => {
      if (confirm('¿Estás seguro de que deseas eliminar este colaborador? (Se aplicará soft delete)')) {
        try {
          await generalesService.deleteEmpleado(id);
          notification.showSuccess('Colaborador eliminado exitosamente.');
          loadData();
        } catch (err) {
          notification.showError(err);
        }
      }
    };

    const formatDate = (dateStr) => {
      if (!dateStr) return '-';
      const d = new Date(dateStr);
      return d.toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
      });
    };

    onMounted(() => {
      loadData();
    });

    return {
      empleados,
      loading,
      currentUser,
      canManage,
      rolOptions,
      showFormModal,
      isEditing,
      form,
      openAddModal,
      openEditModal,
      closeFormModal,
      saveEmpleado,
      handleDelete,
      formatDate
    };
  }
};
</script>
