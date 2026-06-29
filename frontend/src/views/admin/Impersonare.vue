<template>
  <div class="py-4 container-fluid">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header pb-0">
            <h5 class="mb-0">Impersonare utilizatori</h5>
            <p class="text-sm mb-0">
              Conectează-te ca un administrator de tenant pentru a vedea exact ce vede el.
              Poți reveni oricând la contul tău din bara de sus.
            </p>
          </div>
          <div class="card-body">
            <div v-if="loading" class="text-sm">Se încarcă...</div>
            <div v-else-if="loadError" class="text-sm text-danger">
              {{ loadError }}
              <button class="btn btn-link btn-sm p-0 ms-2" @click="load">Reîncearcă</button>
            </div>
            <div v-else>
              <div class="mb-3">
                <input
                  class="form-control form-control-sm"
                  style="max-width: 320px"
                  v-model="search"
                  placeholder="Caută după nume, email sau beneficiar..."
                />
              </div>

              <div v-if="filtered.length === 0" class="text-sm text-muted">
                Nu există utilizatori de impersonat.
              </div>

              <div v-else class="table-responsive">
                <table class="table table-sm align-items-center">
                  <thead>
                    <tr>
                      <th class="text-xs text-uppercase text-secondary">Nume</th>
                      <th class="text-xs text-uppercase text-secondary">Email</th>
                      <th class="text-xs text-uppercase text-secondary">Beneficiar</th>
                      <th class="text-xs text-uppercase text-secondary">Rol</th>
                      <th class="text-xs text-uppercase text-secondary text-end">Acțiune</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="u in filtered" :key="u.id">
                      <td class="text-sm">{{ u.name }}</td>
                      <td class="text-sm">{{ u.email }}</td>
                      <td class="text-sm">{{ u.tenant_name || '—' }}</td>
                      <td class="text-sm">
                        <span
                          v-for="r in u.roles"
                          :key="r"
                          class="badge badge-sm bg-gradient-secondary me-1"
                          >{{ r }}</span
                        >
                      </td>
                      <td class="text-end">
                        <button
                          class="btn btn-sm bg-gradient-primary mb-0"
                          :disabled="busyId === u.id"
                          @click="impersonate(u)"
                        >
                          <span v-if="busyId === u.id">Se conectează...</span>
                          <span v-else>Impersonează</span>
                        </button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import showSwal from "/src/mixins/showSwal.js";
import impersonationService from "/src/services/impersonation.service.js";
import authService from "/src/services/auth.service.js";

export default {
  name: "Impersonare",
  data() {
    return {
      loading: true,
      loadError: null,
      users: [],
      search: "",
      busyId: null,
    };
  },
  computed: {
    filtered() {
      const q = this.search.trim().toLowerCase();
      if (!q) return this.users;
      return this.users.filter((u) =>
        [u.name, u.email, u.tenant_name].filter(Boolean).some((v) => v.toLowerCase().includes(q))
      );
    },
  },
  async mounted() {
    await this.load();
  },
  methods: {
    async load() {
      this.loading = true;
      this.loadError = null;
      try {
        const res = await impersonationService.getCandidates();
        this.users = res?.data?.users || [];
      } catch (e) {
        this.loadError =
          e?.response?.data?.errors?.[0]?.message || "Nu am putut încărca lista de utilizatori.";
      } finally {
        this.loading = false;
      }
    },
    async impersonate(u) {
      this.busyId = u.id;
      try {
        await authService.impersonate(u.id);
        // Reîncărcare completă ca toate componentele să citească noua identitate.
        window.location.href = "/acasa";
      } catch (e) {
        this.busyId = null;
        const msg =
          e?.response?.data?.errors?.[0]?.message || "Nu am putut porni impersonarea.";
        showSwal.methods.showSwal({ type: "error", message: msg });
      }
    },
  },
};
</script>
