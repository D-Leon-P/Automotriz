<template>
  <div class="p-4 sm:p-8 max-w-7xl mx-auto space-y-8 text-slate-100 font-sans">
    <!-- Encabezado -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h2 class="text-3xl font-extrabold tracking-tight text-white">Seguros Vehiculares</h2>
        <p class="text-slate-400 mt-1">Asocia pólizas y gestiona los seguros complementarios de cada venta.</p>
      </div>
      <button
        @click="openAddModal"
        class="self-start sm:self-auto px-4 py-2.5 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-slate-950 font-extrabold rounded-xl shadow-lg hover:shadow-amber-500/20 transition-all duration-200 flex items-center gap-2"
      >
        <i class="fas fa-shield-alt"></i>
        <span>Vincular Póliza</span>
      </button>
    </div>

    <!-- Spinner / Vacío / Tabla -->
    <div v-if="loading" class="flex flex-col items-center justify-center py-20 gap-3">
      <span class="animate-spin border-4 border-amber-500 border-t-transparent rounded-full w-12 h-12"></span>
      <p class="text-slate-500 font-semibold">Cargando pólizas de seguro...</p>
    </div>

    <div v-else-if="seguros.length === 0" class="glass-panel p-16 flex flex-col items-center text-center rounded-2xl">
      <div class="w-16 h-16 rounded-full bg-slate-900 border border-slate-800 flex items-center justify-center text-slate-500 text-2xl mb-4">
        <i class="fas fa-file-shield"></i>
      </div>
      <h3 class="text-lg font-bold text-slate-300">No hay seguros vinculados</h3>
      <p class="text-sm text-slate-500 max-w-sm mt-1">Vincula tu primera póliza de seguro vehicular a una venta concretada para comenzar el seguimiento.</p>
    </div>

    <!-- Tabla de Seguros -->
    <div v-else class="glass-panel overflow-x-auto rounded-2xl">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="bg-slate-950/40 border-b border-white/5 text-slate-400 uppercase text-xs font-bold tracking-wider">
            <th class="p-4 pl-6">Venta Asociada (Cliente)</th>
            <th class="p-4">Tipo de Seguro</th>
            <th class="p-4">Prima Esperada</th>
            <th class="p-4">Prima Real</th>
            <th class="p-4">Estado</th>
            <th class="p-4 pr-6 text-right">Acciones</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-white/5">
          <tr v-for="s in seguros" :key="s.id" class="hover:bg-slate-900/20 transition-colors duration-200 text-sm">
            <td class="p-4 pl-6">
              <div v-if="s.venta" class="flex flex-col">
                <span class="font-bold text-slate-200">
                  {{ getVentaDetail(s.venta.id)?.clientName || 'Cargando...' }}
                </span>
                <span class="text-xs text-slate-400 mt-0.5">
                  {{ getVentaDetail(s.venta.id)?.vehicleInfo || 'Cargando...' }}
                </span>
                <span class="text-[11px] text-slate-500 mt-0.5">
                  ID Venta #{{ s.venta.id }} - Auto: S/ {{ formatCurrency(s.venta.monto) }}
                </span>
              </div>
              <span v-else class="text-slate-500">Venta no disponible</span>
            </td>
            <td class="p-4 text-slate-300 font-semibold">{{ s.tipo_seguro }}</td>
            <td class="p-4 text-slate-300">S/ {{ formatCurrency(s.prima_esperada) }}</td>
            <td class="p-4 text-slate-200 font-bold">
              {{ s.prima_real ? `S/ ${formatCurrency(s.prima_real)}` : 'Pendiente' }}
            </td>
            <td class="p-4">
              <span
                :class="[
                  'px-3 py-1 rounded-full text-xs font-bold capitalize border inline-block',
                  s.estado === 'vendido' ? 'bg-green-500/10 border-green-500/20 text-green-400' : 'bg-yellow-500/10 border-yellow-500/20 text-yellow-400'
                ]"
              >
                {{ s.estado === 'vendido' ? 'Vendido' : 'Prospectado' }}
              </span>
            </td>
            <td class="p-4 pr-6 text-right space-x-2 whitespace-nowrap">
              <!-- Botón Editar -->
              <button
                @click="openEditModal(s)"
                v-title="'Editar póliza'"
                class="p-2 bg-slate-900/20 border border-white/5 hover:border-sky-500/30 text-slate-400 hover:text-sky-400 rounded-xl transition-all duration-200"
              >
                <i class="fas fa-edit text-xs"></i>
              </button>

              <!-- Botón Eliminar -->
              <button
                @click="handleDelete(s.id)"
                v-title.right="'Eliminar póliza'"
                class="p-2 bg-slate-900/20 border border-white/5 hover:border-red-500/30 text-slate-400 hover:text-red-400 rounded-xl transition-all duration-200"
              >
                <i class="fas fa-trash-alt text-xs"></i>
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal Vincular/Editar Seguro -->
    <teleport to="body">
      <div v-if="showFormModal" class="fixed inset-0 z-50 flex items-center justify-center px-4 bg-slate-950/80 backdrop-blur-sm">
        <div class="w-full max-w-lg glass-panel p-6 sm:p-8 rounded-2xl space-y-6">
        <div class="flex justify-between items-center pb-4 border-b border-white/5">
          <h3 class="text-xl font-bold text-white">
            {{ isEditing ? 'Editar Póliza de Seguro' : 'Vincular Póliza de Seguro' }}
          </h3>
          <button @click="closeFormModal" class="text-slate-400 hover:text-slate-200">
            <i class="fas fa-times text-lg"></i>
          </button>
        </div>

        <form @submit.prevent="saveSeguro" class="space-y-4">
          <div>
            <label class="block text-xs font-semibold text-slate-400 mb-1 uppercase tracking-wider">Seleccionar Venta Efectiva</label>
            <CustomSelect
              v-model="form.venta_id"
              :options="ventasOptions"
              placeholder="Selecciona la venta..."
              :disabled="isEditing"
            />
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-400 mb-1 uppercase tracking-wider">Plan de Seguro</label>
            <CustomSelect
              v-model="selectedProduct"
              :options="seguroProductOptions"
              placeholder="Selecciona un plan de seguro..."
              @change="onProductSelect"
            />
          </div>

          <div v-if="selectedProduct === 'personalizado'">
            <label class="block text-xs font-semibold text-slate-400 mb-1 uppercase tracking-wider">Especificar Tipo de Seguro</label>
            <input v-model="form.tipo_seguro" type="text" required placeholder="ej. Todo Riesgo Especial Coaseguro" class="w-full p-2.5 bg-slate-900/20 border border-white/5 rounded-xl text-slate-200 placeholder-slate-600 text-sm focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500/30 transition-all duration-300" />
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-semibold text-slate-400 mb-1 uppercase tracking-wider">Prima Esperada (S/)</label>
              <input 
                v-model="form.prima_esperada" 
                type="number" 
                step="0.01" 
                required 
                :readonly="selectedProduct !== 'personalizado'"
                class="w-full p-2.5 border rounded-xl text-sm focus:outline-none transition-all duration-300" 
                :class="selectedProduct !== 'personalizado' ? 'bg-slate-950/40 border-white/5 text-slate-400 cursor-not-allowed' : 'bg-slate-900/20 border-white/5 text-slate-200 focus:border-amber-500 focus:ring-1 focus:ring-amber-500/30'"
              />
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-400 mb-1 uppercase tracking-wider">Prima Real (S/)</label>
              <input v-model="form.prima_real" type="number" step="0.01" :required="form.estado === 'vendido'" class="w-full p-2.5 bg-slate-900/20 border border-white/5 rounded-xl text-slate-200 text-sm focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500/30 transition-all duration-300" />
            </div>
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-400 mb-1 uppercase tracking-wider">Estado de Póliza</label>
            <CustomSelect
              v-model="form.estado"
              :options="estadoOptions"
              placeholder="Selecciona el estado..."
            />
          </div>

          <div class="flex justify-end gap-3 pt-4 border-t border-white/5">
            <button type="button" @click="closeFormModal" class="px-4 py-2 bg-slate-900/20 border border-white/5 hover:border-slate-800 text-slate-400 hover:text-slate-200 rounded-xl text-sm font-semibold transition-all">
              Cancelar
            </button>
            <button type="submit" class="px-4 py-2 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-slate-950 rounded-xl text-sm font-bold shadow-lg hover:shadow-amber-500/20 transition-all duration-200">
              {{ isEditing ? 'Guardar Cambios' : 'Vincular Seguro' }}
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
import { seguroService } from '../services/seguroService';
import { ventaService } from '../services/ventaService';
import { useNotification } from '../composables/useNotification';
import CustomSelect from '../components/CustomSelect.vue';
import { useSwal } from '../composables/useSwal';

