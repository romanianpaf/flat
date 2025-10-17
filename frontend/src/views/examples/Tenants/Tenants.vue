<template>
  <div class="py-4 container-fluid">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="pb-0 card-header">
            <div class="d-lg-flex">
              <div>
                <h5 class="mb-0">Listă Rezidenți</h5>
              </div>
              <div class="my-auto mt-4 ms-auto mt-lg-0">
                <div class="my-auto ms-auto">
                  <router-link to="/tenants/new" class="mb-0 btn bg-gradient-primary btn-sm"
                    >+&nbsp; Beneficiar Nou</router-link
                  >
                </div>
              </div>
            </div>
          </div>
          <div class="px-0 pb-0 card-body">
            <div class="table-responsive">
              <table id="tenants-list" ref="tenantsList" class="table table-flush">
                <thead class="thead-light">
                  <tr>
                    <th title="name">Nume</th>
                    <th>CUI</th>
                    <th>Contact</th>
                    <th title="created_at">Creat La</th>
                    <th data-sortable="false">Acțiuni</th>
                  </tr>
                </thead>
                <tbody class="text-sm"></tbody>
                <tfoot>
                  <tr>
                    <th>Nume</th>
                    <th>CUI</th>
                    <th>Contact</th>
                    <th>Creat La</th>
                    <th>Acțiuni</th>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>
          <div class="d-flex justify-content-center justify-content-sm-between flex-wrap">
            <div class="ms-3">
              <p>
                Afișare {{ pagination?.from }} - {{ pagination?.to }} din
                {{ pagination?.total }} înregistrări
              </p>
            </div>
            <BasePagination
              class="pagination-primary pagination-md me-3"
              :per-page="pagination?.perPage"
              :value="pagination?.currentPage"
              :total="pagination?.total"
              @click="getDataFromPage($event)"
            />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { DataTable } from "simple-datatables";
import BasePagination from "/src/components/BasePagination.vue";
import eventTable from "/src/mixins/eventTable.js";
import store from "/src/store";
import _ from "lodash";

var currentQuery = "";
var currentPerPage = 5;
var currentPage = 1;
var currentSort = "created_at";

const getTenantsList = _.debounce(async function (params) {
  await store.dispatch("tenants/getTenants", {
    ...(params.sort ? { sort: params.sort } : {}),
    filter: {
      ...(params.query ? { name: params.query } : {}),
    },
    page: {
      number: params.nr,
      size: params.perpage,
    },
  });
}, 300);

export default {
  name: "Tenants",
  components: {
    BasePagination,
  },
  mixins: [eventTable],
  data() {
    return {
      tenantsAux: [],
      pagination: {},
      tableTenants: null,
      hasError: false,
    };
  },
  computed: {
    tenantsList() {
      return this.$store.getters["tenants/tenants"]?.data;
    },
    metaPage() {
      return this.$store.getters["tenants/tenants"]?.meta;
    },
  },
  watch: {
    tenantsList: {
      handler() {
        this.reactiveTable();
      },
      immediate: false,
      deep: true,
    },

    metaPage: {
      handler() {
        this.reactivePagination();
      },
      immediate: false,
      deep: true,
    },
  },
  async created() {
    await getTenantsList({
      query: currentQuery,
      nr: currentPage,
      perpage: currentPerPage,
      sort: currentSort,
    });
  },
  mounted() {
    this.tableTenants = new DataTable(this.$refs.tenantsList, {
      searchable: true,
      perPage: currentPerPage,
      sortable: false,
    });

    this.tableTenants.on("datatable.search", (query) => {
      currentQuery = query;
      getTenantsList({
        query: currentQuery,
        nr: currentPage,
        perpage: currentPerPage,
        sort: currentSort,
      });
    });

    this.tableTenants.on("datatable.perpage", (perpage) => {
      currentPerPage = perpage;
      getTenantsList({
        query: currentQuery,
        nr: currentPage,
        perpage: currentPerPage,
        sort: currentSort,
      });
    });

    this.tableTenants.on("datatable.sort", (column, direction) => {
      const sortMap = {
        0: "name",
        1: "fiscal_code",
        3: "created_at",
      };

      const sortDirection = direction === "asc" ? "" : "-";
      currentSort = sortDirection + (sortMap[column] || "created_at");

      getTenantsList({
        query: currentQuery,
        nr: currentPage,
        perpage: currentPerPage,
        sort: currentSort,
      });
    });
  },

  beforeUnmount() {
    currentQuery = "";
    currentPerPage = 5;
    currentPage = 1;
    currentSort = "created_at";
  },

  methods: {
    async getDataFromPage(page) {
      await getTenantsList({
        query: currentQuery,
        perpage: currentPerPage,
        nr: (currentPage = page),
        sort: currentSort,
      });
    },

    async reactivePagination() {
      this.pagination = this.metaPage?.page;
    },

    async reactiveTable() {
      this.tenantsAux = [];
      if (this.tenantsList?.length > 0) {
        this.tenantsList.forEach((row) => {
          const contact = row.contact_data?.email || row.contact_data?.phone || "—";
          this.tenantsAux.push([
            `<h6 class="my-auto">${row.name}</h6>`,
            row.fiscal_code || "—",
            contact,
            row.created_at,
            this.actionEditButton(row.id, "Editează rezident") +
              this.actionDeleteButton(row.id, "Șterge rezident"),
          ]);
        });
        this.tableTenants.data = [];
        this.tableTenants.refresh();
        document.querySelector(".dataTable-input").value = currentQuery;
        this.tableTenants.insert({ data: this.tenantsAux });
        this.removeEvent();
        this.eventToCall({
          table: this.tableTenants,
          redirectPath: "Edit Tenant",
          deletePath: "tenants/deleteTenant",
          getPath: "tenants/getTenants",
          textDelete: "Beneficiar șters cu succes!",
          textDefaultData: "beneficiari",
          textDeleteError: "A apărut o eroare la ștergerea rezidentului.",
          params: {
            query: currentQuery,
            perpage: currentPerPage,
            nr: currentPage,
            sort: currentSort,
          },
        });
      } else {
        this.tableTenants.setMessage("Nu există rezultate pentru căutarea ta");
      }
    },
  },
};
</script>
