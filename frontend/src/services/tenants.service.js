import axios from "axios";
import authHeader from "./auth-header";
import Jsona from "jsona";
const dataFormatter = new Jsona();

const API_URL = process.env.VUE_APP_API_BASE_URL + '/';

export default {
  async getTenants(params) {
    const response = await axios.get(API_URL + "tenants", {
      headers: authHeader(),
      params: {
        ...(params?.filter?.name ? { "filter[name]": params.filter.name } : {}),
        ...(params?.filter?.fiscal_code ? { "filter[fiscal_code]": params.filter.fiscal_code } : {}),
        ...(params?.page?.size ? { "page[size]": params.page.size } : {}),
        ...(params?.page?.number ? { "page[number]": params.page.number } : {}),
        ...(params?.sort ? { sort: params.sort } : {}),
      },
    });
    return {
      data: dataFormatter.deserialize(response.data),
      meta: response.data.meta,
    };
  },

  async getTenant(id) {
    const response = await axios.get(API_URL + "tenants/" + id, {
      headers: authHeader(),
    });
    return dataFormatter.deserialize(response.data);
  },

  async deleteTenant(id) {
    await axios.delete(API_URL + "tenants/" + id.toString(), {
      headers: authHeader(),
    });
  },

  async addTenant(tenant) {
    tenant.type = "tenants";
    return await axios.post(
      API_URL + "tenants",
      dataFormatter.serialize({ stuff: tenant }),
      {
        headers: authHeader(),
      }
    );
  },

  async editTenant(tenant) {
    const editedTenant = {
      id: tenant.id,
      name: tenant.name,
      address: tenant.address,
      fiscal_code: tenant.fiscal_code,
      description: tenant.description,
      contact_data: tenant.contact_data,
      type: "tenants",
    };
    return await axios.patch(
      API_URL + "tenants/" + editedTenant.id,
      dataFormatter.serialize({ stuff: editedTenant }),
      {
        headers: authHeader(),
      }
    );
  },
};

