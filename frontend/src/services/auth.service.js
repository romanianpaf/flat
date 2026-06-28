import axios from "axios";
import authHeader from "./auth-header";

const API_URL = import.meta.env.VITE_API_BASE_URL + '/';

export default {
  async login(user) {
    const response = await axios.post(API_URL + "login", user);
    console.log("Login response:", response.data);
    if (response.data.access_token) {
      // Salvez token-ul
      localStorage.setItem("token", response.data.access_token);
      if (response.data.refresh_token) {
        localStorage.setItem("refresh_token", response.data.refresh_token);
      }

      // Fetch user profile cu roles și permissions
      // Folosesc token-ul direct pentru a evita timing issues cu authHeader()
      const profileResponse = await axios.get(API_URL + "me", {
        headers: {
          Authorization: `Bearer ${response.data.access_token}`,
          Accept: "application/vnd.api+json",
        },
      });
      console.log("Profile response:", profileResponse.data);
      
      const profileData = profileResponse.data.data;
      const userData = {
        id: profileData.id,
        first_name: profileData.first_name,
        last_name: profileData.last_name,
        name: `${profileData.first_name || ''} ${profileData.last_name || ''}`.trim(),
        email: profileData.email,
        tenant_id: profileData.tenant_id,
        tenant: profileData.tenant,
        is_system_admin: profileData.is_system_admin || false,
        active_tenant_id: profileData.active_tenant_id ?? null,
        roles: profileData.roles || [],
        permissions: profileData.permissions || [],
      };
      console.log("Saving user to localStorage:", userData);
      console.log("Roles:", userData.roles);
      console.log("Permissions count:", userData.permissions.length);
      localStorage.setItem("user", JSON.stringify(userData));
    }
  },

  async logout() {
    try {
      await axios.post(API_URL + "logout", {}, { headers: authHeader() });
    } finally {
      localStorage.removeItem("user");
      localStorage.removeItem("token");
      localStorage.removeItem("refresh_token");
    }
  },

  async register(user) {
    const response = await axios.post(API_URL + "register", user);
    if (response.data.access_token) {
      // Salvez user cu roles si token
      localStorage.setItem("user", JSON.stringify({
        id: 1, // Will be updated by profile module
        name: user.email,
        email: user.email,
        roles: [],
      }));
      localStorage.setItem("token", response.data.access_token);
      if (response.data.refresh_token) {
        localStorage.setItem("refresh_token", response.data.refresh_token);
      }
    }
  },
};
