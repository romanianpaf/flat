<template>
  <div class="py-4 container-fluid">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="p-3 pb-0 card-header">
            <h5 class="mb-0">Configurări Cont</h5>
            <p class="text-sm">Gestionează setările contului tău</p>
          </div>
          <div class="p-3 card-body">
            <!-- Notificări -->
            <div class="card mt-4">
              <div class="card-header">
                <h6>Notificări</h6>
                <p class="text-sm mb-0">
                  Alege cum primești notificările
                </p>
              </div>
              <div class="card-body pt-0">
                <h6 class="text-xs text-uppercase text-body font-weight-bolder mt-3">
                  Email
                </h6>
                <ul class="list-group">
                  <li class="px-0 border-0 list-group-item">
                    <soft-switch
                      id="emailMentions"
                      v-model="settings.notifications.email_mentions"
                      name="emailMentions"
                      class="ps-0 ms-auto"
                      label-class="mb-0 text-body ms-3 text-truncate w-80"
                      @change="updateSettings"
                    >
                      Email când cineva mă menționează
                    </soft-switch>
                  </li>
                  <li class="px-0 border-0 list-group-item">
                    <soft-switch
                      id="emailComments"
                      v-model="settings.notifications.email_comments"
                      name="emailComments"
                      label-class="mb-0 text-body ms-3 text-truncate w-80"
                      class="ps-0 ms-auto"
                      @change="updateSettings"
                    >
                      Email când cineva comentează
                    </soft-switch>
                  </li>
                  <li class="px-0 border-0 list-group-item">
                    <soft-switch
                      id="emailUpdates"
                      v-model="settings.notifications.email_updates"
                      name="emailUpdates"
                      label-class="mb-0 text-body ms-3 text-truncate w-80"
                      class="ps-0 ms-auto"
                      @change="updateSettings"
                    >
                      Actualizări lunare
                    </soft-switch>
                  </li>
                </ul>
                
                <h6 class="text-xs text-uppercase text-body font-weight-bolder mt-4">
                  Aplicație
                </h6>
                <ul class="list-group">
                  <li class="px-0 border-0 list-group-item">
                    <soft-switch
                      id="appPolls"
                      v-model="settings.notifications.app_polls"
                      name="appPolls"
                      class="ps-0 ms-auto"
                      label-class="mb-0 text-body ms-3 text-truncate w-80"
                      @change="updateSettings"
                    >
                      Notificări pentru sondaje noi
                    </soft-switch>
                  </li>
                  <li class="px-0 border-0 list-group-item">
                    <soft-switch
                      id="appAutomations"
                      v-model="settings.notifications.app_automations"
                      name="appAutomations"
                      class="ps-0 ms-auto"
                      label-class="mb-0 text-body ms-3 text-truncate w-80"
                      @change="updateSettings"
                    >
                      Notificări pentru automatizări
                    </soft-switch>
                  </li>
                  <li class="px-0 pb-0 border-0 list-group-item">
                    <soft-switch
                      id="appServices"
                      v-model="settings.notifications.app_services"
                      name="appServices"
                      class="ps-0 ms-auto"
                      label-class="mb-0 text-body ms-3 text-truncate w-80"
                      @change="updateSettings"
                    >
                      Notificări pentru servicii noi
                    </soft-switch>
                  </li>
                </ul>
              </div>
            </div>

            <!-- Preferințe Interfață -->
            <div class="card mt-4">
              <div class="card-header">
                <h6>Preferințe Interfață</h6>
              </div>
              <div class="card-body pt-0">
                <div class="row">
                  <div class="col-md-6">
                    <label class="form-label mt-2">Limbă</label>
                    <select
                      id="language"
                      v-model="settings.preferences.language"
                      class="form-control"
                      name="language"
                      @change="updateSettings"
                    >
                      <option value="ro">Română</option>
                      <option value="en">English</option>
                    </select>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label mt-2">Elemente pe pagină</label>
                    <select
                      id="itemsPerPage"
                      v-model="settings.preferences.items_per_page"
                      class="form-control"
                      name="itemsPerPage"
                      @change="updateSettings"
                    >
                      <option value="5">5</option>
                      <option value="10">10</option>
                      <option value="25">25</option>
                      <option value="50">50</option>
                    </select>
                  </div>
                </div>
              </div>
            </div>

            <!-- Securitate -->
            <div class="card mt-4">
              <div class="card-header">
                <h6>Securitate</h6>
              </div>
              <div class="card-body pt-0">
                <div class="d-flex align-items-center mb-3">
                  <div>
                    <h6 class="mb-0 text-sm">Autentificare cu doi factori</h6>
                    <p class="text-xs text-muted mb-0">
                      Adaugă un nivel suplimentar de securitate contului tău
                    </p>
                  </div>
                  <div class="ms-auto">
                    <soft-switch
                      id="twoFactor"
                      v-model="settings.security.two_factor_enabled"
                      name="twoFactor"
                      @change="updateSettings"
                    />
                  </div>
                </div>
                <hr class="horizontal dark" />
                <div class="d-flex align-items-center">
                  <div>
                    <h6 class="mb-0 text-sm">Sesiuni active</h6>
                    <p class="text-xs text-muted mb-0">
                      Gestionează dispozitivele conectate la contul tău
                    </p>
                  </div>
                  <div class="ms-auto">
                    <soft-button
                      color="primary"
                      variant="outline"
                      size="sm"
                      @click="viewSessions"
                    >
                      Vezi Sesiuni
                    </soft-button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Butoane Acțiuni -->
            <div class="mt-4 d-flex justify-content-end">
              <soft-button
                color="primary"
                variant="gradient"
                size="sm"
                :is-disabled="loading"
                @click="saveSettings"
              >
                <span v-if="loading" class="spinner-border spinner-border-sm"></span>
                <span v-else>Salvează Toate Setările</span>
              </soft-button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import SoftSwitch from "@/components/SoftSwitch.vue";
