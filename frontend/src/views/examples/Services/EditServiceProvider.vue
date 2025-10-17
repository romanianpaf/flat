<template>
  <div class="py-4 container-fluid">
    <div class="row">
      <div class="col-12">
        <div v-if="form" class="card">
          <div class="pb-0 card-header">
            <div class="d-lg-flex align-items-center">
              <div>
                <h5 class="mb-0">Editează furnizor de servicii</h5>
              </div>
              <div class="ms-lg-auto mt-3 mt-lg-0">
                <router-link
                  class="mb-0 btn bg-gradient-primary btn-sm"
                  to="/service-providers"
                >
                  &nbsp; Înapoi la listă
                </router-link>
              </div>
            </div>
          </div>

          <div class="card-body">
            <div class="row">
              <div class="col-md-6">
                <label class="form-label mt-2">Categorie</label>
                <select
                  v-model="form.service_category_id"
                  class="form-control"
                  @change="loadSubcategories"
                >
                  <option disabled value="">Selectează o categorie</option>
                  <option
                    v-for="option in categoryOptions"
                    :key="option.id"
                    :value="option.id"
                  >
                    {{ option.name }}
                  </option>
                </select>
                <validation-error :errors="apiValidationErrors.service_category_id" />
              </div>
              <div class="col-md-6">
                <label class="form-label mt-2">Subcategorie</label>
                <select
                  v-model="form.service_subcategory_id"
                  class="form-control"
                  :disabled="!subcategoryOptions.length"
                >
                  <option disabled value="">Selectează o subcategorie</option>
                  <option
                    v-for="option in subcategoryOptions"
                    :key="option.id"
                    :value="option.id"
                  >
                    {{ option.name }}
                  </option>
                </select>
                <validation-error :errors="apiValidationErrors.service_subcategory_id" />
              </div>
            </div>

            <div class="row mt-3">
              <div class="col-md-4">
                <label class="form-label">Tip furnizor</label>
                <select v-model="form.provider_type" class="form-control" disabled>
                  <option value="private">Persoană fizică</option>
                  <option value="company">Companie</option>
                </select>
              </div>
              <div v-if="form.provider_type === 'private'" class="col-md-4">
                <label class="form-label">Prenume</label>
                <soft-model-input
                  id="first_name"
                  v-model="form.first_name"
                  type="text"
                  placeholder="Prenume"
                />
                <validation-error :errors="apiValidationErrors.first_name" />
              </div>
              <div v-if="form.provider_type === 'private'" class="col-md-4">
                <label class="form-label">Nume</label>
                <soft-model-input
                  id="last_name"
                  v-model="form.last_name"
                  type="text"
                  placeholder="Nume"
                />
                <validation-error :errors="apiValidationErrors.last_name" />
              </div>
              <div v-if="form.provider_type === 'company'" class="col-md-8">
                <label class="form-label">Nume companie</label>
                <soft-model-input
                  id="company_name"
                  v-model="form.company_name"
                  type="text"
                  placeholder="Nume companie"
                />
                <validation-error :errors="apiValidationErrors.company_name" />
              </div>
            </div>

            <div class="row mt-3">
              <div class="col-md-4">
                <label class="form-label">Telefon</label>
                <soft-model-input
                  id="phone"
                  v-model="form.phone"
                  type="text"
                  placeholder="+40 XXX XXX XXX"
                  @input="form.phone = formatPhone($event)"
                />
                <validation-error :errors="apiValidationErrors.phone" />
              </div>
              <div class="col-md-4">
                <label class="form-label">Email</label>
                <soft-model-input
                  id="email"
                  v-model="form.email"
                  type="email"
                  placeholder="Adresă de email"
                />
                <validation-error :errors="apiValidationErrors.email" />
              </div>
              <div class="col-md-4 d-flex align-items-end">
                <div class="form-check form-switch">
                  <input
                    id="is_published"
                    v-model="form.is_published"
                    class="form-check-input"
                    type="checkbox"
                  />
                  <label class="form-check-label" for="is_published">
                    Publică furnizorul
                  </label>
                </div>
              </div>
            </div>

            <label class="form-label mt-4">Descriere servicii</label>
            <soft-model-textarea
              id="service_description"
              v-model="form.service_description"
              placeholder="Descrie serviciile furnizorului..."
              rows="6"
            />
            <validation-error :errors="apiValidationErrors.service_description" />

            <soft-button
              color="dark"
              variant="gradient"
              class="float-end mt-4 mb-0"
              size="sm"
              :is-disabled="loading"
              @click="save"
            >
              <span v-if="loading" class="spinner-border spinner-border-sm"></span>
              <span v-else>Salvează</span>
            </soft-button>
          </div>
        </div>
        <div v-else class="text-center py-5">
          <span class="spinner-border" role="status"></span>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import SoftModelInput from "/src/components/SoftModelInput.vue";
