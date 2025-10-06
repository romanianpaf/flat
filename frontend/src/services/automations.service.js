import axios from "axios";
import authHeader from "./auth-header";
import Jsona from "jsona";
const dataFormatter = new Jsona();

const API_URL = process.env.VUE_APP_API_BASE_URL + '/';

export default {
  async getAutomations(params) {
    const response = await axios.get(API_URL + "automations", {
      headers: authHeader(),
      params: {
        ...(params?.filter?.name ? { "filter[name]": params.filter.name } : {}),
        ...(params?.filter?.type ? { "filter[type]": params.filter.type } : {}),
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
  },

  async getAutomation(payload) {
    const id = typeof payload === 'object' ? payload.id : payload;
    const params = typeof payload === 'object' ? payload.params : {};
    
    const response = await axios.get(API_URL + "automations/" + id, {
      headers: authHeader(),
      params: {
        ...(params?.include ? { include: params.include } : {}),
      },
    });
    return dataFormatter.deserialize(response.data);
  },

  async deleteAutomation(id) {
    await axios.delete(API_URL + "automations/" + id.toString(), {
      headers: authHeader(),
    });
  },

  async addAutomation(automation) {
    // Construim payload-ul JSON:API manual pentru a evita conflictul cu câmpul "type"
    const payload = {
      data: {
        type: "automations", // tipul resursei JSON:API
        attributes: {
          name: automation.name,
          description: automation.description,
          device_type: automation.type, // tipul automatizării - trimis ca device_type
          mqtt_broker_host: automation.mqtt_broker_host,
          mqtt_broker_port: automation.mqtt_broker_port,
          mqtt_broker_username: automation.mqtt_broker_username,
          mqtt_broker_password: automation.mqtt_broker_password,
          mqtt_topic: automation.mqtt_topic,
          mqtt_payload_on: automation.mqtt_payload_on,
          mqtt_payload_off: automation.mqtt_payload_off,
          mqtt_qos: automation.mqtt_qos,
          is_active: automation.is_active,
          tenant_id: automation.tenant_id,
        },
      },
    };
    return await axios.post(
      API_URL + "automations",
      payload,
      {
        headers: authHeader(),
      }
    );
  },

  async editAutomation(automation) {
    // Construim payload-ul JSON:API manual pentru a evita conflictul cu câmpul "type"
    const payload = {
      data: {
        type: "automations", // tipul resursei JSON:API
        id: automation.id.toString(),
        attributes: {
          name: automation.name,
          description: automation.description,
          device_type: automation.type, // tipul automatizării - trimis ca device_type
          mqtt_broker_host: automation.mqtt_broker_host,
          mqtt_broker_port: automation.mqtt_broker_port,
          mqtt_broker_username: automation.mqtt_broker_username,
          mqtt_broker_password: automation.mqtt_broker_password,
          mqtt_topic: automation.mqtt_topic,
          mqtt_payload_on: automation.mqtt_payload_on,
          mqtt_payload_off: automation.mqtt_payload_off,
          mqtt_qos: automation.mqtt_qos,
          is_active: automation.is_active,
          tenant_id: automation.tenant_id,
        },
      },
    };
    return await axios.patch(
      API_URL + "automations/" + automation.id,
      payload,
      {
        headers: authHeader(),
      }
    );
  },
};

