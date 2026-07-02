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
      <div class="page-header min-vh-100">
        <div class="container">
          <div class="row">
            <div class="mx-auto col-xl-5 col-lg-6 col-md-8 d-flex flex-column">
              <div class="mt-6 card card-plain">
                <div class="pb-0 card-header text-start">
                  <h3 class="font-weight-bolder text-primary text-gradient text-center">
                    Solicită cont
                  </h3>
                  <p class="text-sm text-center mb-0">
                    Completează formularul pentru a solicita acces la platforma asociației tale.
                  </p>
                </div>

                <!-- Success state -->
                <div v-if="submitted" class="card-body text-center py-5">
                  <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                  <h4 class="text-success">Cererea a fost trimisă!</h4>
                  <p class="text-muted">
                    Vei primi un email când cererea ta va fi procesată de administrator.
                  </p>
                  <router-link
                    :to="{ name: 'Login' }"
                    class="btn bg-gradient-primary mt-3"
                  >
                    Înapoi la autentificare
                  </router-link>
                </div>

                <!-- Form -->
                <div v-else class="card-body">
                  <form @submit.prevent="handleSubmit">
                    <!-- Tenant selection -->
                    <div class="mb-3">
                      <label class="form-label">Asociația *</label>
                      <select
                        class="form-select"
                        v-model="form.tenant_id"
                        @change="onTenantChange"
                        required
                      >
                        <option value="">-- Selectează asociația --</option>
                        <option v-for="t in tenants" :key="t.id" :value="t.id">
                          {{ t.name }}
                        </option>
                      </select>
                    </div>

                    <!-- Tenant info -->
                    <div v-if="selectedTenant" class="alert alert-light mb-3">
                      <small>
                        <strong>Adresă:</strong> {{ selectedTenant.address || 'N/A' }}
                      </small>
                      <div v-if="selectedTenant.registration_instructions" class="mt-2">
                        <strong>Instrucțiuni înregistrare:</strong>
                        <p class="mb-0 text-sm">{{ selectedTenant.registration_instructions }}</p>
                      </div>
                    </div>

                    <!-- Apartment selection -->
                    <div class="mb-3">
                      <label class="form-label">Apartament *</label>
                      <select
                        class="form-select"
                        v-model="form.apartment_id"
                        :disabled="!form.tenant_id || loadingApartments"
                        required
                      >
                        <option value="">-- Selectează apartamentul --</option>
                        <option v-for="a in apartments" :key="a.id" :value="a.id">
                          {{ a.label }}
                        </option>
                      </select>
                      <small v-if="loadingApartments" class="text-muted">
                        Se încarcă apartamentele...
                      </small>
                    </div>

                    <!-- First Name -->
                    <div class="mb-3">
                      <label class="form-label">Prenume *</label>
                      <input
                        type="text"
                        class="form-control"
                        v-model="form.first_name"
                        placeholder="ex: Ion"
                        required
                      />
                    </div>

                    <!-- Last Name -->
                    <div class="mb-3">
                      <label class="form-label">Nume *</label>
                      <input
                        type="text"
                        class="form-control"
                        v-model="form.last_name"
                        placeholder="ex: Popescu"
                        required
                      />
                    </div>

                    <!-- Email -->
                    <div class="mb-3">
                      <label class="form-label">Email *</label>
                      <input
                        type="email"
                        class="form-control"
                        v-model="form.email"
                        placeholder="email@exemplu.com"
                        required
                      />
                    </div>

                    <!-- Phone -->
                    <div class="mb-3">
                      <label class="form-label">Telefon</label>
                      <input
                        type="tel"
                        class="form-control"
                        v-model="form.phone"
                        placeholder="+40 xxx xxx xxx"
                      />
                    </div>

                    <!-- Document upload (dovadă proprietar) -->
                    <div class="mb-3">
                      <label class="form-label">Poza contractului de vânzare-cumpărare *</label>
                      <input
                        type="file"
                        class="form-control"
                        @change="onFileChange"
                        accept=".pdf,.jpg,.jpeg,.png"
                        required
                      />
                      <small class="text-muted">
                        Dovadă că ești proprietar — poza paginii din contract (PDF sau imagine, max 5MB).
                      </small>
                    </div>

                    <!-- Notes -->
                    <div class="mb-3">
                      <label class="form-label">Mesaj (opțional)</label>
                      <textarea
                        class="form-control"
                        v-model="form.notes"
                        rows="2"
                        placeholder="Informații suplimentare pentru administrator..."
                      ></textarea>
                    </div>

                    <!-- Error message -->
                    <div v-if="error" class="alert alert-danger text-sm">
                      {{ error }}
                    </div>

                    <!-- Submit button -->
                    <div class="text-center">
                      <button
                        type="submit"
                        class="btn bg-gradient-primary w-100 mb-3"
                        :disabled="loading"
                      >
                        <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                        {{ loading ? 'Se trimite...' : 'Trimite cererea' }}
                      </button>
                    </div>
                  </form>
                </div>

                <div class="px-1 pt-0 pb-3 text-center card-footer">
                  <p class="mx-auto mb-0 text-sm">
                    Ai deja cont?
                    <router-link
                      :to="{ name: 'Login' }"
                      class="text-primary text-gradient font-weight-bold"
                    >
                      Autentifică-te
                    </router-link>
                  </p>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div
                class="top-0 oblique position-absolute h-100 d-md-block d-none me-n8"
              >
                <div
                  class="bg-cover oblique-image position-absolute fixed-top ms-auto h-100 z-index-0 ms-n6"
                  :style="{ backgroundImage: 'url(' + curvedImg + ')' }"
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
import registrationRequestService from "@/services/registration-request.service.js";
// Vite nu suportă require() în browser — import static (ca în Login.vue)
import curvedImg from "@/assets/img/curved-images/curved6.jpg";

