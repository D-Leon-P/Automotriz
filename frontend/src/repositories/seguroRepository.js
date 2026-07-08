import apiClient from './apiClient';

export const seguroRepository = {
  getAll() {
    return apiClient.get('/seguros');
  },

  getById(id) {
    return apiClient.get(`/seguros/${id}`);
  },

  create(data) {
    return apiClient.post('/seguros', data);
  },

  update(id, data) {
    return apiClient.put(`/seguros/${id}`, data);
  },

  delete(id) {
    return apiClient.delete(`/seguros/${id}`);
  }
};
