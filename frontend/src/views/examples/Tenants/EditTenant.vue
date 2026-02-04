<template>
  <div class="py-4 container-fluid">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="pb-0 card-header">
            <div class="d-lg-flex">
              <div>
                <h5 class="mb-0">{{ tenant.name || 'Editează Beneficiar' }}</h5>
                <p class="text-sm text-muted mb-0">Gestionează informațiile și configurările beneficiarului</p>
              </div>
              <div class="my-auto mt-4 ms-auto mt-lg-0">
                <router-link to="/tenants/list" class="mb-0 btn btn-outline-primary btn-sm">
                  ← Înapoi la listă
                </router-link>
              </div>
            </div>
          </div>

          <!-- Tabs Navigation -->
          <div class="card-header pt-0 pb-0">
            <ul class="nav nav-tabs" role="tablist">
              <li class="nav-item">
                <a 
                  class="nav-link" 
                  :class="{ active: activeTab === 'info' }"
                  @click.prevent="activeTab = 'info'"
                  href="#"
                >
                  <i class="fas fa-info-circle me-2"></i>Informații
                </a>
              </li>
              <li class="nav-item">
                <a 
                  class="nav-link" 
                  :class="{ active: activeTab === 'building' }"
                  @click.prevent="activeTab = 'building'"
                  href="#"
                >
                  <i class="fas fa-building me-2"></i>Imobil
                </a>
              </li>
              <li v-if="isSysadmin" class="nav-item">
                <a 
                  class="nav-link" 
                  :class="{ active: activeTab === 'technical' }"
                  @click.prevent="activeTab = 'technical'"
                  href="#"
                >
                  <i class="fas fa-cog me-2"></i>Tehnic
                </a>
              </li>
            </ul>
          </div>

          <!-- Tab Content -->
          <div class="card-body">
            <!-- Tab: Informații -->
            <div v-show="activeTab === 'info'">
              <div class="row">
                <div class="col-md-6">
                  <label class="form-label mt-2">Nume *</label>
                  <soft-model-input
                    id="name"
                    v-model="tenant.name"
                    type="text"
                    placeholder="Numele beneficiarului"
                  />
                  <validation-error :errors="apiValidationErrors.name" />
                </div>

                <div class="col-md-6">
                  <label class="form-label mt-2">CUI</label>
                  <soft-model-input
                    id="fiscal_code"
                    v-model="tenant.fiscal_code"
                    type="text"
                    placeholder="Cod Unic de Identificare"
                  />
                  <validation-error :errors="apiValidationErrors.fiscal_code" />
                </div>
              </div>

              <div class="row">
                <div class="col-12">
                  <label class="form-label mt-4">Adresă</label>
                  <soft-model-textarea
                    id="address"
                    v-model="tenant.address"
                    placeholder="Adresa completă"
                    rows="2"
                  />
                  <validation-error :errors="apiValidationErrors.address" />
                </div>
              </div>

              <div class="row">
                <div class="col-12">
                  <label class="form-label mt-4">Descriere</label>
                  <soft-model-textarea
                    id="description"
                    v-model="tenant.description"
                    placeholder="Descrierea beneficiarului"
                    rows="3"
                  />
                  <validation-error :errors="apiValidationErrors.description" />
                </div>
              </div>

              <div class="row">
                <div class="col-12">
                  <label class="form-label mt-4">Instrucțiuni înregistrare</label>
                  <soft-model-textarea
                    id="registration_instructions"
                    v-model="tenant.registration_instructions"
                    placeholder="Instrucțiuni pentru noii membri care se înregistrează"
                    rows="3"
                  />
                  <validation-error :errors="apiValidationErrors.registration_instructions" />
                </div>
              </div>

              <div class="mt-4">
                <h6 class="mb-3">Date de Contact</h6>
                <div class="row">
                  <div class="col-md-4">
                    <label class="form-label">Persoană Contact</label>
                    <soft-model-input
                      id="contact_person"
                      v-model="tenant.contact_data.person"
                      type="text"
                      placeholder="Nume persoană"
                    />
                    <validation-error :errors="apiValidationErrors['contact_data.person']" />
                  </div>

                  <div class="col-md-4">
                    <label class="form-label">Email</label>
                    <soft-model-input
                      id="contact_email"
                      v-model="tenant.contact_data.email"
                      type="email"
                      placeholder="email@example.com"
                    />
                    <validation-error :errors="apiValidationErrors['contact_data.email']" />
                  </div>

                  <div class="col-md-4">
                    <label class="form-label">Telefon</label>
                    <soft-model-input
                      id="contact_phone"
                      v-model="tenant.contact_data.phone"
                      type="text"
                      placeholder="+40 XXX XXX XXX"
                      @input="tenant.contact_data.phone = formatPhone($event)"
                    />
                    <validation-error :errors="apiValidationErrors['contact_data.phone']" />
                  </div>
                </div>
              </div>

              <soft-button
                color="dark"
                variant="gradient"
                class="float-end mt-4 mb-0"
                size="sm"
                :is-disabled="loadingInfo"
                @click="saveInfo"
              >
                <span v-if="loadingInfo" class="spinner-border spinner-border-sm"></span>
                <span v-else>Salvează Informații</span>
              </soft-button>
            </div>

            <!-- Tab: Imobil -->
            <div v-show="activeTab === 'building'">
              <div v-if="loadingBuilding" class="text-center py-5">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="mt-2 text-sm">Se încarcă configurarea...</p>
              </div>
              <div v-else>
                <div class="d-flex justify-content-between align-items-center mb-4">
                  <div>
                    <p class="text-sm mb-0">Definește scările și apartamentele pentru acest beneficiar.</p>
                  </div>
                  <button class="btn bg-gradient-primary btn-sm mb-0" @click="openAddStaircase">
                    + Adaugă scară
                  </button>
                </div>

                <div v-if="staircases.length === 0" class="text-center py-4">
                  <i class="fas fa-building fa-3x text-secondary opacity-6 mb-3"></i>
                  <p class="text-muted">Nu există scări configurate. Adaugă prima scară.</p>
                </div>

                <div class="accordion" id="staircasesAcc" v-else>
                  <div class="accordion-item" v-for="s in staircases" :key="s.id">
                    <h2 class="accordion-header" :id="`h-${s.id}`">
                      <button
                        class="accordion-button collapsed"
                        type="button"
                        data-bs-toggle="collapse"
                        :data-bs-target="`#c-${s.id}`"
                        aria-expanded="false"
                      >
                        <strong>Scara {{ s.code }}</strong>
                        <span class="ms-2 text-muted" v-if="s.name">— {{ s.name }}</span>
                        <span class="ms-auto badge bg-gradient-info me-3">{{ getApartmentsForStaircase(s.code).length }} apartamente</span>
                      </button>
                    </h2>
                    <div :id="`c-${s.id}`" class="accordion-collapse collapse" data-bs-parent="#staircasesAcc">
                      <div class="accordion-body">
                        <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
                          <button class="btn btn-outline-dark btn-sm mb-0" @click="openEditStaircase(s)">
                            <i class="fas fa-edit me-1"></i>Editează
                          </button>
                          <button class="btn btn-outline-danger btn-sm mb-0" @click="onDeleteStaircase(s)">
                            <i class="fas fa-trash me-1"></i>Șterge
                          </button>
                        </div>

                        <label class="form-label">Apartamente (numere separate prin virgulă)</label>
                        <textarea class="form-control" rows="2" v-model="apartmentsByStaircase[s.code]" placeholder="ex: 1, 2, 3, 4, 5" />
                        
                        <div class="form-check mt-2">
                          <input class="form-check-input" type="checkbox" v-model="removeMissing" :id="`rm-${s.id}`" />
                          <label class="form-check-label text-sm" :for="`rm-${s.id}`">Șterge apartamentele care nu sunt în listă</label>
                        </div>

                        <!-- Tabel apartamente cu etaje -->
                        <div class="mt-4" v-if="getApartmentsForStaircase(s.code).length > 0">
                          <label class="form-label">Configurare etaje</label>
                          <div class="table-responsive">
                            <table class="table table-sm table-hover">
                              <thead>
                                <tr>
                                  <th>Apartament</th>
                                  <th style="width: 120px">Etaj</th>
                                </tr>
                              </thead>
                              <tbody>
                                <tr v-for="apt in getApartmentsForStaircase(s.code)" :key="apt.id">
                                  <td>Ap. {{ apt.number }}</td>
                                  <td>
                                    <select 
                                      class="form-select form-select-sm" 
                                      :value="apt.floor || ''"
                                      @change="onFloorChange(apt, $event.target.value)"
                                    >
                                      <option value="">-</option>
                                      <option v-for="f in floorOptions" :key="f" :value="f">{{ f }}</option>
                                    </select>
                                  </td>
                                </tr>
                              </tbody>
                            </table>
                          </div>
                        </div>

                        <button class="btn bg-gradient-success btn-sm mt-3" @click="onSyncApartments(s)">
                          <i class="fas fa-save me-1"></i>Salvează apartamentele
                        </button>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Configurare etaje -->
                <div class="mt-4 pt-3 border-top">
                  <label class="form-label">Etaje disponibile (separate prin virgulă)</label>
                  <input 
                    class="form-control" 
                    v-model="floorsInput" 
                    placeholder="ex: P, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10"
                    @blur="parseFloors"
                  />
                  <small class="text-muted">P = parter</small>
                </div>
              </div>
            </div>

            <!-- Tab: Tehnic (sysadmin only) - MQTT mTLS Configuration -->
            <div v-show="activeTab === 'technical'" v-if="isSysadmin">
              <div class="mb-4">
                <h6 class="mb-1">Configurare MQTT (mTLS)</h6>
                <p class="text-sm text-muted mb-0">
                  Conectare la broker-ul Mosquitto de pe miniPC prin WireGuard + certificate mTLS.
                  <br><small>Fără user/parolă - autentificare exclusiv prin certificate.</small>
                </p>
              </div>

              <!-- Connection Status -->
              <div class="alert" :class="mqttStatusClass" v-if="mqttConfig.mqtt_host">
                <div class="d-flex align-items-center">
                  <span 
                    class="status-indicator me-2" 
                    :class="mqttConnectionStatus === 'ok' ? 'status-connected' : (mqttConnectionStatus === 'error' ? 'status-error' : 'status-unknown')"
                  ></span>
                  <span class="me-3">
                    <strong>{{ mqttConfig.mqtt_host }}:{{ mqttConfig.mqtt_port || 8883 }}</strong>
                  </span>
                  <span class="text-sm">{{ mqttStatusMessage }}</span>
                  <button 
                    class="btn btn-sm btn-outline-dark ms-auto mb-0" 
                    @click="testMqttConnection"
                    :disabled="testingMqtt"
                  >
                    <span v-if="testingMqtt" class="spinner-border spinner-border-sm me-1"></span>
                    <i v-else class="fas fa-plug me-1"></i>
                    Testează conexiunea
                  </button>
                </div>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <label class="form-label mt-3">Host MQTT (IP WireGuard) *</label>
                  <soft-model-input
                    id="mqtt_host"
                    v-model="mqttConfig.mqtt_host"
                    type="text"
                    placeholder="ex: 10.10.0.10"
                  />
                  <small class="text-muted">IP-ul WireGuard al miniPC-ului beneficiarului</small>
                  <validation-error :errors="apiValidationErrors.mqtt_host" />
                </div>

                <div class="col-md-3">
                  <label class="form-label mt-3">Port TLS</label>
                  <soft-model-input
                    id="mqtt_port"
                    v-model="mqttConfig.mqtt_port"
                    type="number"
                    placeholder="8883"
                  />
                  <validation-error :errors="apiValidationErrors.mqtt_port" />
                </div>

                <div class="col-md-3">
                  <label class="form-label mt-3">Prefix Topicuri</label>
                  <soft-model-input
                    id="mqtt_topic_prefix"
                    v-model="mqttConfig.mqtt_topic_prefix"
                    type="text"
                    placeholder="ex: atria"
                  />
                  <small class="text-muted">Pentru {prefix}/cmd/...</small>
                  <validation-error :errors="apiValidationErrors.mqtt_topic_prefix" />
                </div>
              </div>

              <div class="mt-4 pt-3 border-top">
                <h6 class="mb-1">Certificate mTLS</h6>
                <p class="text-sm text-muted mb-3">
                  Path-uri către certificatele stocate pe VPS în <code>/etc/mqtt/tenants/{slug}/</code>
                </p>

                <div class="row">
                  <div class="col-12 mb-3">
                    <label class="form-label">CA Chain (Certificate Authority)</label>
                    <soft-model-input
                      id="mqtt_ca_path"
                      v-model="mqttConfig.mqtt_ca_path"
                      type="text"
                      placeholder="/etc/mqtt/tenants/atria/ca-chain.crt"
                    />
                    <validation-error :errors="apiValidationErrors.mqtt_ca_path" />
                  </div>

                  <div class="col-md-6 mb-3">
                    <label class="form-label">Certificat Client VPS</label>
                    <soft-model-input
                      id="mqtt_client_cert_path"
                      v-model="mqttConfig.mqtt_client_cert_path"
                      type="text"
                      placeholder="/etc/mqtt/tenants/atria/client.crt"
                    />
                    <validation-error :errors="apiValidationErrors.mqtt_client_cert_path" />
                  </div>

                  <div class="col-md-6 mb-3">
                    <label class="form-label">Cheie Privată Client VPS</label>
                    <soft-model-input
                      id="mqtt_client_key_path"
                      v-model="mqttConfig.mqtt_client_key_path"
                      type="text"
                      placeholder="/etc/mqtt/tenants/atria/client.key"
                    />
                    <small class="text-muted">Permisiuni: 600 root:root</small>
                    <validation-error :errors="apiValidationErrors.mqtt_client_key_path" />
                  </div>
                </div>
              </div>

              <!-- Quick Fill Button -->
              <div class="mb-3">
                <button class="btn btn-outline-secondary btn-sm" @click="autoFillMqttPaths">
                  <i class="fas fa-magic me-1"></i>
                  Auto-completare path-uri
                </button>
                <small class="text-muted ms-2">Completează path-urile standard bazat pe slug-ul beneficiarului</small>
              </div>

              <soft-button
                color="dark"
                variant="gradient"
                class="float-end mt-4 mb-0"
                size="sm"
                :is-disabled="savingMqtt"
                @click="saveMqttConfig"
              >
                <span v-if="savingMqtt" class="spinner-border spinner-border-sm"></span>
                <span v-else>Salvează Configurare MQTT</span>
              </soft-button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Scară -->
    <div v-if="showStaircaseModal">
      <div class="modal-backdrop fade show" style="z-index: 1040"></div>
      <div class="modal fade show d-block" tabindex="-1" style="z-index: 1050">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h6 class="modal-title">{{ editingStaircase ? 'Editează scară' : 'Adaugă scară' }}</h6>
              <button type="button" class="btn-close" @click="closeStaircaseModal"></button>
            </div>
            <div class="modal-body">
              <div class="mb-3">
                <label class="form-label">Cod scară *</label>
                <input class="form-control" v-model="staircaseForm.code" placeholder="ex: A, B, 1, 2" />
              </div>
              <div class="mb-3">
                <label class="form-label">Nume (opțional)</label>
                <input class="form-control" v-model="staircaseForm.name" placeholder="ex: Scara din spate" />
              </div>
              <div class="mb-3">
                <label class="form-label">Ordine</label>
                <input class="form-control" type="number" v-model.number="staircaseForm.sort_order" />
              </div>
            </div>
            <div class="modal-footer">
              <button class="btn btn-outline-secondary" @click="closeStaircaseModal">Anulează</button>
              <button class="btn bg-gradient-primary" @click="saveStaircase" :disabled="savingStaircase">
                {{ savingStaircase ? 'Se salvează...' : 'Salvează' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script>
import SoftModelInput from "/src/components/SoftModelInput.vue";
import SoftModelTextarea from "/src/components/SoftModelTextarea.vue";
import SoftButton from "/src/components/SoftButton.vue";
import ValidationError from "@/components/ValidationError.vue";
import showSwal from "/src/mixins/showSwal.js";
import formMixin from "/src/mixins/form-mixin.js";
import phoneFormatter from "/src/mixins/phoneFormatter.js";
import carteImobilService from "/src/services/carte-imobil.service.js";
import axios from "axios";

export default {
  name: "EditTenant",
  components: {
    SoftModelInput,
    SoftModelTextarea,
    SoftButton,
    ValidationError,
  },
  mixins: [formMixin, phoneFormatter],
  data() {
    return {
      activeTab: "info",
      loadingInfo: false,
      loadingBuilding: false,
      
      // Tab Info
      tenant: {
        id: null,
        name: "",
        address: "",
        fiscal_code: "",
        description: "",
        registration_instructions: "",
        contact_data: {
          person: "",
          email: "",
          phone: "",
        },
      },

      // Tab Building
      staircases: [],
      apartmentsGroups: [],
      apartmentsList: [],
      apartmentsByStaircase: {},
      removeMissing: false,
      floorsInput: "P, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10",
      floorOptions: ["P", "1", "2", "3", "4", "5", "6", "7", "8", "9", "10"],
      showStaircaseModal: false,
      editingStaircase: null,
      staircaseForm: { code: "", name: "", sort_order: 0 },
      savingStaircase: false,

      // Tab Technical - MQTT mTLS Configuration
      mqttConfig: {
        mqtt_host: "",
        mqtt_port: 8883,
        mqtt_ca_path: "",
        mqtt_client_cert_path: "",
        mqtt_client_key_path: "",
        mqtt_topic_prefix: "",
      },
      savingMqtt: false,
      testingMqtt: false,
      mqttConnectionStatus: null, // 'ok', 'error', null
      mqttStatusMessage: "Conexiune netestată",
    };
  },

  computed: {
    isSysadmin() {
      const user = JSON.parse(localStorage.getItem("user") || "{}");
      return user?.roles?.some((r) => r.name === "sysadmin") || false;
    },
    mqttStatusClass() {
      if (this.mqttConnectionStatus === 'ok') return 'alert-success';
      if (this.mqttConnectionStatus === 'error') return 'alert-danger';
      return 'alert-secondary';
    },
  },

  watch: {
    activeTab(newTab) {
      if (newTab === "building" && this.staircases.length === 0) {
        this.loadBuilding();
      }
    },
  },

  async created() {
    await this.loadTenant();
  },

  methods: {
    // === INFO TAB ===
    async loadTenant() {
      try {
        const loadedTenant = await this.$store.dispatch("tenants/getTenant", this.$route.params.id);
        this.tenant = {
          id: loadedTenant.id,
          name: loadedTenant.name || "",
          address: loadedTenant.address || "",
          fiscal_code: loadedTenant.fiscal_code || "",
          description: loadedTenant.description || "",
          registration_instructions: loadedTenant.registration_instructions || "",
          contact_data: loadedTenant.contact_data || {
            person: "",
            email: "",
            phone: "",
          },
        };
        
        // Load MQTT config for sysadmin
        if (this.isSysadmin) {
          this.mqttConfig = {
            mqtt_host: loadedTenant.mqtt_host || "",
            mqtt_port: loadedTenant.mqtt_port || 8883,
            mqtt_ca_path: loadedTenant.mqtt_ca_path || "",
            mqtt_client_cert_path: loadedTenant.mqtt_client_cert_path || "",
            mqtt_client_key_path: loadedTenant.mqtt_client_key_path || "",
            mqtt_topic_prefix: loadedTenant.mqtt_topic_prefix || "",
          };
        }
      } catch (error) {
        showSwal.methods.showSwal({ type: "error", message: "Nu am putut încărca beneficiarul." });
      }
    },

    async saveInfo() {
      this.resetApiValidation();
      this.loadingInfo = true;
      try {
        await this.$store.dispatch("tenants/editTenant", this.tenant);
        showSwal.methods.showSwal({ type: "success", message: "Informații actualizate cu succes!" });
      } catch (error) {
        if (error.response?.data?.errors) {
          this.setApiValidation(error.response.data.errors);
        } else {
          showSwal.methods.showSwal({ type: "error", message: "Eroare la salvare." });
        }
      } finally {
        this.loadingInfo = false;
      }
    },

    // === BUILDING TAB ===
    async loadBuilding() {
      this.loadingBuilding = true;
      try {
        const [buildingRes, apartmentsRes] = await Promise.all([
          carteImobilService.getTenantBuilding(this.tenant.id),
          carteImobilService.getApartmentsList(this.tenant.id).catch(() => ({ data: { apartments: [] } })),
        ]);
        
        this.staircases = buildingRes?.data?.staircases || [];
        this.apartmentsGroups = buildingRes?.data?.apartments || [];
        this.apartmentsList = apartmentsRes?.data?.apartments || [];

        const map = {};
        this.apartmentsGroups.forEach((g) => {
          map[g.staircase] = (g.numbers || []).join(", ");
        });
        this.apartmentsByStaircase = map;
      } catch (e) {
        console.error("Load building error:", e);
      } finally {
        this.loadingBuilding = false;
      }
    },

    getApartmentsForStaircase(code) {
      return this.apartmentsList.filter((a) => a.staircase === code);
    },

    parseFloors() {
      this.floorOptions = (this.floorsInput || "").split(",").map((f) => f.trim()).filter((f) => f.length > 0);
    },

    async onFloorChange(apt, newFloor) {
      try {
        await carteImobilService.updateApartmentFloor(apt.id, newFloor || null);
        const found = this.apartmentsList.find((a) => a.id === apt.id);
        if (found) found.floor = newFloor || null;
      } catch (e) {
        showSwal.methods.showSwal({ type: "error", message: "Nu am putut salva etajul." });
      }
    },

    openAddStaircase() {
      this.editingStaircase = null;
      this.staircaseForm = { code: "", name: "", sort_order: 0 };
      this.showStaircaseModal = true;
    },

    openEditStaircase(s) {
      this.editingStaircase = s;
      this.staircaseForm = { code: s.code, name: s.name || "", sort_order: s.sort_order || 0 };
      this.showStaircaseModal = true;
    },

    closeStaircaseModal() {
      this.showStaircaseModal = false;
    },

    async saveStaircase() {
      if (!this.staircaseForm.code?.trim()) {
        showSwal.methods.showSwal({ type: "error", message: "Codul scării este obligatoriu." });
        return;
      }

      this.savingStaircase = true;
      try {
        if (this.editingStaircase) {
          await carteImobilService.updateStaircase(this.editingStaircase.id, this.staircaseForm);
          showSwal.methods.showSwal({ type: "success", message: "Scara a fost actualizată." });
        } else {
          await carteImobilService.addStaircase(this.staircaseForm);
          showSwal.methods.showSwal({ type: "success", message: "Scara a fost adăugată." });
        }
        this.closeStaircaseModal();
        await this.loadBuilding();
      } catch (e) {
        showSwal.methods.showSwal({ type: "error", message: e?.response?.data?.message || "Eroare la salvare." });
      } finally {
        this.savingStaircase = false;
      }
    },

    async onDeleteStaircase(s) {
      const result = await showSwal.methods.showSwalConfirmationDelete();
      if (!result.isConfirmed) return;

      try {
        await carteImobilService.deleteStaircase(s.id);
        showSwal.methods.showSwal({ type: "success", message: "Scara a fost ștearsă." });
        await this.loadBuilding();
      } catch (e) {
        showSwal.methods.showSwal({ type: "error", message: "Nu am putut șterge scara." });
      }
    },

    async onSyncApartments(s) {
      const raw = this.apartmentsByStaircase[s.code] || "";
      const numbers = raw.split(",").map((x) => x.trim()).filter((x) => x.length > 0);
      
      if (numbers.length === 0) {
        showSwal.methods.showSwal({ type: "error", message: "Introdu cel puțin un apartament." });
        return;
      }

      try {
        await carteImobilService.syncApartments(s.id, numbers, this.removeMissing);
        showSwal.methods.showSwal({ type: "success", message: "Apartamentele au fost salvate." });
        await this.loadBuilding();
      } catch (e) {
        showSwal.methods.showSwal({ type: "error", message: "Nu am putut salva apartamentele." });
      }
    },

    // === TECHNICAL TAB - MQTT mTLS Configuration ===
    autoFillMqttPaths() {
      const slug = this.tenant.name
        ? this.tenant.name.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '')
        : 'tenant';
      
      const basePath = `/etc/mqtt/tenants/${slug}`;
      this.mqttConfig.mqtt_ca_path = `${basePath}/ca-chain.crt`;
      this.mqttConfig.mqtt_client_cert_path = `${basePath}/client.crt`;
      this.mqttConfig.mqtt_client_key_path = `${basePath}/client.key`;
      this.mqttConfig.mqtt_topic_prefix = slug;
    },

    async saveMqttConfig() {
      this.resetApiValidation();
      this.savingMqtt = true;
      
      try {
        // Merge MQTT config with tenant data
        const tenantData = {
          ...this.tenant,
          ...this.mqttConfig,
        };
        
        await this.$store.dispatch("tenants/editTenant", tenantData);
        showSwal.methods.showSwal({ type: "success", message: "Configurare MQTT salvată cu succes!" });
      } catch (error) {
        if (error.response?.data?.errors) {
          this.setApiValidation(error.response.data.errors);
        } else {
          showSwal.methods.showSwal({ type: "error", message: "Eroare la salvarea configurării MQTT." });
        }
      } finally {
        this.savingMqtt = false;
      }
    },

    async testMqttConnection() {
      if (!this.mqttConfig.mqtt_host) {
        showSwal.methods.showSwal({ type: "warning", message: "Configurează host-ul MQTT înainte de test." });
        return;
      }

      this.testingMqtt = true;
      this.mqttConnectionStatus = null;
      this.mqttStatusMessage = "Se testează conexiunea...";

      try {
        const res = await axios.post(`/api/v2/tenants/${this.tenant.id}/test-mqtt`);
        
        if (res.data?.success) {
          this.mqttConnectionStatus = 'ok';
          this.mqttStatusMessage = res.data.message || "Conexiune mTLS reușită!";
          showSwal.methods.showSwal({ type: "success", message: this.mqttStatusMessage });
        } else {
          this.mqttConnectionStatus = 'error';
          this.mqttStatusMessage = res.data?.message || "Eroare la conexiune";
          showSwal.methods.showSwal({ type: "error", message: this.mqttStatusMessage });
        }
      } catch (e) {
        this.mqttConnectionStatus = 'error';
        this.mqttStatusMessage = e.response?.data?.message || "Eroare la testarea conexiunii";
        showSwal.methods.showSwal({ type: "error", message: this.mqttStatusMessage });
      } finally {
        this.testingMqtt = false;
      }
    },
  },
};
</script>

<style scoped>
.nav-tabs .nav-link {
  cursor: pointer;
  color: #344767;
  border: none;
  border-bottom: 2px solid transparent;
  padding: 0.75rem 1rem;
}

.nav-tabs .nav-link:hover {
  border-bottom-color: #dee2e6;
}

.nav-tabs .nav-link.active {
  color: #cb0c9f;
  border-bottom-color: #cb0c9f;
  font-weight: 600;
}

.btn-xs {
  padding: 0.25rem 0.5rem;
  font-size: 0.75rem;
}

.accordion-button:not(.collapsed) {
  background-color: #f8f9fa;
}

/* Status indicator */
.status-indicator {
  width: 12px;
  height: 12px;
  border-radius: 50%;
  display: inline-block;
  box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.8);
}

.status-connected {
  background-color: #2dce89;
  box-shadow: 0 0 8px rgba(45, 206, 137, 0.6);
  animation: pulse-green 2s infinite;
}

.status-error {
  background-color: #f5365c;
  box-shadow: 0 0 8px rgba(245, 54, 92, 0.6);
}

.status-timeout {
  background-color: #fb6340;
  box-shadow: 0 0 8px rgba(251, 99, 64, 0.6);
  animation: pulse-orange 2s infinite;
}

.status-unknown {
  background-color: #8898aa;
}

@keyframes pulse-green {
  0%, 100% {
    box-shadow: 0 0 0 0 rgba(45, 206, 137, 0.4);
  }
  50% {
    box-shadow: 0 0 0 6px rgba(45, 206, 137, 0);
  }
}

@keyframes pulse-orange {
  0%, 100% {
    box-shadow: 0 0 0 0 rgba(251, 99, 64, 0.4);
  }
  50% {
    box-shadow: 0 0 0 6px rgba(251, 99, 64, 0);
  }
}
</style>