export default {
  name: "RegistrationRequest",
  components: {
    Navbar,
    AppFooter,
  },
  data() {
    return {
      curvedImg, // expunem imaginea în template
      tenants: [],
      apartments: [],
      loadingApartments: false,
      form: {
        tenant_id: "",
        apartment_id: "",
        first_name: "",
        last_name: "",
        email: "",
        phone: "",
        notes: "",
      },
      document: null,
      loading: false,
      error: null,
      submitted: false,
    };
  },
  computed: {
    selectedTenant() {
      if (!this.form.tenant_id) return null;
      return this.tenants.find((t) => t.id === parseInt(this.form.tenant_id));
    },
  },
  async mounted() {
    this.$store.state.hideConfigButton = true;
    this.$store.state.showNavbar = false;
    this.$store.state.showSidenav = false;
    this.$store.state.showFooter = false;
    document.body.classList.remove("bg-gray-100");

    await this.loadTenants();
  },
  beforeUnmount() {
    this.$store.state.hideConfigButton = false;
    this.$store.state.showNavbar = true;
    this.$store.state.showSidenav = true;
    this.$store.state.showFooter = true;
    document.body.classList.add("bg-gray-100");
  },
  methods: {
    async loadTenants() {
      try {
        const res = await registrationRequestService.getPublicTenants();
        this.tenants = res?.data?.tenants || [];
      } catch (e) {
        console.error("Failed to load tenants:", e);
        this.error = "Nu am putut încărca lista de asociații.";
      }
    },
    async onTenantChange() {
      this.form.apartment_id = "";
      this.apartments = [];

      if (!this.form.tenant_id) return;

      this.loadingApartments = true;
      try {
        const res = await registrationRequestService.getPublicApartments(this.form.tenant_id);
        this.apartments = res?.data?.apartments || [];
      } catch (e) {
        console.error("Failed to load apartments:", e);
      } finally {
        this.loadingApartments = false;
      }
    },
    onFileChange(event) {
      const file = event.target.files[0];
      if (file) {
        // Validate file size (5MB)
        if (file.size > 5 * 1024 * 1024) {
          this.error = "Documentul nu poate depăși 5MB.";
          event.target.value = "";
          return;
        }
        this.document = file;
        this.error = null;
      }
    },
    async handleSubmit() {
      this.error = null;

      if (!this.document) {
        this.error = "Te rog atașează poza contractului de vânzare-cumpărare.";
        return;
      }

      this.loading = true;

      try {
        const formData = new FormData();
        formData.append("tenant_id", this.form.tenant_id);
        formData.append("apartment_id", this.form.apartment_id);
        formData.append("first_name", this.form.first_name);
        formData.append("last_name", this.form.last_name);
        formData.append("email", this.form.email);
        if (this.form.phone) formData.append("phone", this.form.phone);
        if (this.form.notes) formData.append("notes", this.form.notes);
        if (this.document) formData.append("document", this.document);

        await registrationRequestService.submitRequest(formData);
        this.submitted = true;
      } catch (e) {
        console.error("Submit error:", e);
        const errors = e?.response?.data?.errors;
        if (errors && Array.isArray(errors) && errors.length > 0) {
          this.error = errors.map((err) => err.message || err).join(", ");
        } else if (e?.response?.data?.message) {
          this.error = e.response.data.message;
        } else {
          this.error = "A apărut o eroare. Te rog încearcă din nou.";
        }
      } finally {
        this.loading = false;
      }
    },
  },
};
</script>
