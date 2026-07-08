import apiClient from './apiClient';

export const dashboardRepository = {
  getMetrics() {
    return apiClient.get('/dashboard');
  }
};
