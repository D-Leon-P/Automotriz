import { defineStore } from 'pinia';
import { dashboardService } from '../services/dashboardService';

export const useDashboardStore = defineStore('dashboard', {
  state: () => ({
    metrics: null,
    loading: false,
    error: null,
  }),

  actions: {
    async fetchMetrics() {
      this.loading = true;
      this.error = null;
      try {
        const data = await dashboardService.getMetrics();
        this.metrics = data;
      } catch (err) {
        this.error = err;
        throw err;
      } finally {
        this.loading = false;
      }
    }
  }
});
