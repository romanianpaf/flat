<template>
  <div class="py-4 container-fluid">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="pb-0 card-header">
            <div class="d-lg-flex">
              <div>
                <h5 class="mb-0">Beneficiar Nou</h5>
              </div>
              <div class="my-auto mt-4 ms-auto mt-lg-0">
                <div class="my-auto ms-auto">
                  <router-link to="/tenants/list" class="mb-0 btn bg-gradient-primary btn-sm"
                    >&nbsp; Înapoi la listă</router-link
                  >
                </div>
              </div>
            </div>
          </div>

          <div class="card-body">
            <div class="row">
              <div class="col-md-6">
                <label class="form-label mt-2">Nume *</label>
                <soft-model-input
                  id="name"
                  v-model="tenant.name"
                  type="text"
                  placeholder="Numele rezidentului"
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
                  placeholder="Descrierea rezidentului"
                  rows="3"
                />
                <validation-error :errors="apiValidationErrors.description" />
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
              :is-disabled="loading ? true : false"
              @click="addTenant"
              ><span v-if="loading" class="spinner-border spinner-border-sm"></span>
              <span v-else>Salvează</span></soft-button
            >
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
import showSwal from "/src/mixins/showSwal.js";
import formMixin from "/src/mixins/form-mixin.js";
import phoneFormatter from "/src/mixins/phoneFormatter.js";
import ValidationError from "@/components/ValidationError.vue";

export default {
  name: "NewTenant",

  components: {
    SoftModelInput,
    SoftModelTextarea,
    SoftButton,
    ValidationError,
  },
  mixins: [formMixin, phoneFormatter],
  data() {
    return {
      loading: false,
      tenant: {
        name: "",
        address: "",
        fiscal_code: "",
        description: "",
        contact_data: {
          person: "",
          email: "",
          phone: "",
        },
      },
    };
  },

  methods: {
    async addTenant() {
      this.resetApiValidation();
      this.loading = true;
      try {
        await this.$store.dispatch("tenants/addTenant", this.tenant);
        showSwal.methods.showSwal({
          type: "success",
          message: "Beneficiar adăugat cu succes!",
        });
        this.$router.push("/tenants/list");
      } catch (error) {
        if (error.response?.data?.errors) this.setApiValidation(error.response.data.errors);
        else
          showSwal.methods.showSwal({
            type: "error",
            message: "Oops, ceva nu a mers bine!",
            width: 350,
          });
        this.loading = false;
      }
    },
  },
};
</script>

