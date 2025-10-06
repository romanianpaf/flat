import tenantsService from "../services/tenants.service";

const initialState = { tenants: null };

export const tenants = {
  namespaced: true,
  state: initialState,
  actions: {
    async getTenants({ commit }, params) {
      const response = await tenantsService.getTenants(params);
      commit("SET_TENANTS", response);
    },

    async getTenant(_, id) {
      return await tenantsService.getTenant(id);
    },

    async deleteTenant(_, id) {
      await tenantsService.deleteTenant(id);
    },

    async addTenant(_, tenant) {
      return await tenantsService.addTenant(tenant);
    },

    async editTenant(_, tenant) {
      return await tenantsService.editTenant(tenant);
    },
  },

  mutations: {
    SET_TENANTS(state, tenants) {
      state.tenants = tenants;
    },
  },

  getters: {
    tenants(state) {
      return state.tenants;
    },
  },
};