import SoftButton from "@/components/SoftButton.vue";
import showSwal from "/src/mixins/showSwal.js";
import axios from "axios";
import authHeader from "@/services/auth-header";

const API_URL = process.env.VUE_APP_API_BASE_URL + "/api/";

export default {
  name: "Settings",
  components: {
    SoftSwitch,
    SoftButton,
  },
  data() {
    return {
      loading: false,
      settings: {
        notifications: {
          email_mentions: true,
          email_comments: false,
          email_updates: true,
          app_polls: true,
          app_automations: true,
          app_services: false,
        },
        preferences: {
          language: "ro",
          items_per_page: "10",
        },
        security: {
          two_factor_enabled: false,
        },
      },
    };
  },
  mounted() {
    this.loadSettings();
  },
  methods: {
    async loadSettings() {
      try {
        const user = JSON.parse(localStorage.getItem("user"));
        const response = await axios.get(
          `${API_URL}v2/users/${user.id}/settings`,
          { headers: authHeader() }
        );
        
        if (response.data.data) {
          this.settings = {
            ...this.settings,
            ...response.data.data.attributes,
          };
        }
      } catch (error) {
        console.error("Error loading settings:", error);
        // Dacă nu există setări salvate, folosim valorile default
      }
    },
    async updateSettings() {
      // Auto-save on change
      await this.saveSettings(false);
    },
    async saveSettings(showMessage = true) {
      this.loading = true;
      try {
        const user = JSON.parse(localStorage.getItem("user"));
        await axios.patch(
          `${API_URL}v2/users/${user.id}/settings`,
          {
            data: {
              type: "user-settings",
              id: user.id,
              attributes: this.settings,
            },
          },
          { headers: authHeader() }
        );
        
        if (showMessage) {
          showSwal.methods.showSwal({
            type: "success",
            message: "Setările au fost salvate cu succes!",
          });
        }
      } catch (error) {
        console.error("Error saving settings:", error);
        if (showMessage) {
          showSwal.methods.showSwal({
            type: "error",
            message: "Eroare la salvarea setărilor!",
          });
        }
      } finally {
        this.loading = false;
      }
    },
    viewSessions() {
      showSwal.methods.showSwal({
        type: "info",
        message: "Funcționalitatea de gestionare a sesiunilor va fi disponibilă în curând!",
      });
    },
  },
};
</script>

