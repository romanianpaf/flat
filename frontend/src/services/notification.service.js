import axios from "axios";
import authHeader from "./auth-header";

const API_URL = import.meta.env.VITE_API_BASE_URL + "/";

export default {
  async list() {
    const res = await axios.get(API_URL + "notifications", { headers: authHeader() });
    return res?.data;
  },
  async markRead(id) {
    const res = await axios.post(API_URL + `notifications/${id}/read`, {}, { headers: authHeader() });
    return res?.data;
  },
  async markAllRead() {
    const res = await axios.post(API_URL + "notifications/read-all", {}, { headers: authHeader() });
    return res?.data;
  },
};
