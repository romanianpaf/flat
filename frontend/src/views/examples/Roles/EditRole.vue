<template>
  <div class="py-4 container-fluid">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="pb-0 card-header">
            <div class="d-lg-flex">
              <div>
                <h5 class="mb-0">Editează rol</h5>
              </div>
              <div class="my-auto mt-4 ms-auto mt-lg-0">
                <div class="my-auto ms-auto">
                  <router-link to="/examples/roles/list" class="mb-0 btn bg-gradient-success btn-sm"
                    >&nbsp; Înapoi la listă</router-link
                  >
                </div>
              </div>
            </div>
          </div>

          <div class="card-body">
            <div class="row mt-4">
              <soft-model-input
                id="name"
                v-model="role.name"
                type="text"
                placeholder="Nume Rol"
              />
            </div>
            <validation-error :errors="apiValidationErrors.name" />

            <div class="row mt-4">
              <label class="form-label">Beneficiar</label>
              <select 
                v-model="role.tenant_id" 
                class="form-control"
                disabled
              >
                <option :value="null">Global (toate beneficiarele)</option>
                <option 
                  v-for="tenant in tenantsList" 
                  :key="tenant.id" 
                  :value="tenant.id"
                >
                  {{ tenant.name }}
                </option>
              </select>
              <small class="text-muted mt-2">
                <i class="fas fa-lock"></i> 
                Beneficiarul nu poate fi modificat după creare.
              </small>
            </div>
            <validation-error :errors="apiValidationErrors.tenant_id" />

            <soft-button
              color="dark"
              variant="gradient"
              class="float-end mt-4 mb-0"
              size="sm"
              :is-disabled="loading ? true : false"
              @click="editRole"
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
  name: "EditRole",

  components: {
    SoftModelInput,
    SoftButton,
    ValidationError,
  },
  mixins: [formMixin],
  data() {
    return {
      loading: false,
      role: {},
    };
  },
  computed: {
    tenantsList() {
      return this.$store.getters["tenants/tenants"]?.data || [];
    },
  },
  async created() {
    // Încarcă lista de beneficiari
    await this.$store.dispatch("tenants/getTenants", {
      page: {
        number: 1,
        size: 1000,
      },
    });
    
    // Încarcă rolul cu relația tenant
    this.role = await this.$store.dispatch("roles/getRole", {
      id: this.$route.params.id,
      params: {
        include: "tenant",
      },
    });
  },

  methods: {
    async editRole() {
      this.resetApiValidation();
      this.loading = true;
      try {
        await this.$store.dispatch("roles/editRole", this.role);
        showSwal.methods.showSwal({
          type: "success",
          message: "Rol actualizat cu succes!",
        });
        this.$router.push("/examples/roles/list");
      } catch (error) {
        if (error.response.data.errors) this.setApiValidation(error.response.data.errors);
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
