import axios from "axios";
import authHeader from "./auth-header";

const API_URL = process.env.VUE_APP_API_BASE_URL + '/';

export default {
  async login(user) {
    const response = await axios.post(API_URL + "login", user);
    console.log("Login response:", response.data);
    if (response.data.access_token) {
      // Salvez token temporar
      localStorage.setItem("token", response.data.access_token);
      if (response.data.refresh_token) {
        localStorage.setItem("refresh_token", response.data.refresh_token);
      }

      // Fetch user profile cu roles și permissions
      const profileResponse = await axios.get(API_URL + "me", {
        headers: authHeader(),
      });
      console.log("Profile response:", profileResponse.data);
      
      const userData = {
        id: profileResponse.data.data.id,
        name: profileResponse.data.data.name,
        email: profileResponse.data.data.email,
        roles: profileResponse.data.data.roles || [],
        permissions: profileResponse.data.data.permissions || [],
      };
      console.log("Saving user to localStorage:", userData);
      localStorage.setItem("user", JSON.stringify(userData));
      console.log("Saved user:", localStorage.getItem("user"));
      console.log("Permissions count:", userData.permissions.length);
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
