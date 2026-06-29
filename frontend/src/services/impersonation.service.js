import axios from "axios";
import authHeader from "./auth-header";

const API_URL = import.meta.env.VITE_API_BASE_URL + "/";

export default {
  // Userii care pot fi impersonați (doar sysadmin)
  async getCandidates() {
    const res = await axios.get(API_URL + "impersonate/candidates", { headers: authHeader() });
    return res?.data;
  },
};
