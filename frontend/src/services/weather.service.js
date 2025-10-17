import axios from "axios";

/**
 * Weather Service - Backend API cu cache
 * 
 * Optimizare: Backend-ul face UN singur call la OpenWeatherMap la fiecare 10 minute
 * și servește datele din cache tuturor utilizatorilor.
 * 
 * Consum API: ~144 calls/zi (1 call/10min) indiferent de numărul de utilizatori
 * Free tier OpenWeatherMap: 1,000 calls/day → 85% economie!
 * 
 * IMPORTANT: Endpoint-ul este PUBLIC (nu necesită autentificare)
 */

const API_URL = process.env.VUE_APP_API_BASE_URL + "/weather/";

class WeatherService {
  /**
   * Obține vremea curentă (din backend cu cache - PUBLIC endpoint)
   */
  async getCurrentWeather() {
    try {
      // NU trimite authHeader - endpoint-ul este public
      const response = await axios.get(`${API_URL}current`);

      return response.data.data;
    } catch (error) {
      console.error("Eroare la încărcarea vremii:", error);
      return this.getMockWeather();
    }
  }


  /**
   * Date mock pentru development/fallback
   */
  getMockWeather() {
    return {
      location: "București, Chitilei",
      temperature: 22,
      feelsLike: 20,
      description: "parțial înnorat",
      icon: "⛅",
      humidity: 65,
      pressure: 1013,
      windSpeed: 3.5,
      cloudiness: 40,
      sunrise: new Date().toISOString(),
      sunset: new Date().toISOString(),
    };
  }
}

export default new WeatherService();

