<template>
  <div class="container top-0 position-sticky z-index-sticky">
    <div class="row">
      <div class="col-12">
        <navbar
          is-blur="blur blur-rounded my-3 py-2 start-0 end-0 mx-4 shadow"
          btn-background="bg-gradient-primary"
          :dark-mode="true"
        />
      </div>
    </div>
  </div>
  <main class="mt-0 main-content main-content-bg">
    <section>
      <div class="page-header min-vh-75">
        <div class="container">
          <div class="row">
            <div class="mx-auto col-xl-4 col-lg-5 col-md-6 d-flex flex-column">
              <div class="mt-8 card card-plain">
                <div class="pb-0 card-header text-start">
                  <h3 class="font-weight-bolder text-primary text-gradient text-center">
                    Bun venit înapoi
                  </h3>
                  <br />
                </div>
                <div class="card-body pb-3">
                  <Form
                    role="form"
                    class="text-start"
                    :validation-schema="schema"
                    @submit="handleLogin"
                  >
                    <label for="email">Email</label>
                    <soft-field
                      id="email"
                      v-model="user.email"
                      type="email"
                      placeholder="Email"
                      name="email"
                    />

                    <label>Parolă</label>
                    <soft-field
                      id="password"
                      v-model="user.password"
                      type="password"
                      placeholder="Parolă"
                      name="password"
                    />

                    <soft-switch id="rememberMe" name="rememberMe" checked>
                      Ține-mă minte
                    </soft-switch>

                    <div class="text-center">
                      <soft-button
                        class="my-4 mb-2"
                        variant="gradient"
                        color="primary"
                        full-width
                        :is-disabled="loading ? true : false"
                      >
                        <span
                          v-if="loading"
                          class="spinner-border spinner-border-sm"
                        ></span>
                        <span v-else>Autentificare</span>
                      </soft-button>
                    </div>
                  </Form>
                </div>
                <div class="px-1 pt-0 pb-3 text-center card-footer px-lg-2">
                  <p class="mx-auto mb-0 text-sm">
                    Nu ai cont?
                    <router-link
                      :to="{ name: 'Register' }"
                      class="text-primary text-gradient font-weight-bold"
                      >Înregistrează-te</router-link
                    >
                  </p>
                </div>
                <div class="px-1 pt-0 pb-3 text-center card-footer px-lg-2">
                  <p class="mx-auto mb-4 text-sm">
                    Ai uitat parola?
                    <router-link
                      :to="{ name: 'SendEmail' }"
                      class="text-primary text-gradient font-weight-bold"
                      >Recuperează</router-link
                    >
                  </p>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="top-0 oblique position-absolute h-100 d-md-block d-none me-n8">
                <div
                  class="bg-cover oblique-image position-absolute fixed-top ms-auto h-100 z-index-0 ms-n6"
                  :style="{
                    backgroundImage:
                      'url(' + require('@/assets/img/curved-images/atria-fundal.jpeg') + ')',
                  }"
                ></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </main>
  <app-footer />
</template>

<script>
import Navbar from "@/examples/PageLayout/Navbar.vue";
import AppFooter from "@/examples/PageLayout/Footer.vue";
import SoftField from "@/components/SoftField.vue";
import SoftSwitch from "@/components/SoftSwitch.vue";
import SoftButton from "@/components/SoftButton.vue";
import showSwal from "/src/mixins/showSwal.js";
const body = document.getElementsByTagName("body")[0];
import { mapMutations } from "vuex";
import { Form } from "vee-validate";
import * as yup from "yup";

export default {
  name: "Login",
  components: {
    Navbar,
    AppFooter,
    SoftField,
    SoftSwitch,
    SoftButton,
    Form,
  },

  data() {
    const schema = yup.object().shape({
      email: yup
        .string()
        .required("Email is required!")
        .email("Must be a valid email! ex.: name@company.domain"),
      password: yup.string().required("Password is required!"),
    });
    const user = {
      email: "",
      password: "",
    };
    return {
      loading: false,
      user,
      schema,
    };
  },

  created() {
    this.toggleEveryDisplay();
    this.toggleHideConfig();
    body.classList.remove("bg-gray-100");
  },
  beforeUnmount() {
    this.toggleEveryDisplay();
    this.toggleHideConfig();
    body.classList.add("bg-gray-100");
  },
  methods: {
    ...mapMutations(["toggleEveryDisplay", "toggleHideConfig"]),

    async handleLogin() {
      this.loading = true;
      try {
        await this.$store.dispatch("auth/login", this.user);
        this.$router.push("/dashboards/dashboard-default");
      } catch (error) {
        showSwal.methods.showSwal({
          type: "error",
          message: "Invalid credentials!",
        });
        this.loading = false;
      }
    },
  },
};
</script>
