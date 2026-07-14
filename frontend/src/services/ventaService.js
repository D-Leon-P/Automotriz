import { ventaRepository } from '../repositories/ventaRepository';

export const ventaService = {
  async getVentas(showDeleted = false) {
    try {
      const response = await ventaRepository.getAll(showDeleted);
      return response.data;
    } catch (error) {
      throw error.response?.data?.message || 'Error al obtener ventas';
    }
  },

  async getVenta(id) {
    try {
      const response = await ventaRepository.getById(id);
      return response.data;
    } catch (error) {
      throw error.response?.data?.message || 'Error al obtener la venta';
    }
  },

  async createVenta(data) {
    try {
      const response = await ventaRepository.create(data);
      return response.data;
    } catch (error) {
      throw error.response?.data?.message || 'Error al registrar venta';
    }
  },

  async updateVenta(id, data) {
    try {
      const response = await ventaRepository.update(id, data);
      return response.data;
    } catch (error) {
      throw error.response?.data?.message || 'Error al actualizar venta';
    }
  },

  async deleteVenta(id) {
    try {
      const response = await ventaRepository.delete(id);
      return response.data;
    } catch (error) {
      throw error.response?.data?.message || 'Error al eliminar venta';
    }
  },

  async restoreVenta(id) {
    try {
      const response = await ventaRepository.restore(id);
      return response.data;
    } catch (error) {
      throw error.response?.data?.message || 'Error al reintegrar venta';
    }
  }
};
