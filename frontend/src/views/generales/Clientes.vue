<template>
  <div class="p-4 sm:p-8 max-w-7xl mx-auto space-y-8 text-slate-100 font-sans">
    <!-- Encabezado -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h2 class="text-3xl font-extrabold tracking-tight text-white">Gestión de Clientes</h2>
        <p class="text-slate-400 mt-1">Administra los datos de los clientes del sistema.</p>
      </div>
      <button
        v-if="canManage"
        @click="openAddModal"
        class="px-4 py-2.5 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-slate-950 rounded-xl text-sm font-bold shadow-lg hover:shadow-amber-500/20 transition-all duration-200 flex items-center gap-2"
      >
        <i class="fas fa-plus"></i>
        <span>Registrar Cliente</span>
      </button>
    </div>

    <!-- Spinner General de Carga -->
    <div v-if="loading && clientes.length === 0" class="flex flex-col items-center justify-center py-20 gap-3">
      <span class="animate-spin border-4 border-amber-500 border-t-transparent rounded-full w-12 h-12"></span>
      <p class="text-slate-500 font-semibold">Cargando catálogo de clientes...</p>
    </div>

    <div v-else-if="clientes.length === 0" class="glass-panel p-16 flex flex-col items-center text-center rounded-2xl">
      <div class="w-16 h-16 rounded-full bg-slate-900 border border-slate-800 flex items-center justify-center text-slate-500 text-2xl mb-4">
        <i class="fas fa-address-book"></i>
      </div>
      <h3 class="text-lg font-bold text-slate-300">No hay clientes registrados</h3>
      <p class="text-sm text-slate-500 max-w-sm mt-1">Aún no se han registrado clientes en la base de datos.</p>
    </div>

    <!-- Tabla de Clientes -->
    <div v-else class="glass-panel overflow-x-auto rounded-2xl">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="border-b border-white/5 text-slate-400 text-xs font-bold uppercase tracking-wider">
            <th class="p-4 pl-6">Documento (DNI/RUC)</th>
            <th class="p-4">Nombre y Apellido</th>
            <th class="p-4">Razón Social</th>
            <th class="p-4">Edad</th>
            <th class="p-4">Contacto</th>
            <th class="p-4">Dirección</th>
            <th v-if="canManage" class="p-4 pr-6 text-right">Acciones</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-white/5">
          <tr v-for="c in clientes" :key="c.id" class="hover:bg-slate-900/20 transition-colors duration-200 text-sm">
            <td class="p-4 pl-6 font-mono text-slate-300 font-bold">{{ c.documento }}</td>
            <td class="p-4 text-slate-200 font-semibold">{{ c.nombre }} {{ c.apellido }}</td>
            <td class="p-4 text-slate-300">{{ c.razon_social || '-' }}</td>
            <td class="p-4 text-slate-400">{{ c.edad || '-' }}</td>
            <td class="p-4 text-slate-300">
              <div class="flex flex-col gap-0.5">
                <span v-if="c.email" class="text-xs flex items-center gap-1.5 text-slate-400">
                  <i class="fas fa-envelope text-[10px] text-slate-600"></i>
                  {{ c.email }}
                </span>
                <span v-if="c.telefono" class="text-xs flex items-center gap-1.5 text-slate-400">
                  <i class="fas fa-phone text-[10px] text-slate-600"></i>
                  {{ c.telefono }}
                </span>
              </div>
            </td>
            <td class="p-4 text-slate-400 max-w-xs truncate">{{ c.direccion || '-' }}</td>
            <td v-if="canManage" class="p-4 pr-6 text-right space-x-2">
              <button
                @click="openEditModal(c)"
                v-title="'Editar cliente'"
                class="p-2 bg-slate-900/20 border border-white/5 hover:border-sky-500/30 text-slate-400 hover:text-sky-400 rounded-xl transition-all duration-200"
              >
                <i class="fas fa-edit text-xs"></i>
              </button>
              <button
                @click="handleDelete(c.id)"
                v-title.right="'Eliminar cliente'"
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
            {{ isEditing ? 'Editar Cliente' : 'Registrar Cliente' }}
          </h3>
          <button @click="closeFormModal" class="text-slate-400 hover:text-slate-200">
            <i class="fas fa-times text-lg"></i>
          </button>
        </div>

        <form @submit.prevent="saveCliente" class="space-y-4">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-semibold text-slate-400 mb-1 uppercase tracking-wider">Nombre</label>
              <input v-model="form.nombre" type="text" required class="w-full p-2.5 bg-slate-900/20 border border-white/5 rounded-xl text-slate-200 text-sm focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500/30 transition-all duration-300" />
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-400 mb-1 uppercase tracking-wider">Apellido</label>
              <input v-model="form.apellido" type="text" required class="w-full p-2.5 bg-slate-900/20 border border-white/5 rounded-xl text-slate-200 text-sm focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500/30 transition-all duration-300" />
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-semibold text-slate-400 mb-1 uppercase tracking-wider">DNI / RUC</label>
              <input v-model="form.documento" type="text" required class="w-full p-2.5 bg-slate-900/20 border border-white/5 rounded-xl text-slate-200 text-sm focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500/30 transition-all duration-300" />
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-400 mb-1 uppercase tracking-wider">Edad</label>
              <input v-model="form.edad" type="number" min="18" class="w-full p-2.5 bg-slate-900/20 border border-white/5 rounded-xl text-slate-200 text-sm focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500/30 transition-all duration-300" />
            </div>
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-400 mb-1 uppercase tracking-wider">Razón Social</label>
            <input v-model="form.razon_social" type="text" placeholder="Solo para personas jurídicas" class="w-full p-2.5 bg-slate-900/20 border border-white/5 rounded-xl text-slate-200 text-sm focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500/30 transition-all duration-300" />
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-semibold text-slate-400 mb-1 uppercase tracking-wider">Email</label>
              <input v-model="form.email" type="email" class="w-full p-2.5 bg-slate-900/20 border border-white/5 rounded-xl text-slate-200 text-sm focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500/30 transition-all duration-300" />
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-400 mb-1 uppercase tracking-wider">Teléfono</label>
              <input v-model="form.telefono" type="text" class="w-full p-2.5 bg-slate-900/20 border border-white/5 rounded-xl text-slate-200 text-sm focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500/30 transition-all duration-300" />
            </div>
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-400 mb-1 uppercase tracking-wider">Dirección</label>
            <input v-model="form.direccion" type="text" class="w-full p-2.5 bg-slate-900/20 border border-white/5 rounded-xl text-slate-200 text-sm focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500/30 transition-all duration-300" />
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

