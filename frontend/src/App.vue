<template>
  <sidenav
    v-if="showSidenav"
    :custom-class="color"
    :class="[isTransparent, isRTL ? 'fixed-end' : 'fixed-start']"
  />
  <main
    class="main-content position-relative max-height-vh-100 h-100 border-radius-lg"
  >
    <!-- Banner impersonare -->
    <div
      v-if="impersonating"
      class="alert alert-warning text-white m-3 d-flex align-items-center justify-content-between"
      style="position: relative; z-index: 1000"
    >
      <span class="text-sm mb-0">
        <i class="fas fa-user-secret me-2"></i>
        Ești conectat ca <strong>{{ impersonatedName }}</strong> (impersonare).
      </span>
      <button class="btn btn-sm btn-white mb-0 ms-3" @click="stopImpersonation">
        Revino la contul meu
      </button>
    </div>
    <!-- nav -->
    <navbar
      v-if="showNavbar"
      :class="[isNavFixed ? navbarFixed : '', isAbsolute ? absolute : '']"
      :text-white="isAbsolute ? 'text-white opacity-8' : ''"
      :min-nav="navbarMinimize"
    />
    <router-view />
    <app-footer v-show="showFooter" />
  </main>
</template>
<script>
import Sidenav from "@/examples/Sidenav/index.vue";
import Navbar from "@/examples/Navbars/Navbar.vue";
import AppFooter from "@/examples/Footer.vue";
import authService from "@/services/auth.service.js";
import { mapMutations, mapState } from "vuex";
export default {
  name: "App",
  components: {
    Sidenav,
    Navbar,
    AppFooter,
  },
  data() {
    return {
      impersonating: false,
      impersonatedName: "",
    };
  },
  computed: {
    ...mapState([
      "isTransparent",
      "isRTL",
      "isNavFixed",
      "isAbsolute",
      "navbarFixed",
      "absolute",
      "color",
      "showSidenav",
      "showNavbar",
      "showFooter",
    ]),
  },
  beforeMount() {
    this.$store.state.isTransparent = "bg-transparent";
    this.impersonating = authService.isImpersonating();
    if (this.impersonating) {
      try {
        const u = JSON.parse(localStorage.getItem("user") || "{}");
        this.impersonatedName = u?.name || u?.email || "utilizator";
      } catch (e) {
        this.impersonatedName = "utilizator";
      }
    }
  },
  methods: {
    ...mapMutations(["navbarMinimize"]),
    async stopImpersonation() {
      await authService.leaveImpersonation();
      // Reîncărcare completă ca toate componentele să revină la sysadmin.
      window.location.href = "/acasa";
    },
  },
};
</script>
<style>
#sidenav-main {
  overflow: hidden !important;
}
#sidenav-collapse-main {
  overflow-x: hidden !important;
}
</style>