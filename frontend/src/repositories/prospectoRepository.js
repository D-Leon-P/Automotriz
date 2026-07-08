import apiClient from './apiClient';

export const prospectoRepository = {
  getAll() {
    return apiClient.get('/prospectos');
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
