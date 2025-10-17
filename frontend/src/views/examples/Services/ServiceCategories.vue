<template>
  <div class="py-4 container-fluid">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="pb-4 card-header">
            <div class="d-flex flex-column flex-lg-row align-items-lg-center">
              <div>
                <h5 class="mb-0">Categorii servicii</h5>
                <span class="text-sm text-muted">Administrare categorii per beneficiar</span>
              </div>
              <div class="ms-lg-auto mt-3 mt-lg-0 d-flex gap-2">
                <input
                  v-model="search"
                  type="text"
                  class="form-control form-control-sm"
                  placeholder="Caută categorie"
                  @input="debouncedSearch"
                />
                <router-link
                  class="mb-0 btn bg-gradient-primary btn-sm"
                  to="/service-categories/new"
                >
                  + Adaugă categorie
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
                    <th class="cursor-pointer" @click="toggleSort('name')">
                      Nume
                      <i :class="sortIcon('name')" class="ms-1"></i>
                    </th>
                    <th>Descriere</th>
                    <th class="cursor-pointer" @click="toggleSort('created_at')">
                      Creat la
                      <i :class="sortIcon('created_at')" class="ms-1"></i>
                    </th>
                    <th class="text-end" width="120">Acțiuni</th>
                  </tr>
                </thead>
                <tbody>
                  <tr
                    v-for="category in categories"
                    :key="category.id"
                  >
                    <td class="align-middle">
                      <h6 class="mb-0">{{ category.name }}</h6>
                    </td>
                    <td class="align-middle">{{ category.description }}</td>
                    <td class="align-middle">{{ formatDate(category.created_at) }}</td>
                    <td class="align-middle text-end">
                      <router-link
                        class="btn btn-link text-secondary px-1 mb-0"
                        :to="{ name: 'Edit Service Category', params: { id: category.id } }"
                      >
                        <i class="fas fa-user-edit text-secondary"></i>
                      </router-link>
                      <button
                        class="btn btn-link text-danger text-gradient px-1 mb-0"
                        @click="confirmDelete(category)"
                      >
                        <i class="fas fa-trash"></i>
                      </button>
                    </td>
                  </tr>
                  <tr v-if="!categories?.length">
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
                class="pagination-primary pagination-md"
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
import showSwal from "/src/mixins/showSwal.js";
import BasePagination from "/src/components/BasePagination.vue";

const DEFAULT_SORT = "-created_at";

export default {
  name: "ServiceCategories",
  components: {
    BasePagination,
  },
  data() {
    return {
      loading: false,
      search: "",
      sort: DEFAULT_SORT,
      page: 1,
      perPage: 10,
    };
  },
  computed: {
    categories() {
      return this.$store.getters["serviceCategories/categories"]?.data;
    },
    meta() {
      return this.$store.getters["serviceCategories/categories"]?.meta;
    },
    pagination() {
      return this.meta?.page;
    },
  },
  created() {
    this.debouncedSearch = _.debounce(this.fetchCategories, 400);
  },
  mounted() {
    this.fetchCategories();
  },
  methods: {
    async fetchCategories() {
      this.loading = true;
      try {
        await this.$store.dispatch("serviceCategories/getCategories", {
          filter: {
            ...(this.search ? { name: this.search } : {}),
          },
          page: {
            number: this.page,
            size: this.perPage,
          },
          sort: this.sort,
        });
      } catch (error) {
        console.error(error);
      } finally {
        this.loading = false;
      }
    },
    onPageChange(page) {
      this.page = page;
      this.fetchCategories();
    },
    toggleSort(field) {
      if (this.sort === field) {
        this.sort = `-${field}`;
      } else if (this.sort === `-${field}`) {
        this.sort = field;
      } else {
        this.sort = field === "created_at" ? DEFAULT_SORT : field;
      }
      this.fetchCategories();
    },
    sortIcon(field) {
      if (this.sort === field) return "fas fa-sort-up";
      if (this.sort === `-${field}`) return "fas fa-sort-down";
      return "fas fa-sort";
    },
    formatDate(value) {
      if (!value) return "-";
      const date = new Date(value);
      return date.toLocaleString();
    },
    async confirmDelete(category) {
      const result = await showSwal.methods.showSwalConfirmationDelete();
      if (result.isConfirmed) {
        try {
          await this.$store.dispatch("serviceCategories/deleteCategory", category.id);
          await this.fetchCategories();
          showSwal.methods.showSwal({
            type: "success",
            message: "Categoria a fost ștearsă.",
            width: 350,
          });
        } catch (error) {
          showSwal.methods.showSwal({
            type: "error",
            message: error.response?.data?.errors?.[0]?.detail || "Nu s-a putut șterge categoria.",
            width: 380,
          });
        }
      }
    },
  },
};
</script>
