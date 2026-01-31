<template>
  <div class="py-4 container-fluid">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header pb-0 d-flex align-items-center justify-content-between">
            <div>
              <h5 class="mb-0">Administrare - Carte de imobil</h5>
              <p class="text-sm mb-0">Cererile trimise spre validare.</p>
            </div>
          </div>
          <div class="card-body">
            <div v-if="loading" class="text-sm">Se încarcă...</div>

            <div v-else>
              <div class="table-responsive">
                <table class="table table-flush">
                  <thead class="thead-light">
                    <tr>
                      <th>Apartament</th>
                      <th>Nr. persoane</th>
                      <th>Submitted</th>
                      <th>Acțiuni</th>
                    </tr>
                  </thead>
                  <tbody class="text-sm">
                    <tr v-if="submissions.length === 0">
                      <td colspan="4" class="text-center text-muted">Nu există cereri.</td>
                    </tr>
                    <tr v-for="s in submissions" :key="s.apartment_id">
                      <td>Ap. {{ s.apartment_number }}{{ s.staircase ? " | Scara " + s.staircase : "" }}</td>
                      <td>{{ s.nr_persoane }}</td>
                      <td>{{ s.submitted_at || "-" }}</td>
                      <td>
                        <button class="btn btn-outline-dark btn-sm mb-0 me-2" @click="openDetails(s.apartment_id)">
                          Vezi detalii
                        </button>
                        <button class="btn btn-outline-dark btn-sm mb-0" @click="onExport(s.apartment_id)">Export PDF</button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Detalii -->
    <div v-if="showDetails">
      <div class="modal-backdrop fade show" style="z-index: 1040"></div>
      <div class="modal fade show d-block" tabindex="-1" role="dialog" style="z-index: 1050">
        <div class="modal-dialog modal-xl" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h6 class="modal-title">Detaliu cerere (Ap. {{ detailsApartment?.number }})</h6>
              <button type="button" class="btn-close" @click="closeDetails"></button>
            </div>
            <div class="modal-body">
              <div v-if="detailsLoading" class="text-sm">Se încarcă...</div>
              <div v-else>
                <div class="table-responsive">
                  <table class="table table-flush">
                    <thead class="thead-light">
                      <tr>
                        <th>Nume</th>
                        <th>CNP</th>
                        <th>CI</th>
                        <th>Domiciliu</th>
                        <th>Calitate</th>
                        <th>Sosire</th>
                        <th>Plec.</th>
                        <th>Status</th>
                      </tr>
                    </thead>
                    <tbody class="text-sm">
                      <tr v-for="o in detailsOccupants" :key="o.id">
                        <td>{{ o.last_name }} {{ o.first_name }}</td>
                        <td>{{ o.cnp || o.cnp_masked || "-" }}</td>
                        <td>{{ (o.id_series || o.id_series_masked || "") + " " + (o.id_number || o.id_number_masked || "") }}</td>
                        <td>{{ o.domicile_address }}</td>
                        <td>{{ o.role_in_unit === "other" ? (o.other_role_text || "altul") : o.role_in_unit }}</td>
                        <td>{{ o.move_in_date }}</td>
                        <td>{{ o.move_out_date || "-" }}</td>
                        <td>{{ o.status }}</td>
                      </tr>
                    </tbody>
                  </table>
                </div>

                <div class="row g-3 mt-2">
                  <div class="col-12">
                    <label class="form-label">Motiv respingere (dacă e cazul)</label>
                    <textarea class="form-control" rows="2" v-model="rejectReason"></textarea>
                  </div>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button class="btn btn-outline-secondary" @click="closeDetails">Închide</button>
              <button class="btn bg-gradient-danger" :disabled="detailsLoading" @click="onReject">Respinge</button>
              <button class="btn bg-gradient-success" :disabled="detailsLoading" @click="onApprove">Aprobă</button>
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
  name: "CarteImobilAdmin",
  data() {
    return {
      loading: true,
      submissions: [],
      showDetails: false,
      detailsLoading: false,
      detailsApartmentId: null,
      detailsApartment: null,
      detailsOccupants: [],
      rejectReason: "",
    };
  },
  async mounted() {
    await this.loadSubmissions();
  },
  methods: {
    async loadSubmissions() {
      this.loading = true;
      try {
        const res = await carteImobilService.submissions();
        this.submissions = res?.data?.submissions || [];
      } catch (e) {
        showSwal.methods.showSwal({ type: "error", message: "Nu am putut încărca cererile." });
      } finally {
        this.loading = false;
      }
    },
    async openDetails(apartmentId) {
      this.showDetails = true;
      this.detailsLoading = true;
      this.detailsApartmentId = apartmentId;
      this.rejectReason = "";
      try {
        const res = await carteImobilService.getOccupants(apartmentId);
        this.detailsApartment = res?.data?.apartment || null;
        this.detailsOccupants = res?.data?.occupants || [];
      } catch (e) {
        showSwal.methods.showSwal({ type: "error", message: "Nu am putut încărca detaliile." });
      } finally {
        this.detailsLoading = false;
      }
    },
    closeDetails() {
      this.showDetails = false;
      this.detailsApartmentId = null;
      this.detailsApartment = null;
      this.detailsOccupants = [];
      this.rejectReason = "";
    },
    async onApprove() {
      if (!this.detailsApartmentId) return;
      try {
        const res = await carteImobilService.approve(this.detailsApartmentId);
        if (res?.success) {
          showSwal.methods.showSwal({ type: "success", message: "Cererea a fost aprobată." });
          await this.openDetails(this.detailsApartmentId);
          await this.loadSubmissions();
        } else {
          showSwal.methods.showSwal({ type: "error", message: "Nu am putut aproba cererea." });
        }
      } catch (e) {
        const msg = e?.response?.data?.errors?.[0]?.message || "Nu am putut aproba cererea.";
        showSwal.methods.showSwal({ type: "error", message: msg });
      }
    },
    async onReject() {
      if (!this.detailsApartmentId) return;
      if (!this.rejectReason || this.rejectReason.trim().length < 5) {
        showSwal.methods.showSwal({ type: "error", message: "Motivul respingerii trebuie să aibă minim 5 caractere." });
        return;
      }
      try {
        const res = await carteImobilService.reject(this.detailsApartmentId, this.rejectReason.trim());
        if (res?.success) {
          showSwal.methods.showSwal({ type: "success", message: "Cererea a fost respinsă." });
          await this.openDetails(this.detailsApartmentId);
          await this.loadSubmissions();
        } else {
          showSwal.methods.showSwal({ type: "error", message: "Nu am putut respinge cererea." });
        }
      } catch (e) {
        const msg = e?.response?.data?.errors?.[0]?.message || "Nu am putut respinge cererea.";
        showSwal.methods.showSwal({ type: "error", message: msg });
      }
    },
    async onExport(apartmentId) {
      try {
        const res = await carteImobilService.exportPdf(apartmentId);
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

