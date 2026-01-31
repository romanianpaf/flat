<template>
  <div class="py-4 container-fluid">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header pb-0 d-flex align-items-center justify-content-between">
            <div>
              <h5 class="mb-0">Cereri de înregistrare</h5>
              <p class="text-sm mb-0">Gestionează cererile de cont noi din asociația ta.</p>
            </div>
            <div class="d-flex gap-2">
              <select class="form-select form-select-sm" v-model="statusFilter" @change="loadRequests" style="width: auto">
                <option value="">Toate</option>
                <option value="pending">În așteptare</option>
                <option value="approved">Aprobate</option>
                <option value="rejected">Respinse</option>
              </select>
            </div>
          </div>
          <div class="card-body">
            <div v-if="loading" class="text-center py-4">
              <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Se încarcă...</span>
              </div>
            </div>

            <div v-else-if="requests.length === 0" class="text-center py-4 text-muted">
              Nu există cereri de înregistrare.
            </div>

            <div v-else class="table-responsive">
              <table class="table table-hover align-middle">
                <thead>
                  <tr>
                    <th>Nume</th>
                    <th>Email</th>
                    <th>Asociație</th>
                    <th>Apartament</th>
                    <th>Data cererii</th>
                    <th>Status</th>
                    <th>Acțiuni</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="req in requests" :key="req.id">
                    <td>
                      <strong>{{ req.full_name || (req.first_name + ' ' + req.last_name) }}</strong>
                      <div v-if="req.phone" class="text-xs text-muted">{{ req.phone }}</div>
                    </td>
                    <td>{{ req.email }}</td>
                    <td>{{ req.tenant?.name || '-' }}</td>
                    <td>
                      <span v-if="req.apartment">
                        {{ req.apartment.number }}
                        <span v-if="req.apartment.staircase" class="text-muted">
                          (Sc. {{ req.apartment.staircase }})
                        </span>
                      </span>
                      <span v-else class="text-muted">-</span>
                    </td>
                    <td>{{ formatDate(req.created_at) }}</td>
                    <td>
                      <span class="badge" :class="statusBadgeClass(req.status)">
                        {{ statusLabel(req.status) }}
                      </span>
                    </td>
                    <td>
                      <button 
                        class="btn btn-outline-dark btn-sm me-1" 
                        @click="openDetails(req)"
                        title="Detalii"
                      >
                        <i class="fas fa-eye"></i>
                      </button>
                      <button 
                        v-if="req.status === 'pending'"
                        class="btn btn-outline-success btn-sm me-1" 
                        @click="approveRequest(req)"
                        title="Aprobă"
                      >
                        <i class="fas fa-check"></i>
                      </button>
                      <button 
                        v-if="req.status === 'pending'"
                        class="btn btn-outline-danger btn-sm" 
                        @click="openRejectModal(req)"
                        title="Respinge"
                      >
                        <i class="fas fa-times"></i>
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Pagination -->
            <div v-if="pagination.last_page > 1" class="d-flex justify-content-center mt-4">
              <nav>
                <ul class="pagination pagination-sm mb-0">
                  <li class="page-item" :class="{ disabled: pagination.current_page === 1 }">
                    <a class="page-link" href="#" @click.prevent="goToPage(pagination.current_page - 1)">
                      &laquo;
                    </a>
                  </li>
                  <li 
                    v-for="page in pagination.last_page" 
                    :key="page"
                    class="page-item" 
                    :class="{ active: page === pagination.current_page }"
                  >
                    <a class="page-link" href="#" @click.prevent="goToPage(page)">{{ page }}</a>
                  </li>
                  <li class="page-item" :class="{ disabled: pagination.current_page === pagination.last_page }">
                    <a class="page-link" href="#" @click.prevent="goToPage(pagination.current_page + 1)">
                      &raquo;
                    </a>
                  </li>
                </ul>
              </nav>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Details Modal -->
    <div v-if="showDetailsModal">
      <div class="modal-backdrop fade show" style="z-index: 1040"></div>
      <div class="modal fade show d-block" tabindex="-1" style="z-index: 1050">
        <div class="modal-dialog modal-lg">
          <div class="modal-content">
            <div class="modal-header">
              <h6 class="modal-title">Detalii cerere - {{ selectedRequest?.full_name || (selectedRequest?.first_name + ' ' + selectedRequest?.last_name) }}</h6>
              <button type="button" class="btn-close" @click="closeDetailsModal"></button>
            </div>
            <div class="modal-body" v-if="selectedRequest">
              <div class="row">
                <div class="col-md-6">
                  <p><strong>Prenume:</strong> {{ selectedRequest.first_name }}</p>
                  <p><strong>Nume:</strong> {{ selectedRequest.last_name }}</p>
                  <p><strong>Email:</strong> {{ selectedRequest.email }}</p>
                  <p><strong>Telefon:</strong> {{ selectedRequest.phone || '-' }}</p>
                </div>
                <div class="col-md-6">
                  <p><strong>Asociație:</strong> {{ selectedRequest.tenant?.name || '-' }}</p>
                  <p>
                    <strong>Apartament:</strong> 
                    <span v-if="selectedRequest.apartment">
                      {{ selectedRequest.apartment.number }}
                      <span v-if="selectedRequest.apartment.staircase">(Sc. {{ selectedRequest.apartment.staircase }})</span>
                      <span v-if="selectedRequest.apartment.floor">, Et. {{ selectedRequest.apartment.floor }}</span>
                    </span>
                    <span v-else>-</span>
                  </p>
                  <p><strong>Data cererii:</strong> {{ formatDateTime(selectedRequest.created_at) }}</p>
                </div>
              </div>

              <div v-if="selectedRequest.notes" class="mt-3">
                <strong>Mesaj:</strong>
                <p class="text-sm bg-light p-2 rounded">{{ selectedRequest.notes }}</p>
              </div>

              <div v-if="selectedRequest.document_original_name" class="mt-3">
                <strong>Document:</strong>
                <button class="btn btn-outline-primary btn-sm ms-2" @click="downloadDocument">
                  <i class="fas fa-download me-1"></i>
                  {{ selectedRequest.document_original_name }}
                </button>
              </div>

              <div v-if="selectedRequest.status === 'rejected' && selectedRequest.rejection_reason" class="mt-3">
                <strong>Motiv respingere:</strong>
                <p class="text-sm text-danger bg-light p-2 rounded">{{ selectedRequest.rejection_reason }}</p>
              </div>

              <div v-if="selectedRequest.reviewer" class="mt-3">
                <strong>Procesat de:</strong> {{ [selectedRequest.reviewer.first_name, selectedRequest.reviewer.last_name].filter(Boolean).join(' ') }}
                <span class="text-muted">la {{ formatDateTime(selectedRequest.reviewed_at) }}</span>
              </div>
            </div>
            <div class="modal-footer">
              <button class="btn btn-outline-secondary" @click="closeDetailsModal">Închide</button>
              <button 
                v-if="selectedRequest?.status === 'pending'"
                class="btn btn-success" 
                @click="approveRequest(selectedRequest)"
              >
                Aprobă
              </button>
              <button 
                v-if="selectedRequest?.status === 'pending'"
                class="btn btn-danger" 
                @click="openRejectModal(selectedRequest)"
              >
                Respinge
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Reject Modal -->
    <div v-if="showRejectModal">
      <div class="modal-backdrop fade show" style="z-index: 1060"></div>
      <div class="modal fade show d-block" tabindex="-1" style="z-index: 1070">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h6 class="modal-title">Respinge cererea</h6>
              <button type="button" class="btn-close" @click="closeRejectModal"></button>
            </div>
            <div class="modal-body">
              <p>Ești sigur că vrei să respingi cererea lui <strong>{{ requestToReject?.full_name || (requestToReject?.first_name + ' ' + requestToReject?.last_name) }}</strong>?</p>
              <div class="mb-3">
                <label class="form-label">Motiv respingere (opțional)</label>
                <textarea 
                  class="form-control" 
                  v-model="rejectReason" 
                  rows="3"
                  placeholder="Ex: Document invalid, apartament incorect, etc."
                ></textarea>
              </div>
            </div>
            <div class="modal-footer">
              <button class="btn btn-outline-secondary" @click="closeRejectModal">Anulează</button>
              <button class="btn btn-danger" @click="confirmReject" :disabled="rejecting">
                <span v-if="rejecting" class="spinner-border spinner-border-sm me-1"></span>
                Respinge
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Approve Success Modal -->
    <div v-if="showApproveSuccess">
      <div class="modal-backdrop fade show" style="z-index: 1080"></div>
      <div class="modal fade show d-block" tabindex="-1" style="z-index: 1090">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header bg-success text-white">
              <h6 class="modal-title">Cerere aprobată</h6>
              <button type="button" class="btn-close btn-close-white" @click="closeApproveSuccess"></button>
            </div>
            <div class="modal-body text-center">
              <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
              <p>Utilizatorul a fost creat cu succes.</p>
              <div v-if="temporaryPassword" class="alert alert-warning">
                <strong>Parolă temporară:</strong>
                <code class="ms-2">{{ temporaryPassword }}</code>
                <button class="btn btn-sm btn-link" @click="copyPassword" title="Copiază">
                  <i class="fas fa-copy"></i>
                </button>
                <p class="text-xs mb-0 mt-2">Transmite această parolă utilizatorului pentru prima autentificare.</p>
              </div>
            </div>
            <div class="modal-footer">
              <button class="btn btn-success" @click="closeApproveSuccess">OK</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import showSwal from "/src/mixins/showSwal.js";
