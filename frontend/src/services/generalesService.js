import { generalesRepository } from '../repositories/generalesRepository';

export const generalesService = {
  // Roles
  async getRoles() {
    try {
      const response = await generalesRepository.getRoles();
      return response.data;
    } catch (error) {
      throw error.response?.data?.message || 'Error al obtener roles';
    }
  },
  async getRol(id) {
    try {
      const response = await generalesRepository.getRol(id);
      return response.data;
    } catch (error) {
      throw error.response?.data?.message || 'Error al obtener el rol';
    }
  },
  async createRol(data) {
    try {
      const response = await generalesRepository.createRol(data);
      return response.data;
    } catch (error) {
      throw error.response?.data?.message || 'Error al crear rol';
    }
  },
  async updateRol(id, data) {
    try {
      const response = await generalesRepository.updateRol(id, data);
      return response.data;
    } catch (error) {
      throw error.response?.data?.message || 'Error al actualizar rol';
    }
  },
  async deleteRol(id) {
    try {
      const response = await generalesRepository.deleteRol(id);
      return response.data;
    } catch (error) {
      throw error.response?.data?.message || 'Error al eliminar rol';
    }
  },

  // Permisos
  async getPermisos() {
    try {
      const response = await generalesRepository.getPermisos();
      return response.data;
    } catch (error) {
      throw error.response?.data?.message || 'Error al obtener permisos';
    }
  },

  // Empleados
  async getEmpleados() {
    try {
      const response = await generalesRepository.getEmpleados();
      return response.data;
    } catch (error) {
      throw error.response?.data?.message || 'Error al obtener colaboradores';
    }
  },
  async getEmpleado(id) {
    try {
      const response = await generalesRepository.getEmpleado(id);
      return response.data;
    } catch (error) {
      throw error.response?.data?.message || 'Error al obtener el colaborador';
    }
  },
  async createEmpleado(data) {
    try {
      const response = await generalesRepository.createEmpleado(data);
      return response.data;
    } catch (error) {
      throw error.response?.data?.message || 'Error al crear colaborador';
    }
  },
  async updateEmpleado(id, data) {
    try {
      const response = await generalesRepository.updateEmpleado(id, data);
      return response.data;
    } catch (error) {
      throw error.response?.data?.message || 'Error al actualizar colaborador';
    }
  },
  async deleteEmpleado(id) {
    try {
      const response = await generalesRepository.deleteEmpleado(id);
      return response.data;
    } catch (error) {
      throw error.response?.data?.message || 'Error al eliminar colaborador';
    }
  },

  // Clientes
  async getClientes() {
    try {
      const response = await generalesRepository.getClientes();
      return response.data;
    } catch (error) {
      throw error.response?.data?.message || 'Error al obtener clientes';
    }
  },
  async getClienteByDocumento(documento) {
    try {
      const response = await generalesRepository.getClienteByDocumento(documento);
      return response.data;
    } catch (error) {
      throw error.response?.data?.message || 'Error al buscar cliente por documento';
    }
  },
  async getCliente(id) {
    try {
      const response = await generalesRepository.getCliente(id);
      return response.data;
    } catch (error) {
      throw error.response?.data?.message || 'Error al obtener el cliente';
    }
  },
  async createCliente(data) {
    try {
      const response = await generalesRepository.createCliente(data);
      return response.data;
    } catch (error) {
      throw error.response?.data?.message || 'Error al crear cliente';
    }
  },
  async updateCliente(id, data) {
    try {
      const response = await generalesRepository.updateCliente(id, data);
      return response.data;
    } catch (error) {
      throw error.response?.data?.message || 'Error al actualizar cliente';
    }
  },
  async deleteCliente(id) {
    try {
      const response = await generalesRepository.deleteCliente(id);
      return response.data;
    } catch (error) {
      throw error.response?.data?.message || 'Error al eliminar cliente';
    }
  }
};
