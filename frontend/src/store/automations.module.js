import automationsService from "../services/automations.service";

const initialState = { automations: null };

export const automations = {
  namespaced: true,
  state: initialState,
  actions: {
    async getAutomations({ commit }, params) {
      const response = await automationsService.getAutomations(params);
      commit("SET_AUTOMATIONS", response);
    },

    async getAutomation(_, payload) {
      return await automationsService.getAutomation(payload);
    },

    async deleteAutomation(_, id) {
      await automationsService.deleteAutomation(id);
    },

    async addAutomation(_, automation) {
      return await automationsService.addAutomation(automation);
    },

    async editAutomation(_, automation) {
      return await automationsService.editAutomation(automation);
    },
  },

  mutations: {
    SET_AUTOMATIONS(state, automations) {
      state.automations = automations;
    },
  },

  getters: {
    automations(state) {
      return state.automations;
    },
  },
};

