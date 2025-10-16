import serviceSubcategoriesService from "../services/service-subcategories.service";

const initialState = { subcategories: null };

export const serviceSubcategories = {
  namespaced: true,
  state: initialState,
  actions: {
    async getSubcategories({ commit }, params) {
      const response = await serviceSubcategoriesService.getSubcategories(params);
      commit("SET_SUBCATEGORIES", response);
    },

    async getSubcategory(_, { id, params }) {
      return serviceSubcategoriesService.getSubcategory(id, params);
    },

    async addSubcategory(_, payload) {
      return serviceSubcategoriesService.addSubcategory(payload);
    },

    async editSubcategory(_, payload) {
      return serviceSubcategoriesService.editSubcategory(payload);
    },

    async deleteSubcategory(_, id) {
      return serviceSubcategoriesService.deleteSubcategory(id);
    },
  },

  mutations: {
    SET_SUBCATEGORIES(state, subcategories) {
      state.subcategories = subcategories;
    },
  },

  getters: {
    subcategories(state) {
      return state.subcategories;
    },
  },
};

