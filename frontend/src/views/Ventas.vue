<template>
  <div class="p-4 sm:p-8 max-w-7xl mx-auto space-y-8 text-slate-100 font-sans">
    <!-- Encabezado -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h2 class="text-3xl font-extrabold tracking-tight text-white">Registro de Ventas</h2>
        <p class="text-slate-400 mt-1">Monitorea los cierres efectivos y documenta los motivos de pérdida.</p>
      </div>
      <button
        @click="openAddModal"
        class="self-start sm:self-auto px-4 py-2.5 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-slate-950 font-extrabold rounded-xl shadow-lg hover:shadow-amber-500/20 transition-all duration-200 flex items-center gap-2"
      >
        <i class="fas fa-file-signature"></i>
        <span>Registrar Cierre / Venta</span>
      </button>
    </div>

    <!-- Filtros de Ventas -->
    <div class="flex flex-wrap items-center justify-between gap-4 pb-2">
      <div></div>
      <label class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-slate-400 hover:text-slate-200 cursor-pointer select-none">
        <input 
          v-model="showDeleted" 
          type="checkbox" 
          class="w-4 h-4 rounded border-white/5 bg-slate-900/20 text-amber-500 focus:ring-amber-500/30"
          @change="loadVentas"
        />
        <span>Mostrar eliminadas</span>
      </label>
    </div>

    <!-- Spinner / Vacío / Tabla -->
    <div v-if="loading" class="flex flex-col items-center justify-center py-20 gap-3">
      <span class="animate-spin border-4 border-amber-500 border-t-transparent rounded-full w-12 h-12"></span>
      <p class="text-slate-500 font-semibold">Cargando histórico de ventas...</p>
    </div>

    <div v-else-if="ventas.length === 0" class="glass-panel p-16 flex flex-col items-center text-center rounded-2xl">
      <div class="w-16 h-16 rounded-full bg-slate-900 border border-slate-800 flex items-center justify-center text-slate-500 text-2xl mb-4">
        <i class="fas fa-receipt"></i>
      </div>
      <h3 class="text-lg font-bold text-slate-300">No hay ventas registradas</h3>
      <p class="text-sm text-slate-500 max-w-sm mt-1">Aún no se han documentado cierres comerciales. Registra uno nuevo para iniciar.</p>
    </div>

    <!-- Tabla de Ventas -->
    <div v-else class="glass-panel overflow-x-auto rounded-2xl">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="bg-slate-950/40 border-b border-white/5 text-slate-400 uppercase text-xs font-bold tracking-wider">
            <th class="p-4 pl-6">Cliente (Prospecto)</th>
            <th class="p-4">Vehículo</th>
            <th class="p-4">Monto</th>
            <th class="p-4">Colaborador</th>
            <th class="p-4">Estado</th>
            <th class="p-4">Observaciones / Motivo</th>
            <th class="p-4 pr-6 text-right">Acciones</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-white/5">
          <tr 
            v-for="v in ventas" 
            :key="v.id" 
            :class="[
              'hover:bg-slate-900/20 transition-colors duration-200 text-sm', 
              v.deleted_at ? 'opacity-40 bg-slate-950/20' : ''
            ]"
          >
            <td class="p-4 pl-6 font-bold text-slate-200">
              <div class="flex items-center gap-1.5">
                <span>{{ v.prospecto ? v.prospecto.nombre : 'Prospecto Eliminado' }}</span>
                <span v-if="v.deleted_at" class="text-[9px] bg-red-500/10 border border-red-500/20 text-red-400 px-1.5 py-0.5 rounded-full font-bold uppercase tracking-wider">Eliminada</span>
              </div>
            </td>
            <td class="p-4 text-slate-300">
              <span v-if="v.vehiculo" class="font-semibold text-slate-300">
                {{ v.vehiculo.marca }} {{ v.vehiculo.modelo }} ({{ v.vehiculo.anio }})
              </span>
              <span v-else class="text-slate-500">N/A</span>
            </td>
            <td class="p-4 text-amber-400 font-extrabold">
              S/ {{ formatCurrency(v.monto) }}
            </td>
            <td class="p-4 text-slate-400 font-medium">
              {{ v.empleado ? v.empleado.nombre : 'No asignado' }}
            </td>
            <td class="p-4">
              <span
                :class="[
                  'px-3 py-1 rounded-full text-xs font-bold capitalize border inline-block',
                  v.deleted_at ? 'bg-slate-500/10 border-slate-500/20 text-slate-500' : '',
                  (!v.deleted_at && v.estado === 'efectiva') ? 'bg-green-500/10 border-green-500/20 text-green-400' : '',
                  (!v.deleted_at && v.estado === 'fallida') ? 'bg-red-500/10 border-red-500/20 text-red-400' : '',
                ]"
              >
                {{ v.deleted_at ? 'Eliminada' : (v.estado === 'efectiva' ? 'Efectiva' : 'Fallida') }}
              </span>
            </td>
            <td class="p-4 text-slate-400 max-w-xs truncate">
              {{ v.estado === 'fallida' ? v.motivo_perdida : 'Cierre exitoso' }}
            </td>
            <td class="p-4 pr-6 text-right space-x-2 whitespace-nowrap">
              <!-- Si está eliminada -->
              <template v-if="v.deleted_at">
                <button
                  v-if="isAdmin"
                  @click="handleRestore(v.id)"
                  v-title="'Reintegrar venta'"
                  class="p-2 bg-slate-900/20 border border-white/5 hover:border-green-500/30 text-slate-400 hover:text-green-400 rounded-xl transition-all duration-200"
                >
                  <i class="fas fa-undo text-xs"></i>
                </button>
              </template>
              <!-- Si está activa -->
              <template v-else>
                <!-- Botón Eliminar -->
                <button
                  v-if="isAdmin"
                  @click="handleDelete(v.id)"
                  v-title.right="'Eliminar venta'"
                  class="p-2 bg-slate-900/20 border border-white/5 hover:border-red-500/30 text-slate-400 hover:text-red-400 rounded-xl transition-all duration-200"
                >
                  <i class="fas fa-trash-alt text-xs"></i>
                </button>
              </template>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal Registrar Cierre/Venta -->
    <teleport to="body">
      <div v-if="showFormModal" class="fixed inset-0 z-50 flex items-center justify-center px-4 bg-slate-950/80 backdrop-blur-sm">
        <div class="w-full max-w-lg glass-panel p-6 sm:p-8 rounded-2xl space-y-6">
        <div class="flex justify-between items-center pb-4 border-b border-white/5">
          <h3 class="text-xl font-bold text-white">Registrar Venta / Cierre</h3>
          <button @click="closeFormModal" class="text-slate-400 hover:text-slate-200">
            <i class="fas fa-times text-lg"></i>
          </button>
        </div>

        <form @submit.prevent="saveVenta" class="space-y-4">
          <div>
            <label class="block text-xs font-semibold text-slate-400 mb-1 uppercase tracking-wider">Seleccionar Prospecto</label>
            <CustomSelect
              v-model="form.prospecto_id"
              :options="prospectosOptions"
              placeholder="Selecciona el cliente..."
              @change="onProspectoSelect"
            />
            <p class="text-xxs text-slate-500 mt-1">Sólo se muestran prospectos activos en etapa de venta (previos al cierre).</p>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-semibold text-slate-400 mb-1 uppercase tracking-wider">Vehículo Asignado</label>
              <input type="text" readonly :value="selectedVehiculoName" class="w-full p-2.5 bg-slate-950/40 border border-white/5 rounded-xl text-slate-500 text-sm focus:outline-none" />
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-400 mb-1 uppercase tracking-wider">Monto de Venta (S/)</label>
              <input v-model="form.monto" type="number" step="0.01" readonly required class="w-full p-2.5 bg-slate-950/40 border border-white/5 rounded-xl text-slate-500 text-sm focus:outline-none" />
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-semibold text-slate-400 mb-1 uppercase tracking-wider">Colaborador Asignado</label>
              <input type="text" readonly :value="currentUser.nombre" class="w-full p-2.5 bg-slate-950/40 border border-white/5 rounded-xl text-slate-500 text-sm" />
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-400 mb-1 uppercase tracking-wider">Resultado de Venta</label>
              <CustomSelect
                v-model="form.estado"
                :options="estadoOptions"
                placeholder="Selecciona el resultado..."
              />
            </div>
          </div>

          <!-- Motivo de pérdida (solo visible si estado es fallido) -->
          <div v-if="form.estado === 'fallida'">
            <label class="block text-xs font-semibold text-slate-400 mb-1 uppercase tracking-wider">Motivo de Pérdida</label>
            <textarea
              v-model="form.motivo_perdida"
              required
              rows="3"
              placeholder="Explica por qué no se concretó la venta (ej. presupuesto, competidor, etc.)"
              class="w-full p-2.5 bg-slate-900/20 border border-white/5 rounded-xl text-slate-200 placeholder-slate-600 text-sm focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500/30 transition-all duration-300"
            ></textarea>
          </div>

          <div class="flex justify-end gap-3 pt-4 border-t border-white/5">
            <button type="button" @click="closeFormModal" class="px-4 py-2 bg-slate-900/20 border border-white/5 hover:border-slate-800 text-slate-400 hover:text-slate-200 rounded-xl text-sm font-semibold transition-all">
              Cancelar
            </button>
            <button type="submit" class="px-4 py-2 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-slate-950 rounded-xl text-sm font-bold shadow-lg hover:shadow-amber-500/20 transition-all duration-200">
              Registrar Cierre
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
import { useAuthStore } from '../stores/auth';
import { ventaService } from '../services/ventaService';
import { prospectoService } from '../services/prospectoService';
import { useNotification } from '../composables/useNotification';
import CustomSelect from '../components/CustomSelect.vue';
import { useSwal } from '../composables/useSwal';

