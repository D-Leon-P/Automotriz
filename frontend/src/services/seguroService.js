import { seguroRepository } from '../repositories/seguroRepository';

export const seguroService = {
  async getSeguros() {
    try {
      const response = await seguroRepository.getAll();
      return response.data;
    } catch (error) {
      throw error.response?.data?.message || 'Error al obtener seguros';
    }
  },

  async getSeguro(id) {
    try {
      const response = await seguroRepository.getById(id);
      return response.data;
    } catch (error) {
      throw error.response?.data?.message || 'Error al obtener el seguro';
    }
  },

  async createSeguro(data) {
    try {
      const response = await seguroRepository.create(data);
      return response.data;
    } catch (error) {
      throw error.response?.data?.message || 'Error al vincular seguro';
    }
  },

  async updateSeguro(id, data) {
    try {
      const response = await seguroRepository.update(id, data);
      return response.data;
    } catch (error) {
      throw error.response?.data?.message || 'Error al actualizar seguro';
    }
  },

  async deleteSeguro(id) {
    try {
      const response = await seguroRepository.delete(id);
      return response.data;
    } catch (error) {
      throw error.response?.data?.message || 'Error al eliminar seguro';
    }
  }
};
