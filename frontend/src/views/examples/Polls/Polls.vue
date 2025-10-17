<template>
  <div class="py-4 container-fluid">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="pb-0 card-header">
            <div class="d-lg-flex">
              <div>
                <h5 class="mb-0">Listă Sondaje</h5>
                <p class="mb-0 text-sm">Gestionează sondajele de opinie</p>
              </div>
              <div class="my-auto mt-4 ms-auto mt-lg-0">
                <div class="my-auto ms-auto">
                  <router-link
                    to="/polls/new"
                    class="mb-0 btn bg-gradient-primary btn-sm"
                    >+ Sondaj Nou</router-link
                  >
                </div>
              </div>
            </div>
          </div>
          <div class="px-0 pb-0 card-body">
            <div class="table-responsive">
              <table ref="pollsList" class="table table-flush">
                <thead class="thead-light">
                  <tr>
                    <th title="title">Titlu</th>
                    <th data-sortable="false">Status</th>
                    <th data-sortable="false">Opțiuni</th>
                    <th title="created_at">Creat la</th>
                    <th data-sortable="false">Acțiuni</th>
                  </tr>
                </thead>
                <tbody class="text-sm"></tbody>
              </table>
            </div>
            <div class="d-flex justify-content-center mt-4">
              <ul v-if="pagination" class="pagination">
                <li
                  v-for="(page, index) in pagination.links"
                  :key="index"
                  class="page-item"
                  :class="{
                    disabled: !page.url,
                    active: page.active,
                  }"
                >
                  <a
                    class="page-link"
                    href="javascript:;"
                    aria-label="pagination.links[index].label"
                    @click="getDataFromPage(pagination.links[index].label)"
                  >
                    <span
                      aria-hidden="true"
                      v-html="pagination.links[index].label"
                    ></span>
                  </a>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { DataTable } from "simple-datatables";
import setNavPills from "@/assets/js/nav-pills.js";
import eventTable from "/src/mixins/eventTable.js";

var loading = require("/src/assets/img/loading.gif");

let currentQuery = "";
let currentPerPage = 5;
let currentPage = 1;
let currentSort = "created_at";

async function getPollsList(params) {
  await this.$store.dispatch("polls/getPolls", {
    filter: { title: params.query },
    page: { size: params.perpage, number: params.nr },
    sort: params.sort,
    include: "options",
  });
}

const getPollsListObj = {
  methods: { getPollsList },
};

export default {
  name: "Polls",
  mixins: [setNavPills, getPollsListObj, eventTable],
  data() {
    return {
      pagination: null,
      pollsAux: [],
    };
  },
  computed: {
    pollsList() {
      return this.$store.getters["polls/polls"]?.data;
    },
    metaPage() {
      return this.$store.getters["polls/polls"]?.meta;
    },
  },
  watch: {
    metaPage: {
      handler: "reactivePagination",
      immediate: false,
      deep: true,
    },
    pollsList: {
      handler: "reactiveTable",
      immediate: false,
      deep: true,
    },
  },

  async mounted() {
    if (this.$refs.pollsList) {
      this.tablePolls = new DataTable(this.$refs.pollsList, {
        fixedHeight: false,
        perPage: 5,
      });

      const bottom = document.querySelector(".dataTable-bottom");
      if (bottom) bottom.remove();
      this.tablePolls.label = null;
      this.tablePolls.setMessage(
        `<img src="${loading}" width="100" height="100" alt="loading" />`
      );

      await this.getPollsList({
        query: currentQuery,
        perpage: currentPerPage,
        nr: currentPage,
        sort: currentSort,
      });

      const self = this;
      this.tablePolls.on("datatable.perpage", async function (perpage) {
        this.setMessage(
          `<img src="${loading}" width="100" height="100" alt="loading" />`
        );
        await self.getPollsList({
          query: currentQuery,
          perpage: (currentPerPage = perpage),
          nr: (currentPage = 1),
          sort: currentSort,
        });
      });

      this.tablePolls.on("datatable.sort", async function (column, direction) {
        this.setMessage(
          `<img src="${loading}" width="100" height="100" alt="loading" />`
        );
        direction = direction == "asc" ? "" : "-";
        column = this.headings[column].title;
        await self.getPollsList({
          query: currentQuery,
          perpage: currentPerPage,
          nr: currentPage,
          sort: (currentSort = direction + column),
        });
      });

      // eslint-disable-next-line no-unused-vars
      this.tablePolls.on("datatable.search", async function (query) {
        this.setMessage(
          `<img src="${loading}" width="100" height="100" alt="loading" />`
        );
        await self.getPollsList({
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
      await this.getPollsList({
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
      this.pollsAux = [];
      if (this.pollsList?.length > 0) {
        this.pollsList.forEach((row) => {
          const statusBadge = row.is_active 
            ? '<span class="badge badge-sm bg-gradient-primary">Activ</span>'
            : '<span class="badge badge-sm bg-gradient-secondary">Inactiv</span>';
          
          const optionsCount = row.options?.length || 0;
          
          this.pollsAux.push([
            `<h6 class="my-auto">${row.title}</h6>`,
            statusBadge,
            `${optionsCount} opțiuni`,
            row.created_at,
            this.actionEditButton(row.id, "Editează sondaj") +
              this.actionDeleteButton(row.id, "Șterge sondaj"),
          ]);
        });
        this.tablePolls.data = [];
        this.tablePolls.refresh();
        document.querySelector(".dataTable-input").value = currentQuery;
        this.tablePolls.insert({ data: this.pollsAux });
        this.removeEvent();
        this.eventToCall({
          table: this.tablePolls,
          redirectPath: "Edit Poll",
          deletePath: "polls/deletePoll",
          getPath: "polls/getPolls",
          textDelete: "Sondaj șters cu succes!",
          textDefaultData: "polls",
          textDeleteError: "A apărut o eroare la ștergerea sondajului.",
          params: {
            query: currentQuery,
            perpage: currentPerPage,
            nr: currentPage,
            sort: currentSort,
            include: "options",
          },
        });
      } else {
        this.tablePolls.setMessage("Nu există rezultate pentru căutarea ta");
      }
    },
  },
};
</script>

