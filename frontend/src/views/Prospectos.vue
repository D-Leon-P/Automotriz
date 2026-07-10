<template>
  <div class="p-4 sm:p-8 max-w-7xl mx-auto space-y-8 text-slate-100 font-sans">
    <!-- Encabezado -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h2 class="text-3xl font-extrabold tracking-tight text-white">Gestión de Prospectos</h2>
        <p class="text-slate-400 mt-1">Registra y califica potenciales clientes a través de tu embudo comercial.</p>
      </div>
      <button
        @click="openAddModal"
        class="self-start sm:self-auto px-4 py-2.5 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-slate-950 font-extrabold rounded-xl shadow-lg hover:shadow-amber-500/20 transition-all duration-200 flex items-center gap-2"
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
          'px-4 py-2 rounded-full text-xs font-bold uppercase tracking-wider border transition-all duration-200',
          activeFilter === filter.value
            ? 'bg-amber-500/10 border-amber-500 text-amber-400 font-black shadow'
            : 'bg-slate-900/20 border-white/5 text-slate-400 hover:border-white/10 hover:text-slate-200'
        ]"
      >
        {{ filter.label }}
      </button>
    </div>

    <!-- Spinner / Vacío / Tabla -->
    <div v-if="loading" class="flex flex-col items-center justify-center py-20 gap-3">
      <span class="animate-spin border-4 border-amber-500 border-t-transparent rounded-full w-12 h-12"></span>
      <p class="text-slate-500 font-semibold">Cargando prospectos...</p>
    </div>

    <div v-else-if="filteredProspectos.length === 0" class="glass-panel p-16 border-slate-900/40 flex flex-col items-center text-center rounded-2xl">
      <div class="w-16 h-16 rounded-full bg-slate-900 border border-slate-800 flex items-center justify-center text-slate-500 text-2xl mb-4">
        <i class="fas fa-users-slash"></i>
      </div>
      <h3 class="text-lg font-bold text-slate-300">No hay prospectos registrados</h3>
      <p class="text-sm text-slate-500 max-w-sm mt-1">Registra tu primer prospecto o selecciona otra etapa de filtrado para ver la lista.</p>
    </div>

    <!-- Tabla de Prospectos -->
    <div v-else class="glass-panel overflow-x-auto rounded-2xl">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="bg-slate-950/40 border-b border-white/5 text-slate-400 uppercase text-xs font-bold tracking-wider">
            <th class="p-4 pl-6">Cliente</th>
            <th class="p-4">Contacto</th>
            <th class="p-4">Vehículo de Interés</th>
            <th class="p-4">Colaborador</th>
            <th class="p-4">Etapa</th>
            <th class="p-4 pr-6 text-right">Acciones</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-white/5">
          <tr v-for="p in filteredProspectos" :key="p.id" class="hover:bg-slate-900/20 transition-colors duration-200 text-sm">
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
              {{ p.empleado ? p.empleado.nombre : 'No asignado' }}
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
                class="p-2 bg-slate-900/20 border border-white/5 hover:border-amber-500/30 text-slate-400 hover:text-amber-400 rounded-xl transition-all duration-200"
              >
                <i class="fas fa-step-forward text-xs"></i>
              </button>
              
              <!-- Botón Editar Ficha -->
              <button
                @click="openEditModal(p)"
                v-title="'Editar datos'"
                class="p-2 bg-slate-900/20 border border-white/5 hover:border-sky-500/30 text-slate-400 hover:text-sky-400 rounded-xl transition-all duration-200"
              >
                <i class="fas fa-edit text-xs"></i>
              </button>

              <!-- Botón Eliminar -->
              <button
                @click="handleDelete(p.id)"
                v-title.right="'Eliminar prospecto'"
                class="p-2 bg-slate-900/20 border border-white/5 hover:border-red-500/30 text-slate-400 hover:text-red-400 rounded-xl transition-all duration-200"
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
      <div class="w-full max-w-lg glass-panel p-6 sm:p-8 rounded-2xl space-y-6">
        <div class="flex justify-between items-center pb-4 border-b border-white/5">
          <h3 class="text-xl font-bold text-white">
            {{ isEditing ? 'Editar Prospecto' : 'Registrar Prospecto' }}
          </h3>
          <button @click="closeFormModal" class="text-slate-400 hover:text-slate-200">
            <i class="fas fa-times text-lg"></i>
          </button>
        </div>

        <form @submit.prevent="saveProspecto" class="space-y-4">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-semibold text-slate-400 mb-1 uppercase tracking-wider">Nombre Completo</label>
              <input v-model="form.nombre" type="text" required class="w-full p-2.5 bg-slate-900/20 border border-white/5 rounded-xl text-slate-200 placeholder-slate-600 text-sm focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500/30 transition-all duration-300" />
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-400 mb-1 uppercase tracking-wider">Email</label>
              <input v-model="form.email" type="email" required class="w-full p-2.5 bg-slate-900/20 border border-white/5 rounded-xl text-slate-200 placeholder-slate-600 text-sm focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500/30 transition-all duration-300" />
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-semibold text-slate-400 mb-1 uppercase tracking-wider">Teléfono</label>
              <input v-model="form.telefono" type="text" class="w-full p-2.5 bg-slate-900/20 border border-white/5 rounded-xl text-slate-200 placeholder-slate-600 text-sm focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500/30 transition-all duration-300" />
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-400 mb-1 uppercase tracking-wider">Vehículo de Interés</label>
              <CustomSelect
                v-model="form.vehiculo_id"
                :options="vehiculosOptions"
                placeholder="Selecciona un auto"
              />
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-semibold text-slate-400 mb-1 uppercase tracking-wider">Colaborador Asignado</label>
              <input type="text" readonly :value="currentUser.nombre" class="w-full p-2.5 bg-slate-950/40 border border-white/5 rounded-xl text-slate-500 text-sm focus:outline-none" />
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-400 mb-1 uppercase tracking-wider">Etapa Inicial</label>
              <CustomSelect
                v-model="form.etapa"
                :options="etapaOptions"
                placeholder="Selecciona una etapa"
              />
            </div>
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

    <!-- Modal Avanzar Etapa -->
    <div v-if="showAdvanceModal" class="fixed inset-0 z-50 flex items-center justify-center px-4 bg-slate-950/80 backdrop-blur-sm">
      <div class="w-full max-w-sm glass-panel p-6 border-slate-900/40 rounded-2xl space-y-6">
        <div class="flex justify-between items-center pb-2 border-b border-slate-900">
          <h3 class="text-xl font-bold text-white">Actualizar Etapa</h3>
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
                class="w-full p-3 bg-slate-900/40 hover:bg-slate-900 border border-slate-800/80 hover:border-amber-500/30 text-left rounded-xl text-sm font-semibold text-slate-300 transition-all duration-200 flex items-center justify-between"
              >
                <span>{{ formatEtapa(stage) }}</span>
                <i class="fas fa-chevron-right text-slate-500"></i>
              </button>

              <router-link
                to="/ventas"
                class="w-full p-3 bg-slate-900/40 hover:bg-slate-900 border border-slate-800/80 hover:border-emerald-500/30 text-left rounded-xl text-sm font-semibold text-emerald-400 transition-all duration-200 flex items-center justify-between"
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
import CustomSelect from '../components/CustomSelect.vue';

export default {
  components: {
    CustomSelect
  },
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

    const vehiculosOptions = computed(() => {
      return vehiculos.value.map(v => ({
        value: v.id,
        label: `${v.marca} ${v.modelo} (${v.anio}) - S/ ${parseFloat(v.precio).toLocaleString('en-US', { minimumFractionDigits: 2 })}`
      }));
    });

    const etapaOptions = [
      { value: 'prospeccion', label: 'Prospección Inicial' },
      { value: 'calificacion', label: 'Calificación' },
      { value: 'negociacion', label: 'Negociación' },
      { value: 'cierre', label: 'Cierre' }
    ];

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
      empleado_id: '',
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
        empleado_id: currentUser.value.id,
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
        empleado_id: p.empleado_id,
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
      
      // Select options
      vehiculosOptions,
      etapaOptions,
    };
  },
};
</script>
