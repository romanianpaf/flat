<template>
  <div class="py-4 container-fluid">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="pb-0 card-header">
            <div class="d-lg-flex">
              <div>
                <h5 class="mb-0">Editează automatizare</h5>
              </div>
              <div class="my-auto mt-4 ms-auto mt-lg-0">
                <div class="my-auto ms-auto">
                  <router-link to="/automations/list" class="mb-0 btn bg-gradient-primary btn-sm"
                    >&nbsp; Înapoi la listă</router-link
                  >
                </div>
              </div>
            </div>
          </div>

          <div class="card-body">
            <div class="row mt-4">
              <div class="col-md-6">
                <label class="form-label">Nume</label>
                <soft-model-input
                  id="name"
                  v-model="automation.name"
                  type="text"
                  placeholder="Ex: Zăvor Piscină"
                />
                <validation-error :errors="apiValidationErrors.name" />
              </div>
              <div class="col-md-6">
                <label class="form-label">Tip</label>
                <select v-model="automation.type" class="form-control">
                  <option value="switch">🔘 Comutator</option>
                  <option value="sensor">📡 Senzor</option>
                  <option value="actuator">⚙️ Actuator</option>
                  <option value="light">💡 Lumină</option>
                  <option value="lock">🔒 Zăvor</option>
                </select>
                <validation-error :errors="apiValidationErrors.type" />
              </div>
            </div>

            <div class="row mt-4">
              <div class="col-12">
                <label class="form-label">Descriere</label>
                <textarea
                  v-model="automation.description"
                  class="form-control"
                  rows="3"
                  placeholder="Descriere opțională"
                ></textarea>
                <validation-error :errors="apiValidationErrors.description" />
              </div>
            </div>

            <h6 class="mt-4 mb-3">📡 Configurare Broker MQTT</h6>
            
            <div class="row mt-3">
              <div class="col-md-8">
                <label class="form-label">Host Broker</label>
                <soft-model-input
                  v-model="automation.mqtt_broker_host"
                  type="text"
                  placeholder="Ex: mqtt.example.com sau 192.168.1.100"
                />
                <validation-error :errors="apiValidationErrors.mqtt_broker_host" />
              </div>
              <div class="col-md-4">
                <label class="form-label">Port</label>
                <soft-model-input
                  v-model="automation.mqtt_broker_port"
                  type="number"
                  placeholder="1883"
                />
                <validation-error :errors="apiValidationErrors.mqtt_broker_port" />
              </div>
            </div>

            <div class="row mt-3">
              <div class="col-md-6">
                <label class="form-label">Utilizator (opțional)</label>
                <soft-model-input
                  v-model="automation.mqtt_broker_username"
                  type="text"
                  placeholder="Username"
                />
                <validation-error :errors="apiValidationErrors.mqtt_broker_username" />
              </div>
              <div class="col-md-6">
                <label class="form-label">Parolă (lasă gol pentru a păstra cea actuală)</label>
                <soft-model-input
                  v-model="automation.mqtt_broker_password"
                  type="password"
                  placeholder="Parolă nouă (opțional)"
                />
                <validation-error :errors="apiValidationErrors.mqtt_broker_password" />
              </div>
            </div>

            <h6 class="mt-4 mb-3">🔌 Configurare Topic & Payload</h6>

            <div class="row mt-3">
              <div class="col-md-9">
                <label class="form-label">Topic MQTT</label>
                <soft-model-input
                  v-model="automation.mqtt_topic"
                  type="text"
                  placeholder="Ex: home/pool/lock"
                />
                <validation-error :errors="apiValidationErrors.mqtt_topic" />
              </div>
              <div class="col-md-3">
                <label class="form-label">QoS</label>
                <select v-model="automation.mqtt_qos" class="form-control">
                  <option :value="0">0 - At most once</option>
                  <option :value="1">1 - At least once</option>
                  <option :value="2">2 - Exactly once</option>
                </select>
                <validation-error :errors="apiValidationErrors.mqtt_qos" />
              </div>
            </div>

            <div class="row mt-3">
              <div class="col-md-6">
                <label class="form-label">Payload ON/Open</label>
                <textarea
                  v-model="automation.mqtt_payload_on"
                  class="form-control"
                  rows="2"
                  placeholder='Ex: {"state": "ON"} sau 1'
                ></textarea>
                <validation-error :errors="apiValidationErrors.mqtt_payload_on" />
              </div>
              <div class="col-md-6">
                <label class="form-label">Payload OFF/Close</label>
                <textarea
                  v-model="automation.mqtt_payload_off"
                  class="form-control"
                  rows="2"
                  placeholder='Ex: {"state": "OFF"} sau 0'
                ></textarea>
                <validation-error :errors="apiValidationErrors.mqtt_payload_off" />
              </div>
            </div>

            <div class="row mt-4">
              <div class="col-md-6">
                <label class="form-label">Status</label>
                <div class="form-check form-switch">
                  <input
                    id="is_active"
                    v-model="automation.is_active"
                    class="form-check-input"
                    type="checkbox"
                  />
                  <label class="form-check-label" for="is_active">
                    {{ automation.is_active ? 'Activ' : 'Inactiv' }}
                  </label>
                </div>
              </div>
            </div>

            <soft-button
              color="dark"
              variant="gradient"
              class="float-end mt-4 mb-0"
              size="sm"
              :is-disabled="loading ? true : false"
              @click="editAutomation"
              ><span v-if="loading" class="spinner-border spinner-border-sm"></span>
              <span v-else>Actualizează</span></soft-button
            >
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import SoftModelInput from "/src/components/SoftModelInput.vue";
import SoftButton from "/src/components/SoftButton.vue";
import showSwal from "/src/mixins/showSwal.js";
import formMixin from "/src/mixins/form-mixin.js";
import ValidationError from "@/components/ValidationError.vue";

export default {
  name: "EditAutomation",
  components: {
    SoftModelInput,
    SoftButton,
    ValidationError,
  },
  mixins: [formMixin],
  data() {
    return {
      automation: {},
      loading: false,
    };
  },
  async created() {
    const data = await this.$store.dispatch("automations/getAutomation", {
      id: this.$route.params.id,
      params: {
        include: "tenant",
      },
    });
    // Mapăm device_type → type pentru frontend
    this.automation = {
      ...data,
      type: data.device_type || data.type,
    };
  },
  methods: {
    async editAutomation() {
      this.resetApiValidation();
      this.loading = true;
      try {
        await this.$store.dispatch("automations/editAutomation", this.automation);
        showSwal.methods.showSwal({
          type: "success",
          message: "Automatizare actualizată cu succes!",
        });
        this.$router.push("/automations/list");
      } catch (error) {
        if (error.response?.data?.errors) this.setApiValidation(error.response.data.errors);
        else
          showSwal.methods.showSwal({
            type: "error",
            message: "A apărut o eroare!",
            width: 350,
          });
        this.loading = false;
      }
    },
  },
};
</script>

