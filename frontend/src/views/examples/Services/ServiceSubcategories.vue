<template>
  <div class="py-4 container-fluid">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="pb-4 card-header">
            <div class="d-flex flex-column flex-lg-row align-items-lg-center gap-3">
              <div>
                <h5 class="mb-0">Subcategorii servicii</h5>
                <span class="text-sm text-muted">Structură detaliată pe categorii</span>
              </div>
              <div class="ms-lg-auto d-flex flex-column flex-sm-row gap-2 w-100 w-sm-auto">
                <select
                  v-model="selectedCategory"
                  class="form-control form-control-sm"
                  @change="onFilterChange"
                >
                  <option value="">Toate categoriile</option>
                  <option v-for="option in categoryOptions" :key="option.id" :value="option.id">
                    {{ option.name }}
                  </option>
                </select>
                <input
                  v-model="search"
                  type="text"
                  class="form-control form-control-sm"
                  placeholder="Caută subcategorie"
                  @input="debouncedSearch"
                />
                <router-link
                  class="mb-0 btn bg-gradient-success btn-sm"
                  to="/examples/services/subcategories/new"
                >
                  + Adaugă subcategorie
                </router-link>
              </div>
            </div>
          </div>

          <div class="px-0 pb-0 card-body">
            <div v-if="loading" class="text-center py-5">
              <span class="spinner-border" role="status"></span>
            </div>
            <div v-else class="table-responsive">
              <table class="table table-flush">
                <thead class="thead-light">
                  <tr>
                    <th>Nume</th>
                    <th>Categorie</th>
                    <th>Descriere</th>
                    <th class="text-end" width="120">Acțiuni</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="subcategory in subcategories" :key="subcategory.id">
                    <td class="align-middle">
                      <h6 class="mb-0">{{ subcategory.name }}</h6>
                    </td>
                    <td class="align-middle text-sm text-secondary">
                      {{ subcategory.category?.name ?? "-" }}
                    </td>
                    <td class="align-middle">{{ subcategory.description || "-" }}</td>
                    <td class="align-middle text-end">
                      <router-link
                        class="btn btn-link text-secondary px-1 mb-0"
                        :to="{ name: 'Edit Service Subcategory', params: { id: subcategory.id } }"
                      >
                        <i class="fas fa-user-edit text-secondary"></i>
                      </router-link>
                      <button
                        class="btn btn-link text-danger text-gradient px-1 mb-0"
                        @click="confirmDelete(subcategory)"
                      >
                        <i class="fas fa-trash"></i>
                      </button>
                    </td>
                  </tr>
                  <tr v-if="!subcategories?.length">
                    <td colspan="4" class="text-center py-4 text-muted">
                      Nu există rezultate pentru criteriile curente.
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div v-if="pagination" class="d-flex justify-content-between align-items-center px-3 py-2">
              <p class="mb-0 text-sm">
                Afișează {{ pagination.from }} - {{ pagination.to }} din {{ pagination.total }} înregistrări
              </p>
              <BasePagination
                class="pagination-success pagination-md"
                :per-page="pagination.perPage"
                :value="pagination.currentPage"
                :total="pagination.total"
                @click="onPageChange"
              />
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import _ from "lodash";
import BasePagination from "/src/components/BasePagination.vue";
import showSwal from "/src/mixins/showSwal.js";

export default {
  name: "ServiceSubcategories",
  components: {
    BasePagination,
  },
  data() {
    return {
      loading: false,
      search: "",
      selectedCategory: "",
      page: 1,
      perPage: 10,
    };
  },
  computed: {
    subcategories() {
      return this.$store.getters["serviceSubcategories/subcategories"]?.data;
    },
    meta() {
      return this.$store.getters["serviceSubcategories/subcategories"]?.meta;
    },
    pagination() {
      return this.meta?.page;
    },
    categoryOptions() {
      return this.$store.getters["serviceCategories/categories"]?.data ?? [];
    },
  },
  created() {
    this.debouncedSearch = _.debounce(() => {
      this.page = 1;
      this.fetchSubcategories();
    }, 400);
  },
  async mounted() {
    await this.ensureCategoriesLoaded();
    await this.fetchSubcategories();
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
    async fetchSubcategories() {
      this.loading = true;
      try {
        await this.$store.dispatch("serviceSubcategories/getSubcategories", {
          filter: {
            ...(this.search ? { name: this.search } : {}),
            ...(this.selectedCategory ? { service_category_id: this.selectedCategory } : {}),
          },
          page: {
            number: this.page,
            size: this.perPage,
          },
          sort: "name",
          include: "category",
        });
      } catch (error) {
        console.error(error);
      } finally {
        this.loading = false;
      }
    },
    onFilterChange() {
      this.page = 1;
      this.fetchSubcategories();
    },
    onPageChange(page) {
      this.page = page;
      this.fetchSubcategories();
    },
    async confirmDelete(subcategory) {
      const result = await showSwal.methods.showSwalConfirmationDelete();
      if (!result.isConfirmed) return;
      try {
        await this.$store.dispatch("serviceSubcategories/deleteSubcategory", subcategory.id);
        await this.fetchSubcategories();
        showSwal.methods.showSwal({
          type: "success",
          message: "Subcategoria a fost ștearsă.",
          width: 350,
        });
      } catch (error) {
        showSwal.methods.showSwal({
          type: "error",
          message: error.response?.data?.errors?.[0]?.detail || "Nu s-a putut șterge subcategoria.",
          width: 380,
        });
      }
    },
  },
};
</script>
