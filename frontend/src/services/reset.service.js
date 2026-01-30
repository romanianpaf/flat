import axios from "axios";

const API_URL = import.meta.env.VITE_API_BASE_URL + '/';

export default {
  async sendEmail(email) {
    await axios.post(API_URL + "password-forgot", {
      email: email,
      redirect_url: import.meta.env.VITE_BASE_URL + "password/reset/",
    });
  },

  async resetPassword(newUser) {
    await axios.post(API_URL + "password-reset", newUser);
  },
};
