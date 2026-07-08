import apiClient from './apiClient';

export const ventaRepository = {
  getAll() {
    return apiClient.get('/ventas');
  },

  getById(id) {
    return apiClient.get(`/ventas/${id}`);
  },

  create(data) {
    return apiClient.post('/ventas', data);
  },

  update(id, data) {
    return apiClient.put(`/ventas/${id}`, data);
  },

  delete(id) {
    return apiClient.delete(`/ventas/${id}`);
  }
};
