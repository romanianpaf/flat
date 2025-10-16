import axios from "axios";
import authHeader from "./auth-header";
import Jsona from "jsona";

const dataFormatter = new Jsona();
const API_URL = process.env.VUE_APP_API_BASE_URL + "/";

export default {
  async getSubcategories(params = {}) {
    const response = await axios.get(API_URL + "service-subcategories", {
      headers: authHeader(),
      params: {
        ...(params?.filter?.name ? { "filter[name]": params.filter.name } : {}),
        ...(params?.filter?.service_category_id
          ? { "filter[service_category_id]": params.filter.service_category_id }
          : {}),
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

  async getSubcategory(id, params = {}) {
    const response = await axios.get(`${API_URL}service-subcategories/${id}`, {
      headers: authHeader(),
      params: {
        include: params?.include,
      },
    });

    return dataFormatter.deserialize(response.data);
  },

  async addSubcategory(subcategory) {
    const payload = {
      ...subcategory,
      type: "service-subcategories",
    };

    return axios.post(
      API_URL + "service-subcategories",
      dataFormatter.serialize({ stuff: payload }),
      {
        headers: authHeader(),
      }
    );
  },

  async editSubcategory(subcategory) {
    const payload = {
      id: subcategory.id,
      name: subcategory.name,
      description: subcategory.description,
      service_category_id: subcategory.service_category_id,
      type: "service-subcategories",
    };

    return axios.patch(
      `${API_URL}service-subcategories/${payload.id}`,
      dataFormatter.serialize({ stuff: payload }),
      {
        headers: authHeader(),
      }
    );
  },

  async deleteSubcategory(id) {
    return axios.delete(`${API_URL}service-subcategories/${id}`, {
      headers: authHeader(),
    });
  },
};