export default {
  components: {
    CustomSelect
  },
  setup() {
    const notification = useNotification();
    const { confirmDelete } = useSwal();

    const seguros = ref([]);
    const ventas = ref([]);
    const loading = ref(false);

    // Modal
    const showFormModal = ref(false);
    const isEditing = ref(false);
    const selectedProduct = ref('');
    const form = ref({
      id: null,
      venta_id: '',
      tipo_seguro: '',
      prima_esperada: '',
      prima_real: '',
      estado: 'prospectado',
    });

    const seguroProductOptions = [
      { value: 'Seguro Básico (Responsabilidad Civil)', label: 'Seguro Básico (Responsabilidad Civil) - S/ 450.00', price: 450 },
      { value: 'Seguro Estándar (Choque y Robo)', label: 'Seguro Estándar (Choque y Robo) - S/ 850.00', price: 850 },
      { value: 'Seguro Premium (Todo Riesgo)', label: 'Seguro Premium (Todo Riesgo) - S/ 1,200.00', price: 1200 },
      { value: 'Seguro Platino (Todo Riesgo + Asistencia)', label: 'Seguro Platino (Todo Riesgo + Asistencia) - S/ 1,800.00', price: 1800 },
      { value: 'personalizado', label: 'Otro / Personalizado...', price: 0 }
    ];

    const onProductSelect = (val) => {
      if (val === 'personalizado') {
        form.value.tipo_seguro = '';
        form.value.prima_esperada = '';
      } else {
        const opt = seguroProductOptions.find(o => o.value === val);
        if (opt) {
          form.value.tipo_seguro = opt.value;
          form.value.prima_esperada = opt.price;
        }
      }
    };

    const getVentaDetail = (ventaId) => {
      const v = ventas.value.find(item => item.id === ventaId);
      if (!v) return null;
      return {
        clientName: v.prospecto ? v.prospecto.nombre : 'Cliente N/A',
        vehicleInfo: v.vehiculo ? `${v.vehiculo.marca} ${v.vehiculo.modelo} (${v.vehiculo.anio})` : 'Vehículo N/A'
      };
    };

    // Solo ventas efectivas para poder asociarles seguro
    const ventasEfectivas = computed(() => {
      return ventas.value.filter((v) => v.estado === 'efectiva');
    });

    const ventasOptions = computed(() => {
      return ventasEfectivas.value.map(v => {
        const clientName = v.prospecto ? v.prospecto.nombre : 'Cliente N/A';
        const vehicleInfo = v.vehiculo ? `${v.vehiculo.marca} ${v.vehiculo.modelo} (${v.vehiculo.anio})` : 'Vehículo N/A';
        return {
          value: v.id,
          label: `Venta #${v.id} - ${clientName} - ${vehicleInfo} - S/ ${parseFloat(v.monto).toLocaleString('en-US', { minimumFractionDigits: 2 })}`
        };
      });
    });

    const estadoOptions = [
      { value: 'prospectado', label: 'Prospectado' },
      { value: 'vendido', label: 'Vendido' }
    ];

    const loadSeguros = async () => {
      loading.value = true;
      try {
        seguros.value = await seguroService.getSeguros();
      } catch (err) {
        notification.showError(err);
      } finally {
        loading.value = false;
      }
    };

    const loadVentas = async () => {
      try {
        ventas.value = await ventaService.getVentas();
      } catch (err) {
        notification.showError(err);
      }
    };

    const formatCurrency = (value) => {
      if (!value) return '0.00';
      return parseFloat(value).toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
      });
    };

    const openAddModal = () => {
      isEditing.value = false;
      selectedProduct.value = '';
      form.value = {
        id: null,
        venta_id: '',
        tipo_seguro: '',
        prima_esperada: '',
        prima_real: '',
        estado: 'prospectado',
      };
      showFormModal.value = true;
    };

    const openEditModal = (s) => {
      isEditing.value = true;
      const exists = seguroProductOptions.some(o => o.value === s.tipo_seguro);
      selectedProduct.value = exists ? s.tipo_seguro : 'personalizado';

      form.value = {
        id: s.id,
        venta_id: s.venta_id,
        tipo_seguro: s.tipo_seguro,
        prima_esperada: s.prima_esperada,
        prima_real: s.prima_real || '',
        estado: s.estado,
      };
      showFormModal.value = true;
    };

    const closeFormModal = () => {
      showFormModal.value = false;
    };

    const saveSeguro = async () => {
      // Validar rango de desviación de prima real si está vendido
      if (form.value.estado === 'vendido') {
        const esperada = parseFloat(form.value.prima_esperada);
        const real = parseFloat(form.value.prima_real);
        if (isNaN(real)) {
          notification.showError('La prima real es obligatoria si el seguro está vendido.');
          return;
        }
        const min = esperada * 0.70;
        const max = esperada * 1.30;
        if (real < min || real > max) {
          notification.showError(`La prima real (S/ ${real.toFixed(2)}) debe estar dentro del rango permitido del 70% al 130% de la prima esperada (rango permitido: S/ ${min.toFixed(2)} a S/ ${max.toFixed(2)}).`);
          return;
        }
      }

      try {
        // Si es prospectado y la prima real está vacía, enviamos null
        const payload = { ...form.value };
        if (payload.estado === 'prospectado' && !payload.prima_real) {
          payload.prima_real = null;
        }

        if (isEditing.value) {
          await seguroService.updateSeguro(form.value.id, payload);
          notification.showSuccess('Póliza de seguro actualizada con éxito.');
        } else {
          await seguroService.createSeguro(payload);
          notification.showSuccess('Póliza de seguro vinculada con éxito.');
        }
        showFormModal.value = false;
        loadSeguros();
      } catch (err) {
        notification.showError(err);
      }
    };

    const handleDelete = async (id) => {
      const result = await confirmDelete(
        '¿Eliminar seguro?',
        'Esta acción quitará el registro de la póliza de seguro de la lista.'
      );
      if (result.isConfirmed) {
        try {
          await seguroService.deleteSeguro(id);
          notification.showSuccess('Registro de seguro eliminado.');
          loadSeguros();
        } catch (err) {
          notification.showError(err);
        }
      }
    };

    onMounted(() => {
      loadSeguros();
      loadVentas();
    });

    return {
      seguros,
      ventasEfectivas,
      loading,
      formatCurrency,
      
      // Form Modal
      showFormModal,
      isEditing,
      form,
      openAddModal,
      openEditModal,
      closeFormModal,
      saveSeguro,
      handleDelete,
      
      // Select Options
      ventasOptions,
      estadoOptions,
      selectedProduct,
      seguroProductOptions,
      onProductSelect,
      getVentaDetail
    };
  },
};
</script>
