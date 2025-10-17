<template>
  <div class="py-4 container-fluid">
    <div class="row">
      <div class="col-12">
        <div v-if="category" class="card">
          <div class="pb-0 card-header">
            <div class="d-lg-flex align-items-center">
              <div>
                <h5 class="mb-0">Editează categoria de servicii</h5>
              </div>
              <div class="ms-lg-auto mt-3 mt-lg-0">
                <router-link
                  class="mb-0 btn bg-gradient-primary btn-sm"
                  to="/service-categories"
                >
                  &nbsp; Înapoi la listă
                </router-link>
              </div>
            </div>
          </div>

          <div class="card-body">
            <label class="form-label mt-2 row">Nume</label>
            <soft-model-input
              id="name"
              v-model="category.name"
              type="text"
              placeholder="Nume categorie"
            />
            <validation-error :errors="apiValidationErrors.name" />

            <label class="form-label mt-2 row">Descriere</label>
            <soft-model-textarea
              id="description"
              v-model="category.description"
              placeholder="Descriere categorie"
            />
            <validation-error :errors="apiValidationErrors.description" />

            <soft-button
              color="dark"
              variant="gradient"
              class="float-end mt-4 mb-0"
              size="sm"
              :is-disabled="loading"
              @click="updateCategory"
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
  name: "EditServiceCategory",
  components: {
    SoftModelInput,
    SoftModelTextarea,
    SoftButton,
    ValidationError,
  },
  mixins: [formMixin],
  data() {
    return {
      category: null,
      loading: false,
    };
  },
  async created() {
    await this.loadCategory();
  },
  methods: {
    async loadCategory() {
      try {
        const response = await this.$store.dispatch("serviceCategories/getCategory", {
          id: this.$route.params.id,
        });
        this.category = {
          id: response.id,
          name: response.name,
          description: response.description,
        };
      } catch (error) {
        showSwal.methods.showSwal({
          type: "error",
          message: "Nu am putut încărca categoria.",
          width: 380,
        });
        this.$router.push("/service-categories");
      }
    },
    async updateCategory() {
      this.resetApiValidation();
      this.loading = true;
      try {
        await this.$store.dispatch("serviceCategories/editCategory", this.category);
        showSwal.methods.showSwal({
          type: "success",
          message: "Categoria a fost actualizată.",
          width: 350,
        });
        this.$router.push("/service-categories");
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