export default {
  components: {
    CustomSelect
  },
  setup() {
    const authStore = ref(useAuthStore());
    const notification = useNotification();
    const { confirmDelete } = useSwal();

    const ventas = ref([]);
    const prospectos = ref([]);
    const loading = ref(false);

    // Modal
    const showFormModal = ref(false);
    const form = ref({
      prospecto_id: '',
      vehiculo_id: '',
      empleado_id: '',
      monto: '',
      estado: 'efectiva',
      motivo_perdida: '',
    });

    const currentUser = computed(() => authStore.value.user);

    const isAdmin = computed(() => {
      return currentUser.value && currentUser.value.rol && currentUser.value.rol.nombre === 'administrador';
    });

    const prospectosOptions = computed(() => {
      return activeProspectos.value.map(p => ({
        value: p.id,
        label: `${p.nombre} - Interés: ${p.vehiculo ? `${p.vehiculo.marca} ${p.vehiculo.modelo}` : 'Auto no asignado'}`
      }));
    });

    const estadoOptions = [
      { value: 'efectiva', label: 'Venta Efectiva' },
      { value: 'fallida', label: 'Venta Fallida' }
    ];

    // Filtrar prospectos activos (no cerrados)
    const activeProspectos = computed(() => {
      return prospectos.value.filter((p) => p.etapa !== 'cierre');
    });

    const selectedProspectoObj = computed(() => {
      return prospectos.value.find((p) => p.id === form.value.prospecto_id);
    });

    const selectedVehiculoName = computed(() => {
      const p = selectedProspectoObj.value;
      return p && p.vehiculo ? `${p.vehiculo.marca} ${p.vehiculo.modelo} (${p.vehiculo.anio})` : 'No asignado';
    });

    const showDeleted = ref(false);

    const loadVentas = async () => {
      loading.value = true;
      try {
        ventas.value = await ventaService.getVentas(showDeleted.value);
      } catch (err) {
        notification.showError(err);
      } finally {
        loading.value = false;
      }
    };

    const loadProspectos = async () => {
      try {
        prospectos.value = await prospectoService.getProspectos();
      } catch (err) {
        notification.showError(err);
      }
    };

    const formatCurrency = (value) => {
      return parseFloat(value).toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
      });
    };

    const openAddModal = () => {
      form.value = {
        prospecto_id: '',
        vehiculo_id: '',
        empleado_id: currentUser.value.id,
        monto: '',
        estado: 'efectiva',
        motivo_perdida: '',
      };
      showFormModal.value = true;
    };

    const closeFormModal = () => {
      showFormModal.value = false;
    };

    const onProspectoSelect = () => {
      const p = selectedProspectoObj.value;
      if (p) {
        form.value.vehiculo_id = p.vehiculo_id;
        form.value.monto = p.vehiculo ? p.vehiculo.price || p.vehiculo.precio : '';
      }
    };

    const saveVenta = async () => {
      try {
        await ventaService.createVenta(form.value);
        notification.showSuccess('Cierre registrado exitosamente.');
        showFormModal.value = false;
        loadVentas();
        loadProspectos(); // Recargar prospectos activos
      } catch (err) {
        notification.showError(err);
      }
    };

    const handleDelete = async (id) => {
      const result = await confirmDelete(
        '¿Eliminar venta?',
        'Esta acción eliminará el registro de la venta y devolverá el vehículo al inventario si corresponde.'
      );
      if (result.isConfirmed) {
        try {
          await ventaService.deleteVenta(id);
          notification.showSuccess('Registro de venta eliminado.');
          loadVentas();
          loadProspectos();
        } catch (err) {
          notification.showError(err);
        }
      }
    };

    const handleRestore = async (id) => {
      try {
        await ventaService.restoreVenta(id);
        notification.showSuccess('Registro de venta reintegrado exitosamente.');
        loadVentas();
        loadProspectos();
      } catch (err) {
        notification.showError(err);
      }
    };

    onMounted(() => {
      loadVentas();
      loadProspectos();
    });

    return {
      ventas,
      prospectos,
      activeProspectos,
      loading,
      formatCurrency,
      currentUser,
      isAdmin,
      
      // Form Modal
      showFormModal,
      form,
      openAddModal,
      closeFormModal,
      onProspectoSelect,
      selectedVehiculoName,
      saveVenta,
      handleDelete,
      
      // Select options
      prospectosOptions,
      estadoOptions,
      showDeleted,
      handleRestore,
      loadVentas
    };
  },
};
</script>
