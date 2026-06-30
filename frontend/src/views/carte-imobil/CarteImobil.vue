<template>
  <div class="py-4 container-fluid">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header pb-0 d-flex align-items-center justify-content-between">
            <div>
              <h5 class="mb-0">Carte de imobil</h5>
              <p class="text-sm mb-0">Gestionează persoanele din apartament și trimite spre validare.</p>
            </div>
            <div class="d-flex gap-2">
              <button class="btn btn-outline-dark btn-sm mb-0" :disabled="!selectedApartmentId" @click="onExport">
                Export PDF
              </button>
              <button class="btn bg-gradient-primary btn-sm mb-0" :disabled="!selectedApartmentId || isLocked" @click="openCreate">
                + Adaugă persoană
              </button>
              <!-- Manager (CEX/admin): aprobă/respinge direct, fără validare externă -->
              <template v-if="isManager">
                <button
                  class="btn bg-gradient-success btn-sm mb-0"
                  :disabled="!selectedApartmentId || !hasPending"
                  @click="onApproveAll"
                >
                  Aprobă tot
                </button>
                <button
                  class="btn bg-gradient-warning btn-sm mb-0"
                  :disabled="!hasSubmitted"
                  @click="onRejectAll"
                >
                  Respinge tot
                </button>
              </template>
              <!-- Locatar: trimite lista spre validarea comitetului -->
              <button
                v-else
                class="btn bg-gradient-success btn-sm mb-0"
                :disabled="!canSubmit"
                @click="onSubmit"
              >
                Trimite spre validare
              </button>
            </div>
          </div>

          <div class="card-body">
            <div v-if="loading" class="text-sm">Se încarcă...</div>

            <div v-else>
              <div class="row mb-3">
                <div class="col-md-4">
                  <label class="form-label">{{ isManager ? "Apartament" : "Apartamentul meu" }}</label>
                  <select class="form-select" v-model="selectedApartmentId" @change="loadOccupants">
                    <option value="" disabled>Alege apartamentul</option>
                    <option v-for="a in apartments" :key="a.id" :value="a.id">
                      Ap. {{ a.number }}{{ a.staircase ? " | Scara " + a.staircase : "" }}{{ a.floor ? " | Etaj " + a.floor : "" }}
                    </option>
                  </select>
                </div>
                <div class="col-md-8 d-flex align-items-end">
                  <div class="text-sm">
                    <span v-if="isManager" class="badge bg-gradient-primary">Administrare comitet (toate apartamentele)</span>
                    <span v-else-if="isLocked" class="badge bg-gradient-info">Editare înghețată (submitted/approved)</span>
                    <span v-else class="badge bg-gradient-secondary">Draft</span>
                  </div>
                </div>
              </div>

              <div class="table-responsive">
                <table class="table table-flush">
                  <thead class="thead-light">
                    <tr>
                      <th>Nume</th>
                      <th>Calitate</th>
                      <th>Data sosire</th>
                      <th>Status</th>
                      <th>Acțiuni</th>
                    </tr>
                  </thead>
                  <tbody class="text-sm">
                    <tr v-if="occupants.length === 0">
                      <td colspan="5" class="text-center text-muted">Nu există persoane.</td>
                    </tr>
                    <template v-for="o in occupants" :key="o.id">
                    <tr>
                      <td>
                        <div class="d-flex flex-column">
                          <strong>{{ o.last_name }} {{ o.first_name }}</strong>
                          <small class="text-muted" v-if="o.cnp_masked">CNP: {{ o.cnp_masked }}</small>
                        </div>
                      </td>
                      <td>
                        <span class="badge bg-gradient-secondary">
                          {{ o.role_in_unit === "other" ? (o.other_role_text || "altul") : mapRole(o.role_in_unit) }}
                        </span>
                      </td>
                      <td>{{ o.move_in_date }}</td>
                      <td>
                        <span class="badge" :class="statusBadge(o.status)">{{ mapStatus(o.status) }}</span>
                        <div v-if="o.status === 'rejected' && o.reject_reason" class="text-xs text-danger mt-1">
                          Motiv: {{ o.reject_reason }}
                        </div>
                      </td>
                      <td>
                        <button class="btn btn-outline-secondary btn-sm mb-0 me-2" @click="toggleDetails(o.id)">
                          {{ expanded[o.id] ? "Ascunde" : "Detalii" }}
                        </button>
                        <button class="btn btn-outline-dark btn-sm mb-0 me-2" :disabled="!canEdit(o)" @click="openEdit(o)">
                          Editează
                        </button>
                        <button class="btn btn-outline-danger btn-sm mb-0" :disabled="!canEdit(o)" @click="onDelete(o)">
                          Șterge
                        </button>
                      </td>
                    </tr>
                    <tr v-if="expanded[o.id]" class="bg-light">
                      <td colspan="5">
                        <div class="row g-2 text-sm p-2">
                          <div class="col-md-4"><strong>CNP:</strong> {{ o.cnp || o.cnp_masked || "—" }}</div>
                          <div class="col-md-4"><strong>CI:</strong> {{ ciDisplay(o) }}</div>
                          <div class="col-md-4"><strong>Domiciliu:</strong> {{ o.domicile_address || "—" }}</div>
                          <div class="col-md-4"><strong>Calitate:</strong> {{ o.role_in_unit === "other" ? (o.other_role_text || "altul") : mapRole(o.role_in_unit) }}</div>
                          <div class="col-md-4"><strong>Data sosire:</strong> {{ o.move_in_date || "—" }}</div>
                          <div class="col-md-4"><strong>Data plecare:</strong> {{ o.move_out_date || "—" }}</div>
                          <div class="col-md-4"><strong>Minor:</strong> {{ o.is_minor ? "Da" : "Nu" }}</div>
                          <div class="col-md-4" v-if="o.is_minor"><strong>Reprezentant legal:</strong> {{ o.legal_guardian_name || "—" }}</div>
                          <div class="col-md-4"><strong>Telefon:</strong> {{ o.phone || "—" }}</div>
                          <div class="col-md-4"><strong>Email:</strong> {{ o.email || "—" }}</div>
                          <div class="col-md-8"><strong>Notițe:</strong> {{ o.notes || "—" }}</div>
                          <div class="col-12" v-if="o.reject_reason"><strong>Motiv respingere:</strong> {{ o.reject_reason }}</div>
                          <div class="col-md-6 text-muted"><small>Creat: {{ o.created_at || "—" }}</small></div>
                          <div class="col-md-6 text-muted"><small>Actualizat: {{ o.updated_at || "—" }}</small></div>
                        </div>
                      </td>
                    </tr>
                    </template>
                  </tbody>
                </table>
              </div>

              <div class="mt-3">
                <small class="text-muted">
                  Date sensibile: CNP/CI sunt mascate pentru membri (conform configurării). Exportul PDF este disponibil doar după aprobare.
                </small>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal simplu -->
    <div v-if="showModal">
      <div class="modal-backdrop fade show" style="z-index: 1040"></div>
      <div class="modal fade show d-block" tabindex="-1" role="dialog" style="z-index: 1050">
        <div class="modal-dialog modal-lg" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h6 class="modal-title">{{ editing ? "Editează persoană" : "Adaugă persoană" }}</h6>
              <button type="button" class="btn-close" @click="closeModal"></button>
            </div>
            <div class="modal-body">
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label">Prenume *</label>
                  <input class="form-control" v-model="form.first_name" />
                  <small class="text-danger" v-if="errors.first_name">{{ errors.first_name }}</small>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Nume *</label>
                  <input class="form-control" v-model="form.last_name" />
                  <small class="text-danger" v-if="errors.last_name">{{ errors.last_name }}</small>
                </div>

                <div class="col-md-6">
                  <label class="form-label">CNP</label>
                  <input class="form-control" v-model="form.cnp" placeholder="13 cifre" />
                  <small class="text-danger" v-if="errors.cnp">{{ errors.cnp }}</small>
                </div>
                <div class="col-md-3">
                  <label class="form-label">Seria CI</label>
                  <input class="form-control" v-model="form.id_series" />
                </div>
                <div class="col-md-3">
                  <label class="form-label">Număr CI</label>
                  <input class="form-control" v-model="form.id_number" />
                </div>

                <div class="col-12">
                  <label class="form-label">Domiciliu *</label>
                  <textarea class="form-control" rows="2" v-model="form.domicile_address"></textarea>
                  <small class="text-danger" v-if="errors.domicile_address">{{ errors.domicile_address }}</small>
                </div>

                <div class="col-md-6">
                  <label class="form-label">Calitatea *</label>
                  <select class="form-select" v-model="form.role_in_unit">
                    <option value="owner">Proprietar</option>
                    <option value="tenant">Chiriaș</option>
                    <option value="family">Membru familie</option>
                    <option value="other">Altul</option>
                  </select>
                </div>
                <div class="col-md-6" v-if="form.role_in_unit === 'other'">
                  <label class="form-label">Altă calitate *</label>
                  <input class="form-control" v-model="form.other_role_text" />
                  <small class="text-danger" v-if="errors.other_role_text">{{ errors.other_role_text }}</small>
                </div>

                <div class="col-md-6">
                  <label class="form-label">Data sosire *</label>
                  <input class="form-control" type="date" v-model="form.move_in_date" />
                  <small class="text-danger" v-if="errors.move_in_date">{{ errors.move_in_date }}</small>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Data plecare</label>
                  <input class="form-control" type="date" v-model="form.move_out_date" />
                  <small class="text-danger" v-if="errors.move_out_date">{{ errors.move_out_date }}</small>
                </div>

                <div class="col-md-4">
                  <label class="form-label">Minor</label>
                  <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" v-model="form.is_minor" />
                    <label class="form-check-label">Este minor</label>
                  </div>
                </div>
                <div class="col-md-8" v-if="form.is_minor">
                  <label class="form-label">Reprezentant legal *</label>
                  <input class="form-control" v-model="form.legal_guardian_name" />
                  <small class="text-danger" v-if="errors.legal_guardian_name">{{ errors.legal_guardian_name }}</small>
                </div>

                <div class="col-md-6">
                  <label class="form-label">Telefon</label>
                  <input class="form-control" v-model="form.phone" />
                </div>
                <div class="col-md-6">
                  <label class="form-label">Email</label>
                  <input class="form-control" v-model="form.email" />
                </div>
                <div class="col-12">
                  <label class="form-label">Notițe</label>
                  <textarea class="form-control" rows="2" v-model="form.notes"></textarea>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button class="btn btn-outline-secondary" @click="closeModal">Anulează</button>
              <button class="btn bg-gradient-primary" @click="onSave">Salvează</button>
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
import { hasAnyPermission } from "@/utils/permissions";

