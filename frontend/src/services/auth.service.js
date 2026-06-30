import axios from "axios";
import authHeader from "./auth-header";

const API_URL = import.meta.env.VITE_API_BASE_URL + '/';

// Fetch /me cu un token dat și salvează userul în localStorage. Reutilizat la
// login, refresh de token și impersonare.
async function fetchAndStoreProfile(token) {
  const profileResponse = await axios.get(API_URL + "me", {
    headers: {
      Authorization: `Bearer ${token}`,
      Accept: "application/vnd.api+json",
    },
  });
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
    needs_carte_imobil: profileData.needs_carte_imobil || false,
    roles: profileData.roles || [],
    permissions: profileData.permissions || [],
  };
  localStorage.setItem("user", JSON.stringify(userData));
  return userData;
}

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
      await fetchAndStoreProfile(response.data.access_token);
    }
  },

  /**
   * Pornește impersonarea unui user (doar sysadmin). Păstrează identitatea
   * curentă pentru revenire, comută pe token-ul de impersonare și reîncarcă /me.
   */
  async impersonate(userId) {
    const currentToken = localStorage.getItem("token");
    const res = await axios.post(
      API_URL + `impersonate/${userId}`,
      {},
      { headers: { Authorization: `Bearer ${currentToken}`, Accept: "application/vnd.api+json" } }
    );

    const newToken = res.data?.data?.access_token;
    if (!newToken) throw new Error("Nu am primit token de impersonare.");

    // Păstrează identitatea sysadminului pentru "Revino la contul meu".
    localStorage.setItem("impersonator_token", currentToken);
    localStorage.setItem("impersonator_user", localStorage.getItem("user") || "");

    // Comută pe token-ul de impersonare și încarcă profilul userului impersonat.
    localStorage.setItem("token", newToken);
    await fetchAndStoreProfile(newToken);
  },

  /**
   * Oprește impersonarea: revocă token-ul de impersonare (best-effort) și
   * restaurează identitatea sysadminului.
   */
  async leaveImpersonation() {
    const impToken = localStorage.getItem("token");
    try {
      await axios.post(
        API_URL + "impersonate-leave",
        {},
        { headers: { Authorization: `Bearer ${impToken}`, Accept: "application/vnd.api+json" } }
      );
    } catch (e) {
      console.warn("Impersonate-leave call failed (continuăm cu restaurarea locală):", e);
    }

    const origToken = localStorage.getItem("impersonator_token");
    const origUser = localStorage.getItem("impersonator_user");
    if (origToken) localStorage.setItem("token", origToken);
    if (origUser) localStorage.setItem("user", origUser);
    localStorage.removeItem("impersonator_token");
    localStorage.removeItem("impersonator_user");
  },

  isImpersonating() {
    return !!localStorage.getItem("impersonator_token");
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
