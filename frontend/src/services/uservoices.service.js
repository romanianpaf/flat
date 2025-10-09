import axios from "axios";
import authHeader from "./auth-header";
import Jsona from "jsona";
const dataFormatter = new Jsona();

const API_URL = process.env.VUE_APP_API_BASE_URL + '/';

export default {
  async getUserVoices(params) {
    try {
      const response = await axios.get(API_URL + "user-voices", {
        headers: authHeader(),
        params: {
          ...(params?.filter?.is_active ? { "filter[is_active]": params.filter.is_active } : {}),
          ...(params?.page?.size ? { "page[size]": params.page.size } : {}),
          ...(params?.page?.number ? { "page[number]": params.page.number } : {}),
          ...(params?.sort ? { sort: params.sort } : {}),
          ...(params?.include ? { include: params.include } : { include: 'user' }),
        },
      });
      return {
        data: dataFormatter.deserialize(response.data),
        meta: response.data.meta,
      };
    } catch (error) {
      console.error('Error fetching user voices:', error);
      throw error;
    }
  },

  async getUserVoice(payload) {
    const id = typeof payload === 'object' ? payload.id : payload;
    const params = typeof payload === 'object' ? payload.params : {};
    
    const response = await axios.get(API_URL + "user-voices/" + id, {
      headers: authHeader(),
      params: {
        ...(params?.include ? { include: params.include } : { include: 'user' }),
      },
    });
    return dataFormatter.deserialize(response.data);
  },

  async deleteUserVoice(id) {
    await axios.delete(API_URL + "user-voices/" + id.toString(), {
      headers: authHeader(),
    });
  },

  async addUserVoice(userVoice) {
    const payload = {
      data: {
        type: "user-voices",
        attributes: {
          suggestion: userVoice.suggestion,
        },
      },
    };
    
    const response = await axios.post(
      API_URL + "user-voices",
      payload,
      {
        headers: authHeader(),
      }
    );
    
    return response;
  },

  async editUserVoice(userVoice) {
    const payload = {
      data: {
        type: "user-voices",
        id: userVoice.id.toString(),
        attributes: {
          suggestion: userVoice.suggestion,
        },
      },
    };
    
    return await axios.patch(
      API_URL + "user-voices/" + userVoice.id,
      payload,
      {
        headers: authHeader(),
      }
    );
  },

  async voteUserVoice(id, type) {
    return await axios.post(
      API_URL + "user-voices/" + id + "/vote",
      { type },
      {
        headers: authHeader(),
      }
    );
  },
};