import SoftModelTextarea from "/src/components/SoftModelTextarea.vue";
import SoftButton from "/src/components/SoftButton.vue";
import ValidationError from "/src/components/ValidationError.vue";
import showSwal from "/src/mixins/showSwal.js";
import formMixin from "/src/mixins/form-mixin.js";
import phoneFormatter from "/src/mixins/phoneFormatter.js";

export default {
  name: "EditServiceProvider",
  components: {
    SoftModelInput,
    SoftModelTextarea,
    SoftButton,
    ValidationError,
  },
  mixins: [formMixin, phoneFormatter],
  data() {
    return {
      form: null,
      subcategoryOptions: [],
      loading: false,
    };
  },
  computed: {
    categoryOptions() {
      return this.$store.getters["serviceCategories/categories"]?.data ?? [];
    },
  },
  async created() {
    await this.ensureCategoriesLoaded();
    await this.loadProvider();
  },
  methods: {
    async ensureCategoriesLoaded() {
      if (!this.categoryOptions.length) {
        await this.$store.dispatch("serviceCategories/getCategories", {
          page: { number: 1, size: 100 },
          sort: "name",
        });
      }
    },
    async loadProvider() {
      try {
        const response = await this.$store.dispatch("serviceProviders/getProvider", {
          id: this.$route.params.id,
          params: { include: "category,subcategory" },
        });
        this.form = {
          id: response.id,
          service_category_id: response.category?.id || "",
          service_subcategory_id: response.subcategory?.id || "",
          provider_type: response.provider_type,
          first_name: response.first_name,
          last_name: response.last_name,
          company_name: response.company_name,
          phone: response.phone,
          email: response.email,
          service_description: response.service_description,
          is_published: response.is_published,
        };
        await this.loadSubcategories();
      } catch (error) {
        showSwal.methods.showSwal({
          type: "error",
          message: "Nu am putut încărca furnizorul.",
          width: 380,
        });
        this.$router.push("/service-providers");
      }
    },
    async loadSubcategories() {
      this.subcategoryOptions = [];
      if (!this.form?.service_category_id) return;

      try {
        const response = await this.$store.dispatch("serviceSubcategories/getSubcategories", {
          filter: { service_category_id: this.form.service_category_id },
          page: { number: 1, size: 100 },
          sort: "name",
        });
        this.subcategoryOptions = response.data || [];
      } catch (error) {
        console.error(error);
      }
    },
    async save() {
      this.resetApiValidation();
      this.loading = true;
      try {
        await this.$store.dispatch("serviceProviders/editProvider", this.form);
        showSwal.methods.showSwal({
          type: "success",
          message: "Furnizorul a fost actualizat.",
          width: 350,
        });
        this.$router.push("/service-providers");
      } catch (error) {
        if (error.response?.data?.errors) {
          this.setApiValidation(error.response.data.errors);
        } else {
          showSwal.methods.showSwal({
            type: "error",
            message: "A apărut o eroare neașteptată!",
            width: 350,
          });
        }
      } finally {
        this.loading = false;
      }
    },
  },
};
</script>