import registrationRequestService from "@/services/registration-request.service.js";

export default {
  name: "RegistrationRequests",
  data() {
    return {
      loading: false,
      requests: [],
      pagination: {
        total: 0,
        per_page: 20,
        current_page: 1,
        last_page: 1,
      },
      statusFilter: "pending",
      showDetailsModal: false,
      selectedRequest: null,
      showRejectModal: false,
      requestToReject: null,
      rejectReason: "",
      rejecting: false,
      showApproveSuccess: false,
      temporaryPassword: null,
    };
  },
  async mounted() {
    await this.loadRequests();
  },
  methods: {
    async loadRequests(page = 1) {
      this.loading = true;
      try {
        const params = { page };
        if (this.statusFilter) params.status = this.statusFilter;

        const res = await registrationRequestService.getRequests(params);
        this.requests = res?.data?.requests || [];
        this.pagination = res?.data?.pagination || this.pagination;
      } catch (e) {
        console.error("Failed to load requests:", e);
        showSwal.methods.showSwal({ type: "error", message: "Nu am putut încărca cererile." });
      } finally {
        this.loading = false;
      }
    },
    goToPage(page) {
      if (page < 1 || page > this.pagination.last_page) return;
      this.loadRequests(page);
    },
    formatDate(dateStr) {
      if (!dateStr) return "-";
      return new Date(dateStr).toLocaleDateString("ro-RO");
    },
    formatDateTime(dateStr) {
      if (!dateStr) return "-";
      return new Date(dateStr).toLocaleString("ro-RO");
    },
    statusBadgeClass(status) {
      return {
        pending: "bg-gradient-warning",
        approved: "bg-gradient-success",
        rejected: "bg-gradient-danger",
      }[status] || "bg-gradient-secondary";
    },
    statusLabel(status) {
      return {
        pending: "În așteptare",
        approved: "Aprobat",
        rejected: "Respins",
      }[status] || status;
    },
    openDetails(req) {
      this.selectedRequest = req;
      this.showDetailsModal = true;
    },
    closeDetailsModal() {
      this.showDetailsModal = false;
      this.selectedRequest = null;
    },
    async downloadDocument() {
      if (!this.selectedRequest) return;
      try {
        const res = await registrationRequestService.downloadDocument(this.selectedRequest.id);
        const blob = new Blob([res.data]);
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement("a");
        a.href = url;
        a.download = this.selectedRequest.document_original_name || "document";
        document.body.appendChild(a);
        a.click();
        a.remove();
        window.URL.revokeObjectURL(url);
      } catch (e) {
        console.error("Download error:", e);
        showSwal.methods.showSwal({ type: "error", message: "Nu am putut descărca documentul." });
      }
    },
    async approveRequest(req) {
      const fullName = req.full_name || [req.first_name, req.last_name].filter(Boolean).join(' ') || 'utilizator';
      const confirm = await showSwal.methods.showSwal({
        type: "warning",
        message: `Ești sigur că vrei să aprobi cererea lui ${fullName}?`,
        showCancelButton: true,
        confirmButtonText: "Da, aprobă",
        cancelButtonText: "Anulează",
      });

      if (!confirm || !confirm.isConfirmed) return;

      try {
        const res = await registrationRequestService.approveRequest(req.id);
        this.temporaryPassword = res?.data?.temporary_password;
        this.showApproveSuccess = true;
        this.closeDetailsModal();
        await this.loadRequests(this.pagination.current_page);
      } catch (e) {
        console.error("Approve error:", e);
        const msg = e?.response?.data?.errors?.[0]?.message || "Nu am putut aproba cererea.";
        showSwal.methods.showSwal({ type: "error", message: msg });
      }
    },
    openRejectModal(req) {
      this.requestToReject = req;
      this.rejectReason = "";
      this.showRejectModal = true;
    },
    closeRejectModal() {
      this.showRejectModal = false;
      this.requestToReject = null;
      this.rejectReason = "";
    },
    async confirmReject() {
      if (!this.requestToReject) return;

      this.rejecting = true;
      try {
        await registrationRequestService.rejectRequest(this.requestToReject.id, this.rejectReason);
        showSwal.methods.showSwal({ type: "success", message: "Cererea a fost respinsă." });
        this.closeRejectModal();
        this.closeDetailsModal();
        await this.loadRequests(this.pagination.current_page);
      } catch (e) {
        console.error("Reject error:", e);
        const msg = e?.response?.data?.errors?.[0]?.message || "Nu am putut respinge cererea.";
        showSwal.methods.showSwal({ type: "error", message: msg });
      } finally {
        this.rejecting = false;
      }
    },
    closeApproveSuccess() {
      this.showApproveSuccess = false;
      this.temporaryPassword = null;
    },
    copyPassword() {
      if (this.temporaryPassword) {
        navigator.clipboard.writeText(this.temporaryPassword);
        showSwal.methods.showSwal({ type: "success", message: "Parola a fost copiată." });
      }
    },
  },
};
</script>
