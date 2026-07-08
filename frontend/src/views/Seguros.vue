<template>
  <div class="p-8 max-w-7xl mx-auto space-y-8">
    <!-- Encabezado -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h2 class="text-3xl font-extrabold tracking-tight text-slate-100">Seguros Vehiculares</h2>
        <p class="text-slate-400 mt-1">Asocia pólizas y gestiona los seguros complementarios de cada venta.</p>
      </div>
      <button
        @click="openAddModal"
        class="self-start sm:self-auto px-4 py-2.5 bg-gradient-to-r from-brand to-brand-light hover:from-brand-dark hover:to-brand text-slate-950 font-extrabold rounded-lg shadow-lg hover:shadow-brand/20 transition-all flex items-center gap-2"
      >
        <i class="fas fa-shield-alt"></i>
        <span>Vincular Póliza</span>
      </button>
    </div>

    <!-- Spinner / Vacío / Tabla -->
    <div v-if="loading" class="flex flex-col items-center justify-center py-20 gap-3">
      <span class="animate-spin border-4 border-brand border-t-transparent rounded-full w-12 h-12"></span>
      <p class="text-slate-500 font-semibold">Cargando pólizas de seguro...</p>
    </div>

    <div v-else-if="seguros.length === 0" class="glass-panel p-16 border-slate-800 flex flex-col items-center text-center">
      <div class="w-16 h-16 rounded-full bg-slate-800/80 flex items-center justify-center text-slate-500 text-2xl mb-4">
        <i class="fas fa-file-shield"></i>
      </div>
      <h3 class="text-lg font-bold text-slate-300">No hay seguros vinculados</h3>
      <p class="text-sm text-slate-500 max-w-sm mt-1">Vincula tu primera póliza de seguro vehicular a una venta concretada para comenzar el seguimiento.</p>
    </div>

    <!-- Tabla de Seguros -->
    <div v-else class="glass-panel overflow-x-auto border-slate-800">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="bg-slate-950 border-b border-slate-800 text-slate-400 uppercase text-xs font-bold tracking-wider">
            <th class="p-4 pl-6">Venta Asociada (Cliente)</th>
            <th class="p-4">Tipo de Seguro</th>
            <th class="p-4">Prima Esperada</th>
            <th class="p-4">Prima Real</th>
            <th class="p-4">Estado</th>
            <th class="p-4 pr-6 text-right">Acciones</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-800/60">
          <tr v-for="s in seguros" :key="s.id" class="hover:bg-slate-900/40 transition-colors text-sm">
            <td class="p-4 pl-6">
              <div v-if="s.venta" class="flex flex-col">
                <span class="font-bold text-slate-200">
                  ID Venta #{{ s.venta.id }}
                </span>
                <span class="text-xs text-slate-500 mt-0.5">
                  Monto auto: ${{ formatCurrency(s.venta.monto) }}
                </span>
              </div>
              <span v-else class="text-slate-500">Venta no disponible</span>
            </td>
            <td class="p-4 text-slate-300 font-semibold">{{ s.tipo_seguro }}</td>
            <td class="p-4 text-slate-300">$ {{ formatCurrency(s.prima_esperada) }}</td>
            <td class="p-4 text-slate-200 font-bold">
              {{ s.prima_real ? `$ ${formatCurrency(s.prima_real)}` : 'Pendiente' }}
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
            <td class="p-4 pr-6 text-right space-x-2">
              <!-- Botón Editar -->
              <button
                @click="openEditModal(s)"
                class="p-2 bg-slate-900 border border-slate-800 hover:border-brand/40 text-slate-400 hover:text-brand-light rounded-lg transition-all"
              >
                <i class="fas fa-edit text-xs"></i>
              </button>

              <!-- Botón Eliminar -->
              <button
                @click="handleDelete(s.id)"
                class="p-2 bg-slate-900 border border-slate-800 hover:border-red-500/40 text-slate-400 hover:text-red-400 rounded-lg transition-all"
              >
                <i class="fas fa-trash-alt text-xs"></i>
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal Vincular/Editar Seguro -->
    <div v-if="showFormModal" class="fixed inset-0 z-50 flex items-center justify-center px-4 bg-slate-950/80 backdrop-blur-sm">
      <div class="w-full max-w-lg glass-panel p-6 border-slate-800 space-y-6">
        <div class="flex justify-between items-center">
          <h3 class="text-xl font-extrabold text-slate-200">
            {{ isEditing ? 'Editar Póliza de Seguro' : 'Vincular Póliza de Seguro' }}
          </h3>
          <button @click="closeFormModal" class="text-slate-400 hover:text-slate-200">
            <i class="fas fa-times text-lg"></i>
          </button>
        </div>

        <form @submit.prevent="saveSeguro" class="space-y-4">
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Venta Asociada</label>
            <select
              v-model="form.venta_id"
              :disabled="isEditing"
              required
              class="w-full p-2.5 bg-slate-850 border border-slate-800 rounded-lg text-slate-200 text-sm focus:outline-none focus:border-brand disabled:opacity-50"
            >
              <option value="" disabled>Selecciona la venta...</option>
              <option v-for="v in ventasEfectivas" :key="v.id" :value="v.id">
                Venta #{{ v.id }} - Monto: ${{ formatCurrency(v.monto) }}
              </option>
            </select>
          </div>

          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Tipo de Seguro</label>
            <input v-model="form.tipo_seguro" type="text" required placeholder="ej. Todo Riesgo Premium, Responsabilidad Civil" class="w-full p-2.5 bg-slate-850 border border-slate-800 rounded-lg text-slate-200 placeholder-slate-500 text-sm focus:outline-none focus:border-brand" />
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Prima Esperada ($)</label>
              <input v-model="form.prima_esperada" type="number" step="0.01" required class="w-full p-2.5 bg-slate-850 border border-slate-800 rounded-lg text-slate-200 text-sm focus:outline-none focus:border-brand" />
            </div>
            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Prima Real ($)</label>
              <input v-model="form.prima_real" type="number" step="0.01" :required="form.estado === 'vendido'" class="w-full p-2.5 bg-slate-850 border border-slate-800 rounded-lg text-slate-200 text-sm focus:outline-none focus:border-brand" />
            </div>
          </div>

          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Estado de Póliza</label>
            <select v-model="form.estado" class="w-full p-2.5 bg-slate-850 border border-slate-800 rounded-lg text-slate-200 text-sm focus:outline-none focus:border-brand">
              <option value="prospectado">Prospectado</option>
              <option value="vendido">Vendido</option>
            </select>
          </div>

          <div class="flex justify-end gap-3 pt-4 border-t border-slate-800">
            <button type="button" @click="closeFormModal" class="px-4 py-2 bg-slate-900 border border-slate-800 hover:border-slate-700 text-slate-400 hover:text-slate-200 rounded-lg text-sm font-semibold">
              Cancelar
            </button>
            <button type="submit" class="px-4 py-2 bg-brand hover:bg-brand-light text-slate-950 rounded-lg text-sm font-extrabold shadow">
              {{ isEditing ? 'Guardar Cambios' : 'Vincular Seguro' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import { seguroService } from '../services/seguroService';
import { ventaService } from '../services/ventaService';
import { useNotification } from '../composables/useNotification';

export default {
  setup() {
    const notification = useNotification();

    const seguros = ref([]);
    const ventas = ref([]);
    const loading = ref(false);

    // Modal
    const showFormModal = ref(false);
    const isEditing = ref(false);
    const form = ref({
      id: null,
      venta_id: '',
      tipo_seguro: '',
      prima_esperada: '',
      prima_real: '',
      estado: 'prospectado',
    });

    // Solo ventas efectivas para poder asociarles seguro
    const ventasEfectivas = computed(() => {
      return ventas.value.filter((v) => v.estado === 'efectiva');
    });

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
      if (confirm('¿Estás seguro de eliminar este registro de seguro?')) {
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
    };
  },
};
</script>
