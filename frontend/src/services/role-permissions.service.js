import axios from "axios";
import authHeader from "./auth-header";

const API_URL = process.env.VUE_APP_API_BASE_URL + "/";

class RolePermissionsService {
  /**
   * Obține toate permisiunile disponibile
   */
  async getAvailablePermissions() {
    return axios.get(`${API_URL}available-permissions`, {
      headers: authHeader(),
    });
  }

  /**
   * Obține permisiunile unui rol
   */
  async getRolePermissions(roleId) {
    return axios.get(`${API_URL}roles/${roleId}?include=permissions`, {
      headers: authHeader(),
    });
  }

  /**
   * Sincronizează permisiunile unui rol
   */
  async syncRolePermissions(roleId, permissions) {
    return axios.post(
      `${API_URL}roles/${roleId}/sync-permissions`,
      { permissions },
      { headers: authHeader() }
    );
  }
}

export default new RolePermissionsService();

