import { dashboardRepository } from '../repositories/dashboardRepository';

export const dashboardService = {
  async getMetrics() {
    try {
      const response = await dashboardRepository.getMetrics();
      return response.data;
    } catch (error) {
      throw error.response?.data?.message || 'Error al obtener métricas del dashboard';
    }
  }
};
