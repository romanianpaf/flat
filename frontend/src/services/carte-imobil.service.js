import axios from "axios";
import authHeader from "./auth-header";

const API_URL = import.meta.env.VITE_API_BASE_URL + "/";

function unwrap(response) {
  return response?.data;
}

export default {
  async myApartments() {
    const res = await axios.get(API_URL + "my-apartments", { headers: authHeader() });
    return unwrap(res);
  },

  async getOccupants(apartmentId) {
    const res = await axios.get(API_URL + `apartments/${apartmentId}/occupants`, { headers: authHeader() });
    return unwrap(res);
  },

  async addOccupant(apartmentId, payload) {
    const res = await axios.post(API_URL + `apartments/${apartmentId}/occupants`, payload, { headers: authHeader() });
    return unwrap(res);
  },

  async updateOccupant(occupantId, payload) {
    const res = await axios.put(API_URL + `occupants/${occupantId}`, payload, { headers: authHeader() });
    return unwrap(res);
  },

  async deleteOccupant(occupantId) {
    const res = await axios.delete(API_URL + `occupants/${occupantId}`, { headers: authHeader() });
    return unwrap(res);
  },

  async submit(apartmentId) {
    const res = await axios.post(API_URL + `apartments/${apartmentId}/occupants/submit`, {}, { headers: authHeader() });
    return unwrap(res);
  },

  async approve(apartmentId) {
    const res = await axios.post(API_URL + `apartments/${apartmentId}/occupants/approve`, {}, { headers: authHeader() });
    return unwrap(res);
  },

  async reject(apartmentId, reason) {
    const res = await axios.post(
      API_URL + `apartments/${apartmentId}/occupants/reject`,
      { reason },
      { headers: authHeader() }
    );
    return unwrap(res);
  },

  async submissions() {
    const res = await axios.get(API_URL + "occupants/submissions", { headers: authHeader() });
    return unwrap(res);
  },

  async exportPdf(apartmentId) {
    const res = await axios.get(API_URL + `apartments/${apartmentId}/occupants/export.pdf`, {
      headers: authHeader(),
      responseType: "blob",
    });
    return res;
  },

  // Config imobil (admin/cex/sysadmin)
  async getTenantBuilding(tenantId = null) {
    const params = tenantId ? { tenant_id: tenantId } : {};
    const res = await axios.get(API_URL + "tenant-building", { headers: authHeader(), params });
    return unwrap(res);
  },

  // Lista tenanți pentru sysadmin
  async getTenantsList() {
    const res = await axios.get(API_URL + "tenant-building/tenants", { headers: authHeader() });
    return unwrap(res);
  },

  async addStaircase(payload) {
    const res = await axios.post(API_URL + "tenant-building/staircases", payload, { headers: authHeader() });
    return unwrap(res);
  },

  async updateStaircase(id, payload) {
    const res = await axios.put(API_URL + `tenant-building/staircases/${id}`, payload, { headers: authHeader() });
    return unwrap(res);
  },

  async deleteStaircase(id) {
    const res = await axios.delete(API_URL + `tenant-building/staircases/${id}`, { headers: authHeader() });
    return unwrap(res);
  },

  async syncApartments(staircaseId, numbers, removeMissing = false) {
    const res = await axios.post(
      API_URL + `tenant-building/staircases/${staircaseId}/apartments/sync`,
      { numbers, remove_missing: removeMissing },
      { headers: authHeader() }
    );
    return unwrap(res);
  },

  // Building options for dropdowns (accessible by all users in tenant)
  async getBuildingOptions() {
    const res = await axios.get(API_URL + "tenant-building/options", { headers: authHeader() });
    return unwrap(res);
  },

  // Apartments list with floor info (admin only)
  async getApartmentsList(tenantId = null) {
    const params = tenantId ? { tenant_id: tenantId } : {};
    const res = await axios.get(API_URL + "tenant-building/apartments", { headers: authHeader(), params });
    return unwrap(res);
  },

  // Update apartment floor
  async updateApartmentFloor(apartmentId, floor) {
    const res = await axios.put(
      API_URL + `tenant-building/apartments/${apartmentId}/floor`,
      { floor },
      { headers: authHeader() }
    );
    return unwrap(res);
  },
};