export default {
  name: 'Clientes',
  setup() {
    const authStore = useAuthStore();
    const notification = useNotification();

    const clientes = ref([]);
    const loading = ref(false);

    // Modal Form
    const showFormModal = ref(false);
    const isEditing = ref(false);
    const form = ref({
      id: null,
      nombre: '',
      apellido: '',
      documento: '',
      edad: '',
      razon_social: '',
      email: '',
      telefono: '',
      direccion: ''
    });

    const canManage = computed(() => authStore.hasPermission('gestionar_clientes'));

    const loadClientes = async () => {
      loading.value = true;
      try {
        clientes.value = await generalesService.getClientes();
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
        apellido: '',
        documento: '',
        edad: '',
        razon_social: '',
        email: '',
        telefono: '',
        direccion: ''
      };
      showFormModal.value = true;
    };

    const openEditModal = (c) => {
      isEditing.value = true;
      form.value = { ...c };
      showFormModal.value = true;
    };

    const closeFormModal = () => {
      showFormModal.value = false;
    };

    const saveCliente = async () => {
      try {
        if (isEditing.value) {
          await generalesService.updateCliente(form.value.id, form.value);
          notification.showSuccess('Cliente actualizado exitosamente.');
        } else {
          await generalesService.createCliente(form.value);
          notification.showSuccess('Cliente registrado exitosamente.');
        }
        showFormModal.value = false;
        loadClientes();
      } catch (err) {
        notification.showError(err);
      }
    };

    const handleDelete = async (id) => {
      if (confirm('¿Estás seguro de que deseas eliminar este cliente? (Se aplicará soft delete)')) {
        try {
          await generalesService.deleteCliente(id);
          notification.showSuccess('Cliente eliminado exitosamente.');
          loadClientes();
        } catch (err) {
          notification.showError(err);
        }
      }
    };

    onMounted(() => {
      loadClientes();
    });

    return {
      clientes,
      loading,
      canManage,
      showFormModal,
      isEditing,
      form,
      openAddModal,
      openEditModal,
      closeFormModal,
      saveCliente,
      handleDelete
    };
  }
};
</script>
