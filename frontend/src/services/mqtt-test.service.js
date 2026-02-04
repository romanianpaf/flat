import axios from "axios";
import authHeader from "./auth-header";

const API_URL = import.meta.env.VITE_API_BASE_URL;

/**
 * Service pentru testarea MQTT
 */
class MqttTestService {
  /**
   * Obține statusul automatizării de test
   */
  async getStatus() {
    const response = await axios.get(
      `${API_URL}/mqtt-test/status`,
      { headers: authHeader() }
    );
    return response.data;
  }

  /**
   * Trimite un mesaj de test (ping sau pong)
   */
  async send(payload) {
    const response = await axios.post(
      `${API_URL}/mqtt-test/send`,
      { payload },
      { headers: authHeader() }
    );
    return response.data;
  }
}

export default new MqttTestService();
