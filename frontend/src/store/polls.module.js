import PollsService from "../services/polls.service";

export const polls = {
  namespaced: true,
  state: {
    polls: null,
  },
  actions: {
    async getPolls({ commit }, params) {
      const data = await PollsService.getPolls(params);
      commit("SET_POLLS", data);
      return data;
    },
    async getPoll(_, payload) {
      return await PollsService.getPoll(payload);
    },
    async deletePoll(_, id) {
      await PollsService.deletePoll(id);
    },
    async addPoll(_, poll) {
      return await PollsService.addPoll(poll);
    },
    async editPoll(_, poll) {
      return await PollsService.editPoll(poll);
    },
  },
  mutations: {
    SET_POLLS(state, data) {
      state.polls = data;
    },
  },
  getters: {
    polls: (state) => state.polls,
  },
};