export default {
  name: "CarteImobil",
  data() {
    return {
      loading: true,
      isManager: false,
      apartments: [],
      selectedApartmentId: "",
      occupants: [],
      showModal: false,
      editing: null,
      form: this.emptyForm(),
      errors: {},
      expanded: {},
    };
  },
  computed: {
    isLocked() {
      // Managerii de tenant (admin/cex) pot adăuga oricând, indiferent de status.
      if (this.isManager) return false;
      return this.occupants.some((o) => ["submitted", "approved"].includes(o.status));
    },
    canSubmit() {
      if (!this.selectedApartmentId) return false;
      if (this.occupants.length === 0) return false;
      // allow submit only if all are draft/rejected
      const okStatuses = this.occupants.every((o) => ["draft", "rejected"].includes(o.status));
      return okStatuses && Object.keys(this.errors).length === 0;
    },
    // Există persoane neaprobate (pentru butonul „Aprobă tot")
    hasPending() {
      return this.occupants.some((o) => o.status !== "approved");
    },
    // Există o cerere trimisă de locatari (pentru „Respinge tot")
    hasSubmitted() {
      return this.occupants.some((o) => o.status === "submitted");
    },
  },
  async mounted() {
    await this.loadApartments();
  },
  methods: {
    emptyForm() {
      return {
        first_name: "",
        last_name: "",
        cnp: "",
        id_series: "",
        id_number: "",
        domicile_address: "",
        role_in_unit: "owner",
        other_role_text: "",
        move_in_date: "",
        move_out_date: "",
        is_minor: false,
        legal_guardian_name: "",
        phone: "",
        email: "",
        notes: "",
      };
    },
    mapRole(role) {
      return {
        owner: "Proprietar",
        tenant: "Chiriaș",
        family: "Membru familie",
        other: "Altul",
      }[role] || role;
    },
    mapStatus(status) {
      return {
        draft: "Draft",
        submitted: "Trimis",
        approved: "Aprobat",
        rejected: "Respins",
      }[status] || status;
    },
    statusBadge(status) {
      return {
        draft: "bg-gradient-secondary",
        submitted: "bg-gradient-info",
        approved: "bg-gradient-success",
        rejected: "bg-gradient-danger",
      }[status] || "bg-gradient-secondary";
    },
    canEdit(o) {
      // Managerii de tenant pot edita/șterge orice locatar, indiferent de status.
      if (this.isManager) return true;
      return ["draft", "rejected"].includes(o.status);
    },
    toggleDetails(id) {
      this.expanded[id] = !this.expanded[id];
    },
    ciDisplay(o) {
      const s = o.id_series || o.id_series_masked || "";
      const n = o.id_number || o.id_number_masked || "";
      return `${s} ${n}`.trim() || "—";
    },
    async loadApartments() {
      this.loading = true;
      try {
        // Manager de tenant (admin/cex): vede TOATE apartamentele tenantului.
        // Locatar: doar apartamentele proprii.
        this.isManager = hasAnyPermission(["configure apartments", "approve carte imobil"]);
        const res = this.isManager
          ? await carteImobilService.getApartmentsList()
          : await carteImobilService.myApartments();
        this.apartments = res?.data?.apartments || [];
        this.selectedApartmentId = this.apartments[0]?.id || "";
        if (this.selectedApartmentId) {
          await this.loadOccupants();
        }
      } catch (e) {
        showSwal.methods.showSwal({ type: "error", message: "Nu am putut încărca apartamentele." });
      } finally {
        this.loading = false;
      }
    },
    async loadOccupants() {
      if (!this.selectedApartmentId) return;
      this.loading = true;
      this.expanded = {};
      try {
        const res = await carteImobilService.getOccupants(this.selectedApartmentId);
        this.occupants = res?.data?.occupants || [];
      } catch (e) {
        showSwal.methods.showSwal({ type: "error", message: "Nu am putut încărca persoanele." });
      } finally {
        this.loading = false;
      }
    },
    openCreate() {
      this.editing = null;
      this.form = this.emptyForm();
      this.errors = {};
      this.showModal = true;
    },
    openEdit(o) {
      this.editing = o;
      this.form = {
        first_name: o.first_name,
        last_name: o.last_name,
        cnp: o.cnp || "",
        id_series: o.id_series || "",
        id_number: o.id_number || "",
        domicile_address: o.domicile_address || "",
        role_in_unit: o.role_in_unit,
        other_role_text: o.other_role_text || "",
        move_in_date: o.move_in_date || "",
        move_out_date: o.move_out_date || "",
        is_minor: !!o.is_minor,
        legal_guardian_name: o.legal_guardian_name || "",
        phone: o.phone || "",
        email: o.email || "",
        notes: o.notes || "",
      };
      this.errors = {};
      this.showModal = true;
    },
    closeModal() {
      this.showModal = false;
    },
    validateLocal() {
      const errs = {};
      if (!this.form.first_name?.trim()) errs.first_name = "Prenumele este obligatoriu.";
      if (!this.form.last_name?.trim()) errs.last_name = "Numele este obligatoriu.";
      if (!this.form.domicile_address?.trim()) errs.domicile_address = "Domiciliul este obligatoriu.";
      if (this.form.cnp && !/^\d{13}$/.test(this.form.cnp)) errs.cnp = "CNP trebuie să aibă exact 13 cifre.";
      if (!this.form.move_in_date) errs.move_in_date = "Data sosirii este obligatorie.";
      if (this.form.move_out_date && this.form.move_in_date && this.form.move_out_date < this.form.move_in_date) {
        errs.move_out_date = "Data plecării trebuie să fie după sau egală cu data sosirii.";
      }
      if (this.form.role_in_unit === "other" && !this.form.other_role_text?.trim()) {
        errs.other_role_text = "Te rog completează câmpul „Altă calitate”.";
      }
      if (this.form.is_minor && !this.form.legal_guardian_name?.trim()) {
        errs.legal_guardian_name = "Pentru minor este obligatoriu „Reprezentant legal”.";
      }
      this.errors = errs;
      return Object.keys(errs).length === 0;
    },
    async onSave() {
      if (!this.selectedApartmentId) return;
      if (!this.validateLocal()) return;

      try {
        const payload = { ...this.form };
        if (!payload.cnp) payload.cnp = null;
        if (!payload.id_series) payload.id_series = null;
        if (!payload.id_number) payload.id_number = null;
        if (!payload.move_out_date) payload.move_out_date = null;
        if (!payload.other_role_text) payload.other_role_text = null;
        if (!payload.phone) payload.phone = null;
        if (!payload.email) payload.email = null;
        if (!payload.notes) payload.notes = null;
        if (!payload.is_minor) payload.legal_guardian_name = null;

        if (this.editing) {
          const res = await carteImobilService.updateOccupant(this.editing.id, payload);
          if (res?.success) {
            showSwal.methods.showSwal({ type: "success", message: "Persoana a fost actualizată." });
            this.closeModal();
            await this.loadOccupants();
          } else {
            showSwal.methods.showSwal({ type: "error", message: "Nu am putut salva modificările." });
          }
        } else {
          const res = await carteImobilService.addOccupant(this.selectedApartmentId, payload);
          if (res?.success) {
            showSwal.methods.showSwal({ type: "success", message: "Persoana a fost adăugată." });
            this.closeModal();
            await this.loadOccupants();
          } else {
            showSwal.methods.showSwal({ type: "error", message: "Nu am putut adăuga persoana." });
          }
        }
      } catch (e) {
        const msg = e?.response?.data?.errors?.[0]?.message || "Eroare la salvare.";
        showSwal.methods.showSwal({ type: "error", message: msg });
      }
    },
    async onDelete(o) {
      const result = await showSwal.methods.showSwalConfirmationDelete();
      if (!result.isConfirmed) return;

      try {
        const res = await carteImobilService.deleteOccupant(o.id);
        if (res?.success) {
          showSwal.methods.showSwal({ type: "success", message: "Persoana a fost ștearsă." });
          await this.loadOccupants();
        } else {
          showSwal.methods.showSwal({ type: "error", message: "Nu am putut șterge persoana." });
        }
      } catch (e) {
        showSwal.methods.showSwal({ type: "error", message: "Nu am putut șterge persoana." });
      }
    },
    async onSubmit() {
      try {
        const res = await carteImobilService.submit(this.selectedApartmentId);
        if (res?.success) {
          showSwal.methods.showSwal({ type: "success", message: "Trimis spre validare." });
          await this.loadOccupants();
        } else {
          showSwal.methods.showSwal({ type: "error", message: "Nu am putut trimite spre validare." });
        }
      } catch (e) {
        const msg = e?.response?.data?.errors?.[0]?.message || "Nu am putut trimite spre validare.";
        showSwal.methods.showSwal({ type: "error", message: msg });
      }
    },
    async onApproveAll() {
      try {
        const res = await carteImobilService.approve(this.selectedApartmentId);
        if (res?.success) {
          showSwal.methods.showSwal({ type: "success", message: "Persoanele au fost aprobate." });
          await this.loadOccupants();
        } else {
          showSwal.methods.showSwal({ type: "error", message: "Nu am putut aproba." });
        }
      } catch (e) {
        const msg = e?.response?.data?.errors?.[0]?.message || "Nu am putut aproba.";
        showSwal.methods.showSwal({ type: "error", message: msg });
      }
    },
    async onRejectAll() {
      const reason = window.prompt("Motivul respingerii (min. 5 caractere):", "");
      if (reason === null) return;
      if (!reason || reason.trim().length < 5) {
        showSwal.methods.showSwal({ type: "error", message: "Motivul trebuie să aibă cel puțin 5 caractere." });
        return;
      }
      try {
        const res = await carteImobilService.reject(this.selectedApartmentId, reason.trim());
        if (res?.success) {
          showSwal.methods.showSwal({ type: "success", message: "Cererea a fost respinsă." });
          await this.loadOccupants();
        } else {
          showSwal.methods.showSwal({ type: "error", message: "Nu am putut respinge." });
        }
      } catch (e) {
        const msg = e?.response?.data?.errors?.[0]?.message || "Nu am putut respinge.";
        showSwal.methods.showSwal({ type: "error", message: msg });
      }
    },
    async onExport() {
      try {
        const res = await carteImobilService.exportPdf(this.selectedApartmentId);
        const blob = new Blob([res.data], { type: "application/pdf" });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement("a");
        a.href = url;
        a.download = "carte-imobil.pdf";
        document.body.appendChild(a);
        a.click();
        a.remove();
        window.URL.revokeObjectURL(url);
      } catch (e) {
        const msg = e?.response?.data?.errors?.[0]?.message || "Nu am putut genera PDF-ul.";
        showSwal.methods.showSwal({ type: "error", message: msg });
      }
    },
  },
};
</script>

