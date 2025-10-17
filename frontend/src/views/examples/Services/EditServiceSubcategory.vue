<template>
  <div class="py-4 container-fluid">
    <div class="row">
      <div class="col-12">
        <div v-if="subcategory" class="card">
          <div class="pb-0 card-header">
            <div class="d-lg-flex align-items-center">
              <div>
                <h5 class="mb-0">Editează subcategoria</h5>
              </div>
              <div class="ms-lg-auto mt-3 mt-lg-0">
                <router-link
                  class="mb-0 btn bg-gradient-primary btn-sm"
                  to="/service-subcategories"
                >
                  &nbsp; Înapoi la listă
                </router-link>
              </div>
            </div>
          </div>

          <div class="card-body">
            <label class="form-label mt-2 row">Categorie</label>
            <select
              v-model="subcategory.service_category_id"
              class="form-control"
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

            <label class="form-label mt-2 row">Nume</label>
            <soft-model-input
              id="name"
              v-model="subcategory.name"
              type="text"
              placeholder="Nume subcategorie"
            />
            <validation-error :errors="apiValidationErrors.name" />

            <label class="form-label mt-2 row">Descriere</label>
            <soft-model-textarea
              id="description"
              v-model="subcategory.description"
              placeholder="Descriere subcategorie"
            />
            <validation-error :errors="apiValidationErrors.description" />

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

export default {
  name: "EditServiceSubcategory",
  components: {
    SoftModelInput,
    SoftModelTextarea,
    SoftButton,
    ValidationError,
  },
  mixins: [formMixin],
  data() {
    return {
      subcategory: null,
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
    await this.loadSubcategory();
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
    async loadSubcategory() {
      try {
        const response = await this.$store.dispatch("serviceSubcategories/getSubcategory", {
          id: this.$route.params.id,
          params: { include: "category" },
        });
        this.subcategory = {
          id: response.id,
          name: response.name,
          description: response.description,
          service_category_id: response.category?.id || "",
        };
      } catch (error) {
        showSwal.methods.showSwal({
          type: "error",
          message: "Nu am putut încărca subcategoria.",
          width: 380,
        });
        this.$router.push("/service-subcategories");
      }
    },
    async save() {
      this.resetApiValidation();
      this.loading = true;
      try {
        await this.$store.dispatch("serviceSubcategories/editSubcategory", this.subcategory);
        showSwal.methods.showSwal({
          type: "success",
          message: "Subcategoria a fost actualizată.",
          width: 350,
        });
        this.$router.push("/service-subcategories");
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
