<template>
  <div class="p-8 max-w-7xl mx-auto space-y-8">
    <!-- Encabezado -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h2 class="text-3xl font-extrabold tracking-tight text-slate-100">Panel de Control Comercial</h2>
        <p class="text-slate-400 mt-1">Métricas clave e indicadores del embudo de ventas en tiempo real.</p>
      </div>
      <button
        @click="loadData"
        :disabled="loading"
        class="self-start md:self-auto px-4 py-2.5 bg-slate-900 border border-slate-800 hover:border-brand/40 text-slate-300 hover:text-brand-light rounded-lg transition-all text-sm font-semibold flex items-center gap-2 shadow"
      >
        <i :class="['fas fa-sync-alt', loading && 'animate-spin']"></i>
        <span>Actualizar Datos</span>
      </button>
    </div>

    <!-- Spinner General de Carga -->
    <div v-if="loading && !metrics" class="flex flex-col items-center justify-center py-20 gap-3">
      <span class="animate-spin border-4 border-brand border-t-transparent rounded-full w-12 h-12"></span>
      <p class="text-slate-500 font-medium">Cargando métricas comerciales...</p>
    </div>

    <div v-else-if="metrics" class="space-y-8">
      <!-- Fila de Tarjetas KPI -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- KPI 1: Prospectos Activos -->
        <div class="glass-panel p-6 flex items-center justify-between border-slate-800">
          <div>
            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Prospectos</p>
            <h3 class="text-3xl font-extrabold text-slate-100 mt-2">{{ metrics.kpis.total_prospectos }}</h3>
            <p class="text-xs text-slate-500 mt-1">{{ metrics.kpis.prospectos_en_proceso }} activos en proceso</p>
          </div>
          <div class="w-12 h-12 rounded-lg bg-blue-500/10 border border-blue-500/20 flex items-center justify-center text-blue-400 text-xl">
            <i class="fas fa-users"></i>
          </div>
        </div>

        <!-- KPI 2: Ventas Realizadas -->
        <div class="glass-panel p-6 flex items-center justify-between border-slate-800">
          <div>
            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Ventas Realizadas</p>
            <h3 class="text-3xl font-extrabold text-slate-100 mt-2">{{ metrics.kpis.ventas_realizadas }}</h3>
            <p class="text-xs text-green-500 mt-1 font-semibold">
              $ {{ formatCurrency(metrics.kpis.monto_total_vendido) }}
            </p>
          </div>
          <div class="w-12 h-12 rounded-lg bg-green-500/10 border border-green-500/20 flex items-center justify-center text-green-400 text-xl">
            <i class="fas fa-handshake"></i>
          </div>
        </div>

        <!-- KPI 3: Tasa de Conversión -->
        <div class="glass-panel p-6 flex items-center justify-between border-slate-800">
          <div>
            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Tasa de Conversión</p>
            <h3 class="text-3xl font-extrabold text-slate-100 mt-2">{{ metrics.kpis.tasa_conversion }}%</h3>
            <p class="text-xs text-slate-500 mt-1">{{ metrics.kpis.ventas_fallidas }} oportunidades perdidas</p>
          </div>
          <div class="w-12 h-12 rounded-lg bg-yellow-500/10 border border-yellow-500/20 flex items-center justify-center text-yellow-400 text-xl">
            <i class="fas fa-percentage"></i>
          </div>
        </div>

        <!-- KPI 4: Seguros Vinculados -->
        <div class="glass-panel p-6 flex items-center justify-between border-slate-800">
          <div>
            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Seguros Vinculados</p>
            <h3 class="text-3xl font-extrabold text-slate-100 mt-2">{{ metrics.kpis.seguros_vinculados }}</h3>
            <p class="text-xs text-brand-light mt-1 font-semibold">
              $ {{ formatCurrency(metrics.kpis.prima_total_seguros) }} en primas
            </p>
          </div>
          <div class="w-12 h-12 rounded-lg bg-brand/10 border border-brand/20 flex items-center justify-center text-brand-light text-xl">
            <i class="fas fa-shield-alt"></i>
          </div>
        </div>
      </div>

      <!-- Sección de Gráficos y Tablas -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Columna Izquierda/Centro: Embudo de Ventas -->
        <div class="lg:col-span-2 glass-panel p-6 border-slate-800 space-y-6">
          <div>
            <h4 class="font-extrabold text-lg text-slate-200">Embudo de Conversión Comercial</h4>
            <p class="text-sm text-slate-400 mt-0.5">Progreso y porcentaje de los leads a lo largo de las etapas comerciales.</p>
          </div>

          <!-- Representación Visual del Embudo -->
          <div class="space-y-4">
            <div
              v-for="(stage, index) in metrics.embudo"
              :key="stage.etapa"
              class="relative"
            >
              <div
                class="flex items-center justify-between p-4 bg-gradient-to-r from-slate-900 to-slate-900/60 border border-slate-800 rounded-lg relative overflow-hidden z-10"
              >
                <!-- Barra de Progreso en el Fondo -->
                <div
                  class="absolute left-0 top-0 bottom-0 bg-brand/10 transition-all duration-500 -z-10"
                  :style="{ width: `${stage.porcentaje}%` }"
                ></div>

                <div class="flex items-center gap-3">
                  <div class="w-7 h-7 rounded-full bg-slate-800 flex items-center justify-center text-xs font-bold text-slate-400">
                    {{ index + 1 }}
                  </div>
                  <div>
                    <span class="font-bold text-slate-200 text-sm">{{ stage.etapa }}</span>
                  </div>
                </div>

                <div class="flex items-center gap-6">
                  <span class="text-sm font-semibold text-slate-400">{{ stage.cantidad }} leads</span>
                  <span class="text-sm font-black text-brand-light bg-brand/10 px-2.5 py-1 rounded">
                    {{ stage.porcentaje }}%
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Columna Derecha: Rendimiento de Vendedores -->
        <div class="glass-panel p-6 border-slate-800 space-y-6">
          <div>
            <h4 class="font-extrabold text-lg text-slate-200">Desempeño de Vendedores</h4>
            <p class="text-sm text-slate-400 mt-0.5">Tasa de conversión efectiva por asesor comercial.</p>
          </div>

          <div class="space-y-5">
            <div
              v-for="seller in metrics.vendedores"
              :key="seller.id"
              class="space-y-2"
            >
              <div class="flex justify-between items-center text-sm">
                <div class="flex items-center gap-2">
                  <div class="w-8 h-8 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center text-xs font-bold text-slate-300">
                    {{ seller.nombre.charAt(0).toUpperCase() }}
                  </div>
                  <span class="font-semibold text-slate-300">{{ seller.nombre }}</span>
                </div>
                <span class="font-black text-brand-light">{{ parseFloat(seller.tasa_conversion).toFixed(1) }}%</span>
              </div>
              
              <!-- Barra de Progreso -->
              <div class="w-full bg-slate-950 rounded-full h-2 overflow-hidden border border-slate-800">
                <div
                  class="bg-gradient-to-r from-brand to-brand-light h-full rounded-full"
                  :style="{ width: `${seller.tasa_conversion}%` }"
                ></div>
              </div>
              
              <div class="flex justify-between text-xs text-slate-500">
                <span>{{ seller.total_prospectos }} prospectos</span>
                <span>{{ seller.ventas_efectivas }} ventas</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { onMounted, computed } from 'vue';
import { useDashboardStore } from '../stores/dashboard';
import { useNotification } from '../composables/useNotification';

export default {
  setup() {
    const dashboardStore = useDashboardStore();
    const notification = useNotification();

    const metrics = computed(() => dashboardStore.metrics);
    const loading = computed(() => dashboardStore.loading);

    const loadData = async () => {
      try {
        await dashboardStore.fetchMetrics();
      } catch (err) {
        notification.showError('No se pudieron obtener las métricas del servidor.');
      }
    };

    const formatCurrency = (value) => {
      return parseFloat(value).toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
      });
    };

    onMounted(() => {
      loadData();
    });

    return {
      metrics,
      loading,
      loadData,
      formatCurrency,
    };
  },
};
</script>
