import apiClient from './apiClient';

export const generalesRepository = {
  // Roles
  getRoles() {
    return apiClient.get('/roles');
  },
  getRol(id) {
    return apiClient.get(`/roles/${id}`);
  },
  createRol(data) {
    return apiClient.post('/roles', data);
  },
  updateRol(id, data) {
    return apiClient.put(`/roles/${id}`, data);
  },
  deleteRol(id) {
    return apiClient.delete(`/roles/${id}`);
  },

  // Permisos
  getPermisos() {
    return apiClient.get('/permisos');
  },

  // Empleados
  getEmpleados() {
    return apiClient.get('/empleados');
  },
  getEmpleado(id) {
    return apiClient.get(`/empleados/${id}`);
  },
  createEmpleado(data) {
    return apiClient.post('/empleados', data);
  },
  updateEmpleado(id, data) {
    return apiClient.put(`/empleados/${id}`, data);
  },
  deleteEmpleado(id) {
    return apiClient.delete(`/empleados/${id}`);
  },

  // Clientes
  getClientes() {
    return apiClient.get('/clientes');
  },
  getCliente(id) {
    return apiClient.get(`/clientes/${id}`);
  },
  createCliente(data) {
    return apiClient.post('/clientes', data);
  },
  updateCliente(id, data) {
    return apiClient.put(`/clientes/${id}`, data);
  },
  deleteCliente(id) {
    return apiClient.delete(`/clientes/${id}`);
  }
};
