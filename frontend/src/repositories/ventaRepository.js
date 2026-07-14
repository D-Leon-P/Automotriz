import apiClient from './apiClient';

export const ventaRepository = {
  getAll(showDeleted = false) {
    return apiClient.get('/ventas', { params: { show_deleted: showDeleted } });
  },

  restore(id) {
    return apiClient.post(`/ventas/${id}/restore`);
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
