import axios from "axios";
import authHeader from "./auth-header";
import Jsona from "jsona";

const dataFormatter = new Jsona();
const API_URL = process.env.VUE_APP_API_BASE_URL + "/";

export default {
  async getProviders(params = {}) {
    const response = await axios.get(API_URL + "service-providers", {
      headers: authHeader(),
      params: {
        ...(params?.filter?.service_category_id
          ? { "filter[service_category_id]": params.filter.service_category_id }
          : {}),
        ...(params?.filter?.provider_type ? { "filter[provider_type]": params.filter.provider_type } : {}),
        ...(params?.filter?.is_published !== undefined
          ? { "filter[is_published]": params.filter.is_published }
          : {}),
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

  async getProvider(id, params = {}) {
    const response = await axios.get(`${API_URL}service-providers/${id}`, {
      headers: authHeader(),
      params: {
        include: params?.include,
      },
    });

    return dataFormatter.deserialize(response.data);
  },

  async addProvider(provider) {
    const payload = {
      ...provider,
      type: "service-providers",
    };

    return axios.post(
      API_URL + "service-providers",
      dataFormatter.serialize({ stuff: payload }),
      {
        headers: authHeader(),
      }
    );
  },

  async editProvider(provider) {
    const payload = {
      id: provider.id,
      service_category_id: provider.service_category_id,
      service_subcategory_id: provider.service_subcategory_id,
      provider_type: provider.provider_type,
      first_name: provider.first_name,
      last_name: provider.last_name,
      company_name: provider.company_name,
      phone: provider.phone,
      email: provider.email,
      photo_path: provider.photo_path,
      service_description: provider.service_description,
      is_published: provider.is_published,
      type: "service-providers",
    };

    return axios.patch(
      `${API_URL}service-providers/${payload.id}`,
      dataFormatter.serialize({ stuff: payload }),
      {
        headers: authHeader(),
      }
    );
  },

  async deleteProvider(id) {
    return axios.delete(`${API_URL}service-providers/${id}`, {
      headers: authHeader(),
    });
  },
};


