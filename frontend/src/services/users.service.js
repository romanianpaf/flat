import axios from "axios";
import authHeader from "./auth-header";
import Jsona from "jsona";
const dataFormatter = new Jsona();

const API_URL = import.meta.env.VITE_API_BASE_URL + '/';

export default {
  async getUsers(params) {
    const response = await axios.get(API_URL + "users", {
      headers: authHeader(),
      params: {
        ...(params?.include ? { include: params.include } : { include: "roles" }),
        ...(params?.filter?.name ? { "filter[name]": params.filter.name } : {}),
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

  async getUser(id) {
    const response = await axios.get(API_URL + "users/" + id, {
      headers: authHeader(),
      params: {
        include: "roles,tenant",
      },
    });
    return dataFormatter.deserialize(response.data);
  },

  async deleteUser(id) {
    await axios.delete(API_URL + "users/" + id.toString(), {
      headers: authHeader(),
    });
  },

  async addUser(user) {
    const payload = {
      data: {
        type: "users",
        attributes: {
          first_name: user.first_name,
          last_name: user.last_name,
          email: user.email,
          password: user.password,
          password_confirmation: user.password_confirmation,
          phone: user.phone || null,
          tenant_id: user.tenant_id || null,
        },
        relationships: {},
      },
    };

    // Adăugăm roluri dacă există
    if (user.roles && user.roles.length > 0) {
      payload.data.relationships.roles = {
        data: user.roles.map(role => ({
          type: "roles",
          id: String(role.id),
        })),
      };
    }

    return await axios.post(API_URL + "users", payload, {
      headers: authHeader(),
    });
  },

  async editUser(user) {
    // Construim payload-ul manual pentru a ne asigura că toate câmpurile sunt incluse
    const payload = {
      data: {
        type: "users",
        id: String(user.id),
        attributes: {
          first_name: user.first_name || null,
          last_name: user.last_name || null,
          email: user.email,
          phone: user.phone || null,
          apartment: user.apartment || null,
          staircase: user.staircase || null,
          floor: user.floor || null,
        },
        relationships: {},
      },
    };

    // Adăugăm parola doar dacă este setată
    if (user.password) {
      payload.data.attributes.password = user.password;
      payload.data.attributes.password_confirmation = user.password_confirmation;
    }

    // Adăugăm profile_image dacă există
    if (user.profile_image) {
      payload.data.attributes.profile_image = user.profile_image;
    }

    // Adăugăm roluri dacă există
    if (user.roles && user.roles.length > 0) {
      payload.data.relationships.roles = {
        data: user.roles.map(role => ({
          type: "roles",
          id: String(role.id),
        })),
      };
    }

    return await axios.patch(
      API_URL + "users/" + user.id,
      payload,
      {
        headers: authHeader(),
      }
    );
  },
};
