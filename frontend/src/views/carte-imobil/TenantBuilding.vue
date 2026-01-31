<template>
  <div class="py-4 container-fluid">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header pb-0">
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <h5 class="mb-0">Configurare imobil</h5>
                <p class="text-sm mb-0">Definește scările și apartamentele pentru tenantul selectat.</p>
              </div>
              <button class="btn bg-gradient-primary btn-sm mb-0" @click="openAddStaircase">
                + Adaugă scară
              </button>
            </div>
            <!-- Selector tenant pentru sysadmin -->
            <div v-if="isSysadmin" class="mt-3">
              <label class="form-label text-sm">Selectează asociația:</label>
              <select class="form-select form-select-sm" v-model="selectedTenantId" @change="load" style="max-width: 350px;">
                <option v-for="t in tenants" :key="t.id" :value="t.id">
                  {{ t.name }}
                </option>
              </select>
            </div>
          </div>
          <div class="card-body">
            <div v-if="loading" class="text-sm">Se încarcă...</div>
            <div v-else-if="loadError" class="text-sm text-danger">
              {{ loadError }}
              <button class="btn btn-link btn-sm p-0 ms-2" @click="load">Reîncearcă</button>
            </div>
            <div v-else>
              <div v-if="staircases.length === 0" class="text-sm text-muted">
                Nu există scări. Adaugă prima scară.
              </div>

              <div class="accordion" id="staircasesAcc">
                <div class="accordion-item" v-for="s in staircases" :key="s.id">
                  <h2 class="accordion-header" :id="`h-${s.id}`">
                    <button
                      class="accordion-button collapsed"
                      type="button"
                      data-bs-toggle="collapse"
                      :data-bs-target="`#c-${s.id}`"
                      aria-expanded="false"
                      :aria-controls="`c-${s.id}`"
                    >
                      <strong>{{ s.code }}</strong>
                      <span class="ms-2 text-muted" v-if="s.name">— {{ s.name }}</span>
                    </button>
                  </h2>
                  <div :id="`c-${s.id}`" class="accordion-collapse collapse" :aria-labelledby="`h-${s.id}`" data-bs-parent="#staircasesAcc">
                    <div class="accordion-body">
                      <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
                        <button class="btn btn-outline-dark btn-sm mb-0" @click="openEditStaircase(s)">Editează scara</button>
                        <button class="btn btn-outline-danger btn-sm mb-0" @click="onDeleteStaircase(s)">Șterge scara</button>
                      </div>

                      <label class="form-label">Apartamente (numere, separate prin virgulă)</label>
                      <textarea class="form-control" rows="2" v-model="apartmentsByStaircase[s.code]" />
                      <div class="d-flex gap-2 mt-2">
                        <div class="form-check">
                          <input class="form-check-input" type="checkbox" v-model="removeMissing" :id="`rm-${s.id}`" />
                          <label class="form-check-label" :for="`rm-${s.id}`">Șterge apartamentele care lipsesc din listă</label>
                        </div>
                      </div>
                      <!-- Tabel apartamente cu etaje -->
                      <div class="mt-4" v-if="getApartmentsForStaircase(s.code).length > 0">
                        <label class="form-label">Configurare etaje per apartament</label>
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
                                <td>{{ apt.number }}</td>
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
                        Salvează apartamentele
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Configurare etaje disponibile -->
              <div class="mt-4">
                <label class="form-label">Etaje disponibile (separate prin virgulă)</label>
                <div class="d-flex gap-2">
                  <input 
                    class="form-control" 
                    v-model="floorsInput" 
                    placeholder="ex: P, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10"
                    @blur="parseFloors"
                  />
                </div>
                <small class="text-muted">Introdu etajele disponibile în imobil (P = parter)</small>
              </div>

              <hr class="horizontal dark my-4" />
              <div class="text-xs text-muted">
                Notă: după schimbarea permisiunilor, e recomandat logout/login pentru a reîncărca lista de permisiuni în UI.
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- modal scara -->
    <div v-if="showModal">
      <div class="modal-backdrop fade show" style="z-index: 1040"></div>
      <div class="modal fade show d-block" tabindex="-1" role="dialog" style="z-index: 1050">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h6 class="modal-title">{{ editing ? "Editează scară" : "Adaugă scară" }}</h6>
              <button type="button" class="btn-close" @click="closeModal"></button>
            </div>
            <div class="modal-body">
              <div class="mb-3">
                <label class="form-label">Cod scară *</label>
                <input class="form-control" v-model="form.code" />
              </div>
              <div class="mb-3">
                <label class="form-label">Nume scară</label>
                <input class="form-control" v-model="form.name" />
              </div>
              <div class="mb-3">
                <label class="form-label">Ordine</label>
                <input class="form-control" type="number" v-model.number="form.sort_order" />
              </div>
            </div>
            <div class="modal-footer">
              <button class="btn btn-outline-secondary" @click="closeModal" :disabled="saving">Anulează</button>
              <button class="btn bg-gradient-primary" @click="onSaveStaircase" :disabled="saving">
                <span v-if="saving">Se salvează...</span>
                <span v-else>Salvează</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import showSwal from "/src/mixins/showSwal.js";
