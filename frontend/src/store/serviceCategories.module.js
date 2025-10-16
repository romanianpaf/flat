import serviceCategoriesService from "../services/service-categories.service";

const initialState = { categories: null };

export const serviceCategories = {
  namespaced: true,
  state: initialState,
  actions: {
    async getCategories({ commit }, params) {
      const response = await serviceCategoriesService.getCategories(params);
      commit("SET_CATEGORIES", response);
    },

    async getCategory(_, { id, params }) {
      return serviceCategoriesService.getCategory(id, params);
    },

    async addCategory(_, payload) {
      return serviceCategoriesService.addCategory(payload);
    },

    async editCategory(_, payload) {
      return serviceCategoriesService.editCategory(payload);
    },

    async deleteCategory(_, id) {
      return serviceCategoriesService.deleteCategory(id);
    },
  },

  mutations: {
    SET_CATEGORIES(state, categories) {
      state.categories = categories;
    },
  },

  getters: {
    categories(state) {
      return state.categories;
    },
  },
};

