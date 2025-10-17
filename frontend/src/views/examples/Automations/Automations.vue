<template>
  <div class="py-4 container-fluid">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="pb-0 card-header">
            <div class="d-lg-flex">
              <div>
                <h5 class="mb-0">Listă Automatizări</h5>
              </div>
              <div class="my-auto mt-4 ms-auto mt-lg-0">
                <div class="my-auto ms-auto">
                  <router-link to="/automations/new" class="mb-0 btn bg-gradient-primary btn-sm"
                    >+&nbsp; Automatizare Nouă</router-link
                  >
                </div>
              </div>
            </div>
          </div>
          <div class="px-0 pb-0 card-body">
            <div class="table-responsive">
              <table id="automations-list" ref="automationsList" class="table table-flush">
                <thead class="thead-light">
                  <tr>
                    <th title="name">Nume</th>
                    <th title="type">Tip</th>
                    <th data-sortable="false">Broker MQTT</th>
                    <th data-sortable="false">Topic</th>
                    <th title="is_active">Status</th>
                    <th title="created_at">Creat la</th>
                    <th data-sortable="false">Acțiuni</th>
                  </tr>
                </thead>
                <tbody class="text-sm"></tbody>
                <tfoot>
                  <tr>
                    <th>Nume</th>
                    <th>Tip</th>
                    <th>Broker MQTT</th>
                    <th>Topic</th>
                    <th>Status</th>
                    <th>Creat la</th>
                    <th>Acțiuni</th>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>
          <div class="d-flex justify-content-center justify-content-sm-between flex-wrap">
            <div class="ms-3">
              <p>
                Showing {{ pagination?.from }} to {{ pagination?.to }} of
                {{ pagination?.total }} entries
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
var loading = require("/src/assets/img/loading.gif");

const getAutomationsList = _.debounce(async function (params) {
  await store.dispatch("automations/getAutomations", {
    ...(params.sort ? { sort: params.sort } : {}),
    filter: {
      ...(params.query ? { name: params.query } : {}),
    },
    page: {
      number: params.nr,
      size: params.perpage,
    },
    include: "tenant",
  });
}, 300);

export default {
  name: "Automations",
  components: {
    BasePagination,
  },
  mixins: [eventTable],
  data() {
    return {
      automationsAux: [],
      pagination: {},
      tableAutomations: null,
    };
  },
  computed: {
    automationsList() {
      return this.$store.getters["automations/automations"]?.data;
    },
    metaPage() {
      return this.$store.getters["automations/automations"]?.meta;
    },
  },
  watch: {
    metaPage: {
      handler: "reactivePagination",
      immediate: false,
      deep: true,
    },
    automationsList: {
      handler: "reactiveTable",
      immediate: false,
      deep: true,
    },
  },

  async mounted() {
    if (this.$refs.automationsList) {
      this.tableAutomations = new DataTable(this.$refs.automationsList, {
        fixedHeight: false,
        perPage: 5,
      });

      const bottom = document.querySelector(".dataTable-bottom");
      if (bottom) bottom.remove();
      this.tableAutomations.label = null;
      this.tableAutomations.setMessage(
        `<img src="${loading}" width="100" height="100" alt="loading" />`
      );

      await getAutomationsList({
        query: currentQuery,
        perpage: currentPerPage,
        nr: currentPage,
        sort: currentSort,
      });

      this.tableAutomations.on("datatable.perpage", async function (perpage) {
        this.setMessage(
          `<img src="${loading}" width="100" height="100" alt="loading" />`
        );
        await getAutomationsList({
          query: currentQuery,
          perpage: (currentPerPage = perpage),
          nr: (currentPage = 1),
          sort: currentSort,
        });
      });

      this.tableAutomations.on("datatable.sort", async function (column, direction) {
        this.setMessage(
          `<img src="${loading}" width="100" height="100" alt="loading" />`
        );
        direction = direction == "asc" ? "" : "-";
        column = this.headings[column].title;
        await getAutomationsList({
          query: currentQuery,
          perpage: currentPerPage,
          nr: currentPage,
          sort: (currentSort = direction + column),
        });
      });

      // eslint-disable-next-line no-unused-vars
      this.tableAutomations.on("datatable.search", async function (query) {
        this.setMessage(
          `<img src="${loading}" width="100" height="100" alt="loading" />`
        );
        await getAutomationsList({
          query: (currentQuery = query),
          perpage: currentPerPage,
          nr: (currentPage = 1),
          sort: currentSort,
        });
      });
    }
  },

  beforeUnmount() {
    currentQuery = "";
    currentPerPage = 5;
    currentPage = 1;
    currentSort = "created_at";
  },

  methods: {
    async getDataFromPage(page) {
      await getAutomationsList({
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
      this.automationsAux = [];
      if (this.automationsList?.length > 0) {
        this.automationsList.forEach((row) => {
          const typeLabels = {
            switch: '🔘 Comutator',
            sensor: '📡 Senzor',
            actuator: '⚙️ Actuator',
            light: '💡 Lumină',
            lock: '🔒 Zăvor'
          };
          const statusBadge = row.is_active 
            ? '<span class="badge badge-sm bg-gradient-primary">Activ</span>'
            : '<span class="badge badge-sm bg-gradient-secondary">Inactiv</span>';
          
          this.automationsAux.push([
            `<h6 class="my-auto">${row.name}</h6>`,
            typeLabels[row.device_type || row.type] || row.device_type || row.type,
            `${row.mqtt_broker_host}:${row.mqtt_broker_port}`,
            row.mqtt_topic,
            statusBadge,
            row.created_at,
            this.actionEditButton(row.id, "Editează automatizare") +
              this.actionDeleteButton(row.id, "Șterge automatizare"),
          ]);
        });
        this.tableAutomations.data = [];
        this.tableAutomations.refresh();
        document.querySelector(".dataTable-input").value = currentQuery;
        this.tableAutomations.insert({ data: this.automationsAux });
        this.removeEvent();
        this.eventToCall({
          table: this.tableAutomations,
          redirectPath: "Edit Automation",
          deletePath: "automations/deleteAutomation",
          getPath: "automations/getAutomations",
          textDelete: "Automatizare ștearsă cu succes!",
          textDefaultData: "automations",
          textDeleteError: "A apărut o eroare la ștergerea automatizării.",
          params: {
            query: currentQuery,
            perpage: currentPerPage,
            nr: currentPage,
            sort: currentSort,
            include: "tenant",
          },
        });
      } else {
        this.tableAutomations.setMessage("Nu există rezultate pentru căutarea ta");
      }
    },
  },
};
</script>

