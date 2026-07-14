import apiClient from './apiClient';

export const prospectoRepository = {
  getAll(showDeleted = false) {
    return apiClient.get('/prospectos', { params: { show_deleted: showDeleted } });
  },

  restore(id) {
    return apiClient.post(`/prospectos/${id}/restore`);
  },

  getById(id) {
    return apiClient.get(`/prospectos/${id}`);
  },

  create(data) {
    return apiClient.post('/prospectos', data);
  },

  update(id, data) {
    return apiClient.put(`/prospectos/${id}`, data);
  },

  delete(id) {
    return apiClient.delete(`/prospectos/${id}`);
  },

  getVehiculos() {
    return apiClient.get('/vehiculos');
  }
};
