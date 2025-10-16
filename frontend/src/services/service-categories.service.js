import axios from "axios";
import authHeader from "./auth-header";
import Jsona from "jsona";

const dataFormatter = new Jsona();
const API_URL = process.env.VUE_APP_API_BASE_URL + "/";

export default {
  async getCategories(params = {}) {
    const response = await axios.get(API_URL + "service-categories", {
      headers: authHeader(),
      params: {
        ...(params?.filter?.name ? { "filter[name]": params.filter.name } : {}),
        ...(params?.page?.size ? { "page[size]": params.page.size } : {}),
        ...(params?.page?.number ? { "page[number]": params.page.number } : {}),
        ...(params?.sort ? { sort: params.sort } : {}),
        include: params?.include,
      },
    });

    return {
      data: dataFormatter.deserialize(response.data),
      meta: response.data.meta,
    };
  },

  async getCategory(id, params = {}) {
    const response = await axios.get(`${API_URL}service-categories/${id}`, {
      headers: authHeader(),
      params: {
        include: params?.include,
      },
    });

    return dataFormatter.deserialize(response.data);
  },

  async addCategory(category) {
    const payload = {
      ...category,
      type: "service-categories",
    };

    return axios.post(
      API_URL + "service-categories",
      dataFormatter.serialize({ stuff: payload }),
      {
        headers: authHeader(),
      }
    );
  },

  async editCategory(category) {
    const payload = {
      id: category.id,
      name: category.name,
      description: category.description,
      type: "service-categories",
    };

    return axios.patch(
      `${API_URL}service-categories/${payload.id}`,
      dataFormatter.serialize({ stuff: payload }),
      {
        headers: authHeader(),
      }
    );
  },

  async deleteCategory(id) {
    return axios.delete(`${API_URL}service-categories/${id}`, {
      headers: authHeader(),
    });
  },
};
