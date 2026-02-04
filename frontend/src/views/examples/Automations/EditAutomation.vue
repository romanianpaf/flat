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
            <!-- MQTT Status from Tenant -->
            <div v-if="!tenantHasMqtt" class="alert alert-warning">
              <i class="fas fa-exclamation-triangle me-2"></i>
              <strong>MQTT nu este configurat.</strong> Contactează administratorul pentru configurarea conexiunii MQTT.
            </div>
            <div v-else class="alert alert-success">
              <i class="fas fa-check-circle me-2"></i>
              Broker MQTT: <strong>{{ tenantMqttHost }}:{{ tenantMqttPort }}</strong>
              <span class="ms-2 text-sm">(configurat de admin)</span>
            </div>

            <!-- Last execution info -->
            <div v-if="automation.last_run_at" class="alert" :class="lastStatusClass">
              <i class="fas" :class="lastStatusIcon"></i>
              Ultima execuție: <strong>{{ automation.last_run_at }}</strong>
              <span v-if="automation.last_status" class="ms-2">
                ({{ automation.last_status }})
              </span>
              <span v-if="automation.last_error" class="d-block text-sm mt-1">
                {{ automation.last_error }}
              </span>
            </div>

            <div class="row mt-4">
              <div class="col-md-6">
                <label class="form-label">Nume *</label>
                <soft-model-input
                  id="name"
                  v-model="automation.name"
                  type="text"
                  placeholder="Ex: Zăvor Piscină"
                />
                <validation-error :errors="apiValidationErrors.name" />
              </div>
              <div class="col-md-6">
                <label class="form-label">Tip dispozitiv *</label>
                <select v-model="automation.device_type" class="form-control">
                  <option value="switch">🔘 Comutator</option>
                  <option value="sensor">📡 Senzor</option>
                  <option value="actuator">⚙️ Actuator</option>
                  <option value="light">💡 Lumină</option>
                  <option value="lock">🔒 Zăvor</option>
                  <option value="test">🧪 Test/Ping</option>
                </select>
                <validation-error :errors="apiValidationErrors.device_type" />
              </div>
            </div>

            <div class="row mt-4">
              <div class="col-12">
                <label class="form-label">Descriere</label>
                <textarea
                  v-model="automation.description"
                  class="form-control"
                  rows="2"
                  placeholder="Descriere opțională"
                ></textarea>
                <validation-error :errors="apiValidationErrors.description" />
              </div>
            </div>

            <h6 class="mt-4 mb-3">⚡ Trigger & Acțiune</h6>
            
            <div class="row mt-3">
              <div class="col-md-6">
                <label class="form-label">Tip Declanșare *</label>
                <select v-model="automation.trigger_type" class="form-control">
                  <option value="manual">👆 Manual (din UI)</option>
                  <option value="scheduled">⏰ Programat (cron)</option>
                  <option value="mqtt_event">📨 Event MQTT</option>
                </select>
                <validation-error :errors="apiValidationErrors.trigger_type" />
              </div>
              <div class="col-md-6">
                <label class="form-label">Tip Acțiune *</label>
                <select v-model="automation.action_type" class="form-control">
                  <option value="mqtt_publish">📤 Publish MQTT</option>
                  <option value="webhook">🌐 Webhook</option>
                  <option value="notification">🔔 Notificare</option>
                </select>
                <validation-error :errors="apiValidationErrors.action_type" />
              </div>
            </div>

            <!-- Scheduled Trigger Config -->
            <div v-if="automation.trigger_type === 'scheduled'" class="row mt-3">
              <div class="col-12">
                <label class="form-label">Expresie Cron *</label>
                <soft-model-input
                  v-model="automation.schedule_cron"
                  type="text"
                  placeholder="Ex: 0 8 * * * (zilnic la 8:00)"
                />
                <small class="text-muted">Format: minut oră zi lună zi_săptămână</small>
                <validation-error :errors="apiValidationErrors.schedule_cron" />
              </div>
            </div>

            <!-- MQTT Event Trigger Config -->
            <div v-if="automation.trigger_type === 'mqtt_event'" class="row mt-3">
              <div class="col-md-8">
                <label class="form-label">Topic Subscribe *</label>
                <soft-model-input
                  v-model="automation.mqtt_subscribe_topic"
                  type="text"
                  placeholder="Ex: home/motion/detected"
                />
                <validation-error :errors="apiValidationErrors.mqtt_subscribe_topic" />
              </div>
              <div class="col-md-4">
                <label class="form-label">Payload Match (regex)</label>
                <soft-model-input
                  v-model="automation.mqtt_subscribe_payload_match"
                  type="text"
                  placeholder="Ex: ON|1|true"
                />
                <validation-error :errors="apiValidationErrors.mqtt_subscribe_payload_match" />
              </div>
            </div>

            <!-- MQTT Publish Action Config -->
            <div v-if="automation.action_type === 'mqtt_publish'">
              <h6 class="mt-4 mb-3">🔌 Configurare Publish MQTT</h6>

              <div class="row mt-3">
                <div class="col-md-7">
                  <label class="form-label">Topic MQTT *</label>
                  <soft-model-input
                    v-model="automation.mqtt_topic"
                    type="text"
                    placeholder="Ex: home/pool/lock"
                  />
                  <validation-error :errors="apiValidationErrors.mqtt_topic" />
                </div>
                <div class="col-md-3">
                  <label class="form-label">QoS *</label>
                  <select v-model="automation.mqtt_qos" class="form-control">
                    <option :value="0">0 - At most once</option>
                    <option :value="1">1 - At least once</option>
                    <option :value="2">2 - Exactly once</option>
                  </select>
                  <validation-error :errors="apiValidationErrors.mqtt_qos" />
                </div>
                <div class="col-md-2">
                  <label class="form-label">Retain</label>
                  <div class="form-check form-switch mt-2">
                    <input
                      id="mqtt_retain"
                      v-model="automation.mqtt_retain"
                      class="form-check-input"
                      type="checkbox"
                    />
                    <label class="form-check-label" for="mqtt_retain">
                      {{ automation.mqtt_retain ? 'Da' : 'Nu' }}
                    </label>
                  </div>
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
            </div>

            <h6 class="mt-4 mb-3">⚙️ Opțiuni</h6>

            <div class="row mt-3">
              <div class="col-md-4">
                <label class="form-label">Cooldown (ms)</label>
                <soft-model-input
                  v-model="automation.cooldown_ms"
                  type="number"
                  placeholder="0"
                />
                <small class="text-muted">Anti-flood: timp minim între execuții</small>
                <validation-error :errors="apiValidationErrors.cooldown_ms" />
              </div>
              <div class="col-md-4 d-flex align-items-end">
                <div class="form-check form-switch mb-3">
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
              :is-disabled="loading"
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
import axios from "axios";

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
      automation: {
        name: '',
        description: '',
        device_type: 'lock',
        trigger_type: 'manual',
        action_type: 'mqtt_publish',
        schedule_cron: '',
        mqtt_subscribe_topic: '',
        mqtt_subscribe_payload_match: '',
        mqtt_topic: '',
        mqtt_payload_on: '',
        mqtt_payload_off: '',
        mqtt_qos: 0,
        mqtt_retain: false,
        cooldown_ms: 0,
        is_active: true,
        last_run_at: null,
        last_status: null,
        last_error: null,
      },
      tenantMqttHost: null,
      tenantMqttPort: 8883,
      tenantHasMqtt: false,
      loading: false,
    };
  },
  computed: {
    lastStatusClass() {
      if (this.automation.last_status === 'success') return 'alert-success';
      if (this.automation.last_status === 'error') return 'alert-danger';
      if (this.automation.last_status === 'timeout') return 'alert-warning';
      return 'alert-secondary';
    },
    lastStatusIcon() {
      if (this.automation.last_status === 'success') return 'fa-check-circle me-2';
      if (this.automation.last_status === 'error') return 'fa-times-circle me-2';
      if (this.automation.last_status === 'timeout') return 'fa-clock me-2';
      return 'fa-history me-2';
    },
  },
  async created() {
    await this.loadMqttStatus();
    await this.loadAutomation();
  },
  methods: {
    async loadMqttStatus() {
      try {
        const res = await axios.get('/api/v2/mqtt-test/status');
        if (res.data?.mqtt_configured) {
          this.tenantHasMqtt = true;
          this.tenantMqttHost = res.data?.data?.broker_host || '';
          this.tenantMqttPort = res.data?.data?.broker_port || 8883;
        }
      } catch (e) {
        console.log('Nu s-a putut verifica statusul MQTT:', e.message);
      }
    },
    
    async loadAutomation() {
      const data = await this.$store.dispatch("automations/getAutomation", {
        id: this.$route.params.id,
        params: {
          include: "tenant",
        },
      });
      
      this.automation = {
        id: data.id,
        name: data.name || '',
        description: data.description || '',
        device_type: data.device_type || 'lock',
        trigger_type: data.trigger_type || 'manual',
        action_type: data.action_type || 'mqtt_publish',
        schedule_cron: data.schedule_cron || '',
        mqtt_subscribe_topic: data.mqtt_subscribe_topic || '',
        mqtt_subscribe_payload_match: data.mqtt_subscribe_payload_match || '',
        mqtt_topic: data.mqtt_topic || '',
        mqtt_payload_on: data.mqtt_payload_on || '',
        mqtt_payload_off: data.mqtt_payload_off || '',
        mqtt_qos: data.mqtt_qos ?? 0,
        mqtt_retain: data.mqtt_retain ?? false,
        cooldown_ms: data.cooldown_ms ?? 0,
        is_active: data.is_active ?? true,
        last_run_at: data.last_run_at,
        last_status: data.last_status,
        last_error: data.last_error,
      };
    },
    
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

<style scoped>
</style>
