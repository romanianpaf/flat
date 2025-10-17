<template>
  <div class="py-4 container-fluid">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="pb-0 card-header">
            <div class="d-lg-flex">
              <div>
                <h5 class="mb-0">Gestionare Permisiuni - {{ roleName }}</h5>
                <p class="mb-0 text-sm">Selectează permisiunile pentru acest rol</p>
              </div>
              <div class="my-auto mt-4 ms-auto mt-lg-0">
                <router-link
                  to="/roles/list"
                  class="mb-0 btn btn-outline-primary btn-sm"
                >
                  ← Înapoi la Roluri
                </router-link>
              </div>
            </div>
          </div>
          <div class="p-3 card-body">
            <div v-if="loading" class="text-center">
              <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Se încarcă...</span>
              </div>
            </div>

            <div v-else>
              <!-- Filtre rapide -->
              <div class="mb-4">
                <div class="btn-group btn-group-sm" role="group">
                  <button
                    type="button"
                    class="btn"
                    :class="filter === 'all' ? 'btn-primary' : 'btn-outline-primary'"
                    @click="filter = 'all'"
                  >
                    Toate ({{ allPermissions.length }})
                  </button>
                  <button
                    type="button"
                    class="btn"
                    :class="filter === 'selected' ? 'btn-primary' : 'btn-outline-primary'"
                    @click="filter = 'selected'"
                  >
                    Selectate ({{ selectedPermissions.length }})
                  </button>
                  <button
                    type="button"
                    class="btn"
                    :class="filter === 'unselected' ? 'btn-primary' : 'btn-outline-primary'"
                    @click="filter = 'unselected'"
                  >
                    Neselectate ({{ allPermissions.length - selectedPermissions.length }})
                  </button>
                </div>
                <div class="mt-2">
                  <button
                    type="button"
                    class="btn btn-sm btn-success me-2"
                    @click="selectAll"
                  >
                    Selectează Tot
                  </button>
                  <button
                    type="button"
                    class="btn btn-sm btn-danger"
                    @click="deselectAll"
                  >
                    Deselectează Tot
                  </button>
                </div>
              </div>

              <!-- Grupează permisiunile pe module -->
              <div v-for="(permissions, module) in groupedPermissions" :key="module" class="mb-4">
                <h6 class="text-uppercase text-xs font-weight-bolder opacity-7 mb-2">
                  {{ module }}
                  <span class="badge bg-gradient-info badge-sm ms-2">
                    {{ permissions.length }} permisiuni
                  </span>
                </h6>
                <div class="row">
                  <div
                    v-for="permission in permissions"
                    :key="permission.id"
                    class="col-12 col-md-6 col-lg-4 mb-2"
                  >
                    <div class="form-check">
                      <input
                        :id="`perm-${permission.id}`"
                        v-model="selectedPermissions"
                        class="form-check-input"
                        type="checkbox"
                        :value="permission.name"
                      />
                      <label
                        :for="`perm-${permission.id}`"
                        class="form-check-label custom-control-label"
                      >
                        {{ permission.display_name }}
                        <span class="text-xs text-muted">({{ permission.name }})</span>
                      </label>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Butoane de salvare -->
              <div class="mt-4">
                <soft-button
                  color="primary"
                  variant="gradient"
                  size="sm"
                  :is-disabled="saving"
                  @click="savePermissions"
                >
                  <span v-if="saving" class="spinner-border spinner-border-sm me-2"></span>
                  Salvează Permisiunile
                </soft-button>
                <soft-button
                  color="secondary"
                  variant="outline"
                  size="sm"
                  class="ms-2"
                  @click="$router.push('/roles/list')"
                >
                  Anulează
                </soft-button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import SoftButton from "@/components/SoftButton.vue";
import rolePermissionsService from "@/services/role-permissions.service";
import showSwal from "@/mixins/showSwal.js";

export default {
  name: "RolePermissions",
  components: {
    SoftButton,
  },
  data() {
    return {
      roleId: null,
      roleName: "",
      loading: true,
      saving: false,
      allPermissions: [],
      selectedPermissions: [],
      filter: "all",
    };
  },
  computed: {
    /**
     * Grupează permisiunile pe module (users, roles, categories, etc.)
     */
    groupedPermissions() {
      const filtered = this.filteredPermissions;
      const groups = {};

      filtered.forEach((permission) => {
        // Extrage modulul din numele permisiunii (ex: "view users" -> "Users")
        const parts = permission.name.split(" ");
        const module = parts.length > 1 
          ? parts.slice(1).join(" ").charAt(0).toUpperCase() + parts.slice(1).join(" ").slice(1)
          : "General";

        if (!groups[module]) {
          groups[module] = [];
        }
        groups[module].push(permission);
      });

      return groups;
    },

    /**
     * Filtrează permisiunile în funcție de filtrul activ
     */
    filteredPermissions() {
      if (this.filter === "selected") {
        return this.allPermissions.filter((p) =>
          this.selectedPermissions.includes(p.name)
        );
      } else if (this.filter === "unselected") {
        return this.allPermissions.filter(
          (p) => !this.selectedPermissions.includes(p.name)
        );
      }
      return this.allPermissions;
    },
  },
  async mounted() {
    this.roleId = this.$route.params.id;
    await this.loadData();
  },
  methods: {
    async loadData() {
      this.loading = true;
      try {
        // Încarcă toate permisiunile disponibile
        const permissionsResponse = await rolePermissionsService.getAvailablePermissions();
        this.allPermissions = permissionsResponse.data.data;

        // Încarcă permisiunile rolului
        const roleResponse = await rolePermissionsService.getRolePermissions(this.roleId);
        this.roleName = roleResponse.data.data.attributes.name;

        // Extrage permisiunile selectate
        if (roleResponse.data.included) {
          this.selectedPermissions = roleResponse.data.included
            .filter((item) => item.type === "permissions")
            .map((item) => item.attributes.name);
        }
      } catch (error) {
        console.error("Error loading permissions:", error);
        showSwal.methods.showSwal({
          type: "error",
          message: "Eroare la încărcarea permisiunilor!",
        });
      } finally {
        this.loading = false;
      }
    },

    async savePermissions() {
      this.saving = true;
      try {
        await rolePermissionsService.syncRolePermissions(
          this.roleId,
          this.selectedPermissions
        );

        showSwal.methods.showSwal({
          type: "success",
          message: "Permisiunile au fost actualizate cu succes!",
        });

        // Redirect după 1 secundă
        setTimeout(() => {
          this.$router.push({ name: "Roles" });
        }, 1000);
      } catch (error) {
        console.error("Error saving permissions:", error);
        showSwal.methods.showSwal({
          type: "error",
          message: error.response?.data?.message || "Eroare la salvarea permisiunilor!",
        });
      } finally {
        this.saving = false;
      }
    },

    selectAll() {
      this.selectedPermissions = this.allPermissions.map((p) => p.name);
    },

    deselectAll() {
      this.selectedPermissions = [];
    },
  },
};
</script>

<style scoped>
.form-check-label {
  cursor: pointer;
  user-select: none;
}

.form-check-input:checked ~ .form-check-label {
  font-weight: 600;
}
</style>

