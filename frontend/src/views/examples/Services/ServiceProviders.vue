<template>
  <div class="py-4 container-fluid">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="pb-4 card-header">
            <div class="d-flex flex-column gap-3">
              <div>
                <h5 class="mb-0">Furnizorii de servicii</h5>
                <span class="text-sm text-muted">Gestionare furnizori și ratinguri</span>
              </div>
              <div class="d-flex flex-column flex-lg-row gap-2 flex-wrap">
                <select
                  v-model="filters.category"
                  class="form-control form-control-sm"
                  style="max-width: 200px"
                  @change="onFilterChange"
                >
                  <option value="">Toate categoriile</option>
                  <option v-for="cat in categoryOptions" :key="cat.id" :value="cat.id">
                    {{ cat.name }}
                  </option>
                </select>
                <select
                  v-model="filters.type"
                  class="form-control form-control-sm"
                  style="max-width: 150px"
                  @change="onFilterChange"
                >
                  <option value="">Toate tipurile</option>
                  <option value="private">Persoană fizică</option>
                  <option value="company">Companie</option>
                </select>
                <input
                  v-model="search"
                  type="text"
                  class="form-control form-control-sm"
                  placeholder="Caută furnizor"
                  @input="debouncedSearch"
                />
                <router-link
                  class="btn bg-gradient-success btn-sm"
                  to="/examples/services/providers/new"
                >
                  + Adaugă furnizor
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
                    <th>Tip</th>
                    <th>Categorie</th>
                    <th>Email / Telefon</th>
                    <th>Status</th>
                    <th class="text-end" width="120">Acțiuni</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="provider in providers" :key="provider.id">
                    <td class="align-middle">
                      <h6 class="mb-0">{{ getProviderName(provider) }}</h6>
                    </td>
                    <td class="align-middle text-sm">
                      <span
                        :class="[
                          'badge',
                          provider.provider_type === 'company' ? 'bg-info' : 'bg-secondary'
                        ]"
                      >
                        {{ provider.provider_type === 'company' ? 'Companie' : 'Persoană fizică' }}
                      </span>
                    </td>
                    <td class="align-middle text-sm text-secondary">
                      {{ provider.category?.name ?? '-' }}
                    </td>
                    <td class="align-middle text-sm">
                      <span v-if="provider.email">{{ provider.email }}</span>
                      <br v-if="provider.email && provider.phone" />
                      <span v-if="provider.phone">{{ provider.phone }}</span>
                      <span v-if="!provider.email && !provider.phone">-</span>
                    </td>
                    <td class="align-middle">
                      <span
                        :class="[
                          'badge',
                          provider.is_published ? 'bg-success' : 'bg-warning'
                        ]"
                      >
                        {{ provider.is_published ? 'Publicat' : 'Ciornă' }}
                      </span>
                    </td>
                    <td class="align-middle text-end">
                      <router-link
                        class="btn btn-link text-secondary px-1 mb-0"
                        :to="{ name: 'Edit Service Provider', params: { id: provider.id } }"
                      >
                        <i class="fas fa-user-edit text-secondary"></i>
                      </router-link>
                      <button
                        v-if="canDelete"
                        class="btn btn-link text-danger text-gradient px-1 mb-0"
                        @click="confirmDelete(provider)"
                      >
                        <i class="fas fa-trash"></i>
                      </button>
                    </td>
                  </tr>
                  <tr v-if="!providers?.length">
                    <td colspan="6" class="text-center py-4 text-muted">
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
  name: "ServiceProviders",
  components: {
    BasePagination,
  },
  data() {
    return {
      loading: false,
      search: "",
      filters: {
        category: "",
        type: "",
      },
      page: 1,
      perPage: 10,
    };
  },
  computed: {
    providers() {
      return this.$store.getters["serviceProviders/providers"]?.data;
    },
    meta() {
      return this.$store.getters["serviceProviders/providers"]?.meta;
    },
    pagination() {
      return this.meta?.page;
    },
    categoryOptions() {
      return this.$store.getters["serviceCategories/categories"]?.data ?? [];
    },
    canDelete() {
      const roles = this.$store.getters["auth/roles"] ?? [];
      return roles.includes("admin") || roles.includes("cex");
    },
  },
  created() {
    this.debouncedSearch = _.debounce(() => {
      this.page = 1;
      this.fetchProviders();
    }, 400);
  },
  async mounted() {
    await this.ensureCategoriesLoaded();
    await this.fetchProviders();
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
    async fetchProviders() {
      this.loading = true;
      try {
        await this.$store.dispatch("serviceProviders/getProviders", {
          filter: {
            ...(this.search ? { name: this.search } : {}),
            ...(this.filters.category ? { service_category_id: this.filters.category } : {}),
            ...(this.filters.type ? { provider_type: this.filters.type } : {}),
          },
          page: {
            number: this.page,
            size: this.perPage,
          },
          sort: "-created_at",
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
      this.fetchProviders();
    },
    onPageChange(page) {
      this.page = page;
      this.fetchProviders();
    },
    getProviderName(provider) {
      if (provider.provider_type === "company") {
        return provider.company_name || "Fără nume";
      }
      return `${provider.first_name || ""} ${provider.last_name || ""}`.trim() || "Fără nume";
    },
    async confirmDelete(provider) {
      const result = await showSwal.methods.showSwalConfirmationDelete();
      if (!result.isConfirmed) return;
      try {
        await this.$store.dispatch("serviceProviders/deleteProvider", provider.id);
        await this.fetchProviders();
        showSwal.methods.showSwal({
          type: "success",
          message: "Furnizorul a fost șters.",
          width: 350,
        });
      } catch (error) {
        showSwal.methods.showSwal({
          type: "error",
          message: error.response?.data?.errors?.[0]?.detail || "Nu s-a putut șterge furnizorul.",
          width: 380,
        });
      }
    },
  },
};
</script>
