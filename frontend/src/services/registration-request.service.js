import axios from "axios";
import authHeader from "./auth-header";

const API_URL = import.meta.env.VITE_API_BASE_URL + "/";

function unwrap(response) {
  return response?.data;
}

export default {
  // Public endpoints (no auth required)
  async getPublicTenants() {
    const res = await axios.get(API_URL + "tenants/public");
    return unwrap(res);
  },

  async getPublicApartments(tenantId) {
    const res = await axios.get(API_URL + `tenants/${tenantId}/apartments/public`);
    return unwrap(res);
  },

  async submitRequest(formData) {
    const res = await axios.post(API_URL + "registration-requests", formData, {
      headers: {
        "Content-Type": "multipart/form-data",
      },
    });
    return unwrap(res);
  },

  // Protected endpoints (auth required)
  async getRequests(params = {}) {
    const res = await axios.get(API_URL + "registration-requests", {
      headers: authHeader(),
      params,
    });
    return unwrap(res);
  },

  async getRequest(id) {
    const res = await axios.get(API_URL + `registration-requests/${id}`, {
      headers: authHeader(),
    });
    return unwrap(res);
  },

  async downloadDocument(id) {
    const res = await axios.get(API_URL + `registration-requests/${id}/document`, {
      headers: authHeader(),
      responseType: "blob",
    });
    return res;
  },

  async approveRequest(id) {
    const res = await axios.post(
      API_URL + `registration-requests/${id}/approve`,
      {},
      { headers: authHeader() }
    );
    return unwrap(res);
  },

  async rejectRequest(id, reason = null) {
    const res = await axios.post(
      API_URL + `registration-requests/${id}/reject`,
      { reason },
      { headers: authHeader() }
    );
    return unwrap(res);
  },
};