import carteImobilService from "/src/services/carte-imobil.service.js";

export default {
  name: "TenantBuilding",
  data() {
    return {
      loading: true,
      loadError: null,
      staircases: [],
      apartmentsGroups: [],
      apartmentsList: [],
      apartmentsByStaircase: {},
      removeMissing: false,
      showModal: false,
      editing: null,
      form: { code: "", name: "", sort_order: 0 },
      saving: false,
      floorsInput: "P, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10",
      floorOptions: ["P", "1", "2", "3", "4", "5", "6", "7", "8", "9", "10"],
      tenants: [],
      selectedTenantId: null,
    };
  },
  computed: {
    isSysadmin() {
      const user = JSON.parse(localStorage.getItem("user") || "{}");
      const roles = user?.roles || [];
      return roles.some((r) => r.name === "sysadmin");
    },
  },
  async mounted() {
    await this.loadTenants();
    await this.load();
  },
  methods: {
    async loadTenants() {
      if (!this.isSysadmin) return;
      try {
        const res = await carteImobilService.getTenantsList();
        this.tenants = res?.data?.tenants || [];
        // Default: primul tenant sau user-ul curent
        if (this.tenants.length > 0 && !this.selectedTenantId) {
          const user = JSON.parse(localStorage.getItem("user") || "{}");
          this.selectedTenantId = user?.tenant_id || this.tenants[0].id;
        }
      } catch (e) {
        console.error("Failed to load tenants:", e);
      }
    },
    async load() {
      this.loading = true;
      this.loadError = null;
      try {
        const tenantParam = this.isSysadmin && this.selectedTenantId ? this.selectedTenantId : null;
        const [buildingRes, apartmentsRes] = await Promise.all([
          carteImobilService.getTenantBuilding(tenantParam),
          carteImobilService.getApartmentsList(tenantParam).catch(() => ({ data: { apartments: [] } })),
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
        console.error("Load tenant building error:", e);
        const msg = e?.response?.data?.errors?.[0]?.message 
          || e?.response?.data?.message 
          || e?.message 
          || "Nu am putut încărca configurarea.";
        this.loadError = msg;
        showSwal.methods.showSwal({ type: "error", message: msg });
      } finally {
        this.loading = false;
      }
    },
    getCurrentNumbers(code) {
      const group = this.apartmentsGroups.find((g) => g.staircase === code);
      return group?.numbers || [];
    },
    getApartmentsForStaircase(staircaseCode) {
      return this.apartmentsList.filter((a) => a.staircase === staircaseCode);
    },
    parseFloors() {
      this.floorOptions = (this.floorsInput || "")
        .split(",")
        .map((f) => f.trim())
        .filter((f) => f.length > 0);
    },
    async onFloorChange(apt, newFloor) {
      try {
        await carteImobilService.updateApartmentFloor(apt.id, newFloor || null);
        // Update local state
        const found = this.apartmentsList.find((a) => a.id === apt.id);
        if (found) {
          found.floor = newFloor || null;
        }
      } catch (e) {
        const msg = e?.response?.data?.errors?.[0]?.message || "Nu am putut salva etajul.";
        showSwal.methods.showSwal({ type: "error", message: msg });
      }
    },
    openAddStaircase() {
      this.editing = null;
      this.form = { code: "", name: "", sort_order: 0 };
      this.showModal = true;
    },
    openEditStaircase(s) {
      this.editing = s;
      this.form = { code: s.code, name: s.name || "", sort_order: s.sort_order || 0 };
      this.showModal = true;
    },
    closeModal() {
      this.showModal = false;
    },
    async onSaveStaircase() {
      if (!this.form.code || !this.form.code.trim()) {
        showSwal.methods.showSwal({ type: "error", message: "Codul scării este obligatoriu." });
        return;
      }

      this.saving = true;
      try {
        if (this.editing) {
          const res = await carteImobilService.updateStaircase(this.editing.id, {
            code: this.form.code.trim(),
            name: this.form.name?.trim() || null,
            sort_order: this.form.sort_order || 0,
          });
          if (res?.success) {
            showSwal.methods.showSwal({ type: "success", message: "Scara a fost actualizată." });
          }
        } else {
          const res = await carteImobilService.addStaircase({
            code: this.form.code.trim(),
            name: this.form.name?.trim() || null,
            sort_order: this.form.sort_order || 0,
          });
          if (res?.success) {
            showSwal.methods.showSwal({ type: "success", message: "Scara a fost adăugată." });
          }
        }
        this.closeModal();
        await this.load();
      } catch (e) {
        console.error("Save staircase error:", e);
        const msg = e?.response?.data?.errors?.[0]?.message 
          || e?.response?.data?.message 
          || e?.message 
          || "Nu am putut salva scara.";
        showSwal.methods.showSwal({ type: "error", message: msg });
      } finally {
        this.saving = false;
      }
    },
    async onDeleteStaircase(s) {
      const result = await showSwal.methods.showSwalConfirmationDelete();
      if (!result.isConfirmed) return;

      try {
        const res = await carteImobilService.deleteStaircase(s.id);
        if (res?.success) {
          showSwal.methods.showSwal({ type: "success", message: "Scara a fost ștearsă." });
          await this.load();
        } else {
          showSwal.methods.showSwal({ type: "error", message: "Nu am putut șterge scara." });
        }
      } catch (e) {
        const msg = e?.response?.data?.errors?.[0]?.message || "Nu am putut șterge scara.";
        showSwal.methods.showSwal({ type: "error", message: msg });
      }
    },
    parseNumbers(raw) {
      return (raw || "")
        .split(",")
        .map((x) => x.trim())
        .filter((x) => x.length > 0);
    },
    async onSyncApartments(s) {
      const raw = this.apartmentsByStaircase[s.code] || "";
      const numbers = this.parseNumbers(raw);
      if (numbers.length === 0) {
        showSwal.methods.showSwal({ type: "error", message: "Te rog introdu cel puțin un număr de apartament." });
        return;
      }

      try {
        const res = await carteImobilService.syncApartments(s.id, numbers, this.removeMissing);
        if (res?.success) {
          showSwal.methods.showSwal({ type: "success", message: "Apartamentele au fost salvate." });
          await this.load();
        } else {
          showSwal.methods.showSwal({ type: "error", message: "Nu am putut salva apartamentele." });
        }
      } catch (e) {
        const msg = e?.response?.data?.errors?.[0]?.message || "Nu am putut salva apartamentele.";
        showSwal.methods.showSwal({ type: "error", message: msg });
      }
    },
  },
};
</script>

