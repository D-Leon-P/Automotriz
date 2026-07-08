import { prospectoRepository } from '../repositories/prospectoRepository';

export const prospectoService = {
  async getProspectos() {
    try {
      const response = await prospectoRepository.getAll();
      return response.data;
    } catch (error) {
      throw error.response?.data?.message || 'Error al obtener prospectos';
    }
  },

  async getProspecto(id) {
    try {
      const response = await prospectoRepository.getById(id);
      return response.data;
    } catch (error) {
      throw error.response?.data?.message || 'Error al obtener el prospecto';
    }
  },

  async createProspecto(data) {
    try {
      const response = await prospectoRepository.create(data);
      return response.data;
    } catch (error) {
      throw error.response?.data?.message || 'Error al crear prospecto';
    }
  },

  async updateProspecto(id, data) {
    try {
      const response = await prospectoRepository.update(id, data);
      return response.data;
    } catch (error) {
      throw error.response?.data?.message || 'Error al actualizar prospecto';
    }
  },

  async deleteProspecto(id) {
    try {
      const response = await prospectoRepository.delete(id);
      return response.data;
    } catch (error) {
      throw error.response?.data?.message || 'Error al eliminar prospecto';
    }
  },

  async getVehiculos() {
    try {
      const response = await prospectoRepository.getVehiculos();
      return response.data;
    } catch (error) {
      throw error.response?.data?.message || 'Error al obtener vehículos';
    }
  }
};
