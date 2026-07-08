<template>
  <div class="p-8 max-w-7xl mx-auto space-y-8">
    <!-- Encabezado -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h2 class="text-3xl font-extrabold tracking-tight text-slate-100">Gestión de Prospectos</h2>
        <p class="text-slate-400 mt-1">Registra y califica potenciales clientes a través de tu embudo comercial.</p>
      </div>
      <button
        @click="openAddModal"
        class="self-start sm:self-auto px-4 py-2.5 bg-gradient-to-r from-brand to-brand-light hover:from-brand-dark hover:to-brand text-slate-950 font-extrabold rounded-lg shadow-lg hover:shadow-brand/20 transition-all flex items-center gap-2"
      >
        <i class="fas fa-user-plus"></i>
        <span>Registrar Prospecto</span>
      </button>
    </div>

    <!-- Filtros por Etapa -->
    <div class="flex flex-wrap gap-2 pb-2">
      <button
        v-for="filter in filters"
        :key="filter.value"
        @click="activeFilter = filter.value"
        :class="[
          'px-4 py-2 rounded-full text-xs font-bold uppercase tracking-wider border transition-all',
          activeFilter === filter.value
            ? 'bg-brand/10 border-brand text-brand-light font-black shadow'
            : 'bg-slate-900 border-slate-800 text-slate-400 hover:border-slate-700 hover:text-slate-200'
        ]"
      >
        {{ filter.label }}
      </button>
    </div>

    <!-- Spinner / Vacío / Tabla -->
    <div v-if="loading" class="flex flex-col items-center justify-center py-20 gap-3">
      <span class="animate-spin border-4 border-brand border-t-transparent rounded-full w-12 h-12"></span>
      <p class="text-slate-500 font-semibold">Cargando prospectos...</p>
    </div>

    <div v-else-if="filteredProspectos.length === 0" class="glass-panel p-16 border-slate-800 flex flex-col items-center text-center">
      <div class="w-16 h-16 rounded-full bg-slate-800/80 flex items-center justify-center text-slate-500 text-2xl mb-4">
        <i class="fas fa-users-slash"></i>
      </div>
      <h3 class="text-lg font-bold text-slate-300">No hay prospectos registrados</h3>
      <p class="text-sm text-slate-500 max-w-sm mt-1">Registra tu primer prospecto o selecciona otra etapa de filtrado para ver la lista.</p>
    </div>

    <!-- Tabla de Prospectos -->
    <div v-else class="glass-panel overflow-x-auto border-slate-800">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="bg-slate-950 border-b border-slate-800 text-slate-400 uppercase text-xs font-bold tracking-wider">
            <th class="p-4 pl-6">Cliente</th>
            <th class="p-4">Contacto</th>
            <th class="p-4">Vehículo de Interés</th>
            <th class="p-4">Asesor</th>
            <th class="p-4">Etapa</th>
            <th class="p-4 pr-6 text-right">Acciones</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-800/60">
          <tr v-for="p in filteredProspectos" :key="p.id" class="hover:bg-slate-900/40 transition-colors text-sm">
            <td class="p-4 pl-6 font-bold text-slate-200">{{ p.nombre }}</td>
            <td class="p-4 text-slate-300">
              <div class="flex flex-col">
                <span class="flex items-center gap-1.5"><i class="fas fa-envelope text-slate-500 text-xs"></i> {{ p.email }}</span>
                <span class="text-xs text-slate-500 mt-0.5 flex items-center gap-1.5"><i class="fas fa-phone text-slate-500 text-xxs"></i> {{ p.telefono || 'N/A' }}</span>
              </div>
            </td>
            <td class="p-4 text-slate-300">
              <span v-if="p.vehiculo" class="font-semibold text-slate-300">
                {{ p.vehiculo.marca }} {{ p.vehiculo.modelo }} <span class="text-slate-500 text-xs">({{ p.vehiculo.anio }})</span>
              </span>
              <span v-else class="text-slate-500">No asignado</span>
            </td>
            <td class="p-4 text-slate-400 font-medium">
              {{ p.vendedor ? p.vendedor.nombre : 'No asignado' }}
            </td>
            <td class="p-4">
              <span
                :class="[
                  'px-3 py-1 rounded-full text-xs font-bold capitalize border inline-block',
                  p.etapa === 'prospeccion' && 'bg-blue-500/10 border-blue-500/20 text-blue-400',
                  p.etapa === 'calificacion' && 'bg-purple-500/10 border-purple-500/20 text-purple-400',
                  p.etapa === 'negociacion' && 'bg-yellow-500/10 border-yellow-500/20 text-yellow-400',
                  p.etapa === 'cierre' && 'bg-green-500/10 border-green-500/20 text-green-400',
                ]"
              >
                {{ formatEtapa(p.etapa) }}
              </span>
            </td>
            <td class="p-4 pr-6 text-right space-x-2">
              <!-- Botón Avanzar Etapa -->
              <button
                v-if="p.etapa !== 'cierre'"
                @click="openAdvanceModal(p)"
                v-title="'Avanzar/Editar etapa'"
                class="p-2 bg-slate-900 border border-slate-800 hover:border-brand/40 text-slate-400 hover:text-brand-light rounded-lg transition-all"
              >
                <i class="fas fa-step-forward text-xs"></i>
              </button>
              
              <!-- Botón Editar -->
              <button
                @click="openEditModal(p)"
                class="p-2 bg-slate-900 border border-slate-800 hover:border-brand/40 text-slate-400 hover:text-brand-light rounded-lg transition-all"
              >
                <i class="fas fa-edit text-xs"></i>
              </button>

              <!-- Botón Eliminar -->
              <button
                @click="handleDelete(p.id)"
                class="p-2 bg-slate-900 border border-slate-800 hover:border-red-500/40 text-slate-400 hover:text-red-400 rounded-lg transition-all"
              >
                <i class="fas fa-trash-alt text-xs"></i>
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal Registrar/Editar Prospecto -->
    <div v-if="showFormModal" class="fixed inset-0 z-50 flex items-center justify-center px-4 bg-slate-950/80 backdrop-blur-sm">
      <div class="w-full max-w-lg glass-panel p-6 border-slate-800 space-y-6">
        <div class="flex justify-between items-center">
          <h3 class="text-xl font-extrabold text-slate-200">
            {{ isEditing ? 'Editar Prospecto' : 'Registrar Prospecto' }}
          </h3>
          <button @click="closeFormModal" class="text-slate-400 hover:text-slate-200">
            <i class="fas fa-times text-lg"></i>
          </button>
        </div>

        <form @submit.prevent="saveProspecto" class="space-y-4">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Nombre Completo</label>
              <input v-model="form.nombre" type="text" required class="w-full p-2.5 bg-slate-850 border border-slate-800 rounded-lg text-slate-200 placeholder-slate-500 text-sm focus:outline-none focus:border-brand" />
            </div>
            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Email</label>
              <input v-model="form.email" type="email" required class="w-full p-2.5 bg-slate-850 border border-slate-800 rounded-lg text-slate-200 placeholder-slate-500 text-sm focus:outline-none focus:border-brand" />
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Teléfono</label>
              <input v-model="form.telefono" type="text" class="w-full p-2.5 bg-slate-850 border border-slate-800 rounded-lg text-slate-200 placeholder-slate-500 text-sm focus:outline-none focus:border-brand" />
            </div>
            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Vehículo de Interés</label>
              <select v-model="form.vehiculo_id" required class="w-full p-2.5 bg-slate-850 border border-slate-800 rounded-lg text-slate-200 text-sm focus:outline-none focus:border-brand">
                <option value="" disabled>Selecciona un auto</option>
                <option v-for="v in vehiculos" :key="v.id" :value="v.id">
                  {{ v.marca }} {{ v.modelo }} ({{ v.anio }}) - ${{ v.precio }}
                </option>
              </select>
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Asesor Comercial</label>
              <!-- En un entorno real esto puede cargarse dinámicamente. De momento autocompletamos con el vendedor autenticado -->
              <input type="text" readonly :value="currentUser.nombre" class="w-full p-2.5 bg-slate-900 border border-slate-800 rounded-lg text-slate-500 text-sm focus:outline-none" />
            </div>
            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Etapa Inicial</label>
              <select v-model="form.etapa" class="w-full p-2.5 bg-slate-850 border border-slate-800 rounded-lg text-slate-200 text-sm focus:outline-none focus:border-brand">
                <option value="prospeccion">Prospección Inicial</option>
                <option value="calificacion">Calificación</option>
                <option value="negociacion">Negociación</option>
                <option value="cierre">Cierre</option>
              </select>
            </div>
          </div>

          <div class="flex justify-end gap-3 pt-4 border-t border-slate-800">
            <button type="button" @click="closeFormModal" class="px-4 py-2 bg-slate-900 border border-slate-800 hover:border-slate-700 text-slate-400 hover:text-slate-200 rounded-lg text-sm font-semibold">
              Cancelar
            </button>
            <button type="submit" class="px-4 py-2 bg-brand hover:bg-brand-light text-slate-950 rounded-lg text-sm font-extrabold shadow">
              {{ isEditing ? 'Guardar Cambios' : 'Registrar' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal Avanzar Etapa -->
    <div v-if="showAdvanceModal" class="fixed inset-0 z-50 flex items-center justify-center px-4 bg-slate-950/80 backdrop-blur-sm">
      <div class="w-full max-w-sm glass-panel p-6 border-slate-800 space-y-6">
        <div class="flex justify-between items-center">
          <h3 class="text-xl font-extrabold text-slate-200">Actualizar Etapa</h3>
          <button @click="closeAdvanceModal" class="text-slate-400 hover:text-slate-200">
            <i class="fas fa-times text-lg"></i>
          </button>
        </div>

        <div class="space-y-4">
          <div>
            <p class="text-sm text-slate-400">Selecciona la nueva etapa para el prospecto <span class="font-bold text-slate-200">{{ selectedProspecto.nombre }}</span>:</p>
            
            <div class="mt-4 space-y-2">
              <button
                v-for="stage in ['prospeccion', 'calificacion', 'negociacion']"
                :key="stage"
                @click="updateStage(stage)"
                class="w-full p-3 bg-slate-850 hover:bg-slate-800 border border-slate-850 hover:border-brand/40 text-left rounded-lg text-sm font-semibold text-slate-300 transition-all flex items-center justify-between"
              >
                <span>{{ formatEtapa(stage) }}</span>
                <i class="fas fa-chevron-right text-slate-500"></i>
              </button>

              <!-- El Cierre (Venta Efectiva/Venta Fallida) redirige a la pestaña de ventas, ya que debe registrarse formalmente -->
              <router-link
                to="/ventas"
                class="w-full p-3 bg-slate-850 hover:bg-slate-800 border border-slate-850 hover:border-green-500/30 text-left rounded-lg text-sm font-semibold text-green-400 transition-all flex items-center justify-between"
              >
                <span>Cerrar Venta (Efectiva / Fallida)</span>
                <i class="fas fa-file-signature"></i>
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import { useAuthStore } from '../stores/auth';
import { prospectoService } from '../services/prospectoService';
import { useNotification } from '../composables/useNotification';

export default {
  setup() {
    const authStore = useAuthStore();
    const notification = useNotification();

    const prospectos = ref([]);
    const vehiculos = ref([]);
    const loading = ref(false);
    
    // Filtros
    const activeFilter = ref('todos');
    const filters = [
      { label: 'Todos', value: 'todos' },
      { label: 'Prospección', value: 'prospeccion' },
      { label: 'Calificación', value: 'calificacion' },
      { label: 'Negociación', value: 'negociacion' },
      { label: 'Cierre', value: 'cierre' },
    ];

    const currentUser = computed(() => authStore.user);

    // Modal de edición/adición
    const showFormModal = ref(false);
    const isEditing = ref(false);
    const form = ref({
      id: null,
      nombre: '',
      email: '',
      telefono: '',
      vehiculo_id: '',
      etapa: 'prospeccion',
      vendedor_id: '',
    });

    // Modal de avance de etapa rápido
    const showAdvanceModal = ref(false);
    const selectedProspecto = ref(null);

    const loadProspectos = async () => {
      loading.value = true;
      try {
        prospectos.value = await prospectoService.getProspectos();
      } catch (err) {
        notification.showError(err);
      } finally {
        loading.value = false;
      }
    };

    const loadVehiculos = async () => {
      try {
        vehiculos.value = await prospectoService.getVehiculos();
      } catch (err) {
        notification.showError(err);
      }
    };

    const filteredProspectos = computed(() => {
      if (activeFilter.value === 'todos') {
        return prospectos.value;
      }
      return prospectos.value.filter((p) => p.etapa === activeFilter.value);
    });

    const formatEtapa = (etapa) => {
      const map = {
        prospeccion: 'Prospección Inicial',
        calificacion: 'Calificación',
        negociacion: 'Negociación',
        cierre: 'Cierre',
      };
      return map[etapa] || etapa;
    };

    // Form Modal Actions
    const openAddModal = () => {
      isEditing.value = false;
      form.value = {
        id: null,
        nombre: '',
        email: '',
        telefono: '',
        vehiculo_id: '',
        etapa: 'prospeccion',
        vendedor_id: currentUser.value.id,
      };
      showFormModal.value = true;
    };

    const openEditModal = (p) => {
      isEditing.value = true;
      form.value = {
        id: p.id,
        nombre: p.nombre,
        email: p.email,
        telefono: p.telefono,
        vehiculo_id: p.vehiculo_id,
        etapa: p.etapa,
        vendedor_id: p.vendedor_id,
      };
      showFormModal.value = true;
    };

    const closeFormModal = () => {
      showFormModal.value = false;
    };

    const saveProspecto = async () => {
      try {
        if (isEditing.value) {
          await prospectoService.updateProspecto(form.value.id, form.value);
          notification.showSuccess('Prospecto actualizado exitosamente.');
        } else {
          await prospectoService.createProspecto(form.value);
          notification.showSuccess('Prospecto registrado con éxito.');
        }
        showFormModal.value = false;
        loadProspectos();
      } catch (err) {
        notification.showError(err);
      }
    };

    const handleDelete = async (id) => {
      if (confirm('¿Estás seguro de eliminar este prospecto permanentemente?')) {
        try {
          await prospectoService.deleteProspecto(id);
          notification.showSuccess('Prospecto eliminado.');
          loadProspectos();
        } catch (err) {
          notification.showError(err);
        }
      }
    };

    // Advance Modal Actions
    const openAdvanceModal = (p) => {
      selectedProspecto.value = p;
      showAdvanceModal.value = true;
    };

    const closeAdvanceModal = () => {
      showAdvanceModal.value = false;
    };

    const updateStage = async (newStage) => {
      try {
        await prospectoService.updateProspecto(selectedProspecto.value.id, {
          etapa: newStage,
        });
        notification.showSuccess(`Prospecto movido a: ${formatEtapa(newStage)}`);
        showAdvanceModal.value = false;
        loadProspectos();
      } catch (err) {
        notification.showError(err);
      }
    };

    onMounted(() => {
      loadProspectos();
      loadVehiculos();
    });

    return {
      prospectos,
      vehiculos,
      loading,
      activeFilter,
      filters,
      filteredProspectos,
      formatEtapa,
      currentUser,
      
      // Form Modal
      showFormModal,
      isEditing,
      form,
      openAddModal,
      openEditModal,
      closeFormModal,
      saveProspecto,
      handleDelete,
      
      // Advance Modal
      showAdvanceModal,
      selectedProspecto,
      openAdvanceModal,
      closeAdvanceModal,
      updateStage,
    };
  },
};
</script>
