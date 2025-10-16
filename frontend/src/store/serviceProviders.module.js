import serviceProvidersService from "../services/service-providers.service";

const initialState = { providers: null };

export const serviceProviders = {
  namespaced: true,
  state: initialState,
  actions: {
    async getProviders({ commit }, params) {
      const response = await serviceProvidersService.getProviders(params);
      commit("SET_PROVIDERS", response);
    },

    async getProvider(_, { id, params }) {
      return serviceProvidersService.getProvider(id, params);
    },

    async addProvider(_, payload) {
      return serviceProvidersService.addProvider(payload);
    },

    async editProvider(_, payload) {
      return serviceProvidersService.editProvider(payload);
    },

    async deleteProvider(_, id) {
      return serviceProvidersService.deleteProvider(id);
    },
  },

  mutations: {
    SET_PROVIDERS(state, providers) {
      state.providers = providers;
    },
  },

  getters: {
    providers(state) {
      return state.providers;
    },
  },
};

