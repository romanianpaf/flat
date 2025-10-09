import UserVoicesService from "../services/uservoices.service";

export const uservoices = {
  namespaced: true,
  state: {
    uservoices: null,
  },
  actions: {
    async getUserVoices({ commit }, params) {
      try {
        const data = await UserVoicesService.getUserVoices(params);
        commit("SET_USERVOICES", data);
        return data;
      } catch (error) {
        console.error('Error in uservoices store:', error);
        throw error;
      }
    },
    async getUserVoice(_, payload) {
      return await UserVoicesService.getUserVoice(payload);
    },
    async deleteUserVoice(_, id) {
      await UserVoicesService.deleteUserVoice(id);
    },
    async addUserVoice(_, uservoice) {
      return await UserVoicesService.addUserVoice(uservoice);
    },
    async editUserVoice(_, uservoice) {
      return await UserVoicesService.editUserVoice(uservoice);
    },
    async voteUserVoice(_, { id, type }) {
      return await UserVoicesService.voteUserVoice(id, type);
    },
  },
  mutations: {
    SET_USERVOICES(state, data) {
      state.uservoices = data;
    },
  },
  getters: {
    uservoices: (state) => state.uservoices,
  },
};

