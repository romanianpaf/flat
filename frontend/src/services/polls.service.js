import axios from "axios";
import authHeader from "./auth-header";
import Jsona from "jsona";
const dataFormatter = new Jsona();

const API_URL = process.env.VUE_APP_API_BASE_URL + '/';

export default {
  async getPolls(params) {
    try {
      const response = await axios.get(API_URL + "polls", {
        headers: authHeader(),
        params: {
          ...(params?.filter?.title ? { "filter[title]": params.filter.title } : {}),
          ...(params?.filter?.is_active ? { "filter[is_active]": params.filter.is_active } : {}),
          ...(params?.page?.size ? { "page[size]": params.page.size } : {}),
          ...(params?.page?.number ? { "page[number]": params.page.number } : {}),
          ...(params?.sort ? { sort: params.sort } : {}),
          ...(params?.include ? { include: params.include } : {}),
        },
      });
      return {
        data: dataFormatter.deserialize(response.data),
        meta: response.data.meta,
      };
    } catch (error) {
      console.error('Error fetching polls:', error);
      throw error;
    }
  },

  async getPoll(payload) {
    const id = typeof payload === 'object' ? payload.id : payload;
    const params = typeof payload === 'object' ? payload.params : {};

    const response = await axios.get(API_URL + "polls/" + id, {
      headers: authHeader(),
      params: {
        ...(params?.include ? { include: params.include } : {}),
      },
    });
    return dataFormatter.deserialize(response.data);
  },

  async deletePoll(id) {
    await axios.delete(API_URL + "polls/" + id.toString(), {
      headers: authHeader(),
    });
  },

  async addPoll(poll) {
    // Pas 1: Creează poll-ul
    const pollPayload = {
      data: {
        type: "polls",
        attributes: {
          title: poll.title,
          description: poll.description,
          is_active: poll.is_active,
          allow_multiple_votes: poll.allow_multiple_votes,
          start_date: poll.start_date,
          end_date: poll.end_date,
          tenant_id: poll.tenant_id,
        },
      },
    };
    
    const pollResponse = await axios.post(
      API_URL + "polls",
      pollPayload,
      {
        headers: authHeader(),
      }
    );
    
    const dataFormatter = new Jsona();
    const createdPoll = dataFormatter.deserialize(pollResponse.data);
    
    // Pas 2: Creează opțiunile pentru poll
    const optionsPromises = poll.options.map((option, index) => {
      const optionPayload = {
        data: {
          type: "poll-options",
          attributes: {
            poll_id: parseInt(createdPoll.id),
            option_text: option.option_text,
            order: index,
            votes_count: 0,
          },
        },
      };
      
      return axios.post(
        API_URL + "poll-options",
        optionPayload,
        {
          headers: authHeader(),
        }
      );
    });
    
    await Promise.all(optionsPromises);
    
    return pollResponse;
  },

  async editPoll(poll) {
    const payload = {
      data: {
        type: "polls",
        id: poll.id.toString(),
        attributes: {
          title: poll.title,
          description: poll.description,
          is_active: poll.is_active,
          allow_multiple_votes: poll.allow_multiple_votes,
          start_date: poll.start_date,
          end_date: poll.end_date,
          tenant_id: poll.tenant_id,
        },
      },
    };
    return await axios.patch(
      API_URL + "polls/" + poll.id,
      payload,
      {
        headers: authHeader(),
      }
    );
  },
};

