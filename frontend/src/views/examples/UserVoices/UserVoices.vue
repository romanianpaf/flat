<template>
  <div class="py-4 container-fluid">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="pb-0 card-header">
            <div class="d-lg-flex">
              <div>
                <h5 class="mb-0">Listă Sugestii</h5>
                <p class="mb-0 text-sm">Gestionează sugestiile utilizatorilor</p>
              </div>
              <div class="my-auto mt-4 ms-auto mt-lg-0">
                <div class="my-auto ms-auto">
                  <router-link
                    to="/user-voices/new"
                    class="mb-0 btn bg-gradient-primary btn-sm"
                    >+ Sugestie Nouă</router-link
                  >
                </div>
              </div>
            </div>
          </div>
          <div class="px-0 pb-0 card-body">
            <div class="table-responsive">
              <table ref="userVoicesList" class="table table-flush">
                <thead class="thead-light">
                  <tr>
                    <th title="suggestion">Sugestie</th>
                    <th data-sortable="false">Autor</th>
                    <th data-sortable="false">Voturi</th>
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

async function getUserVoicesList(params) {
  await this.$store.dispatch("uservoices/getUserVoices", {
    filter: { is_active: params.filter?.is_active },
    page: { size: params.perpage, number: params.nr },
    sort: params.sort,
    include: "user",
  });
}

const getUserVoicesListObj = {
  methods: { getUserVoicesList },
};

export default {
  name: "UserVoices",
  mixins: [setNavPills, getUserVoicesListObj, eventTable],
  data() {
    return {
      pagination: null,
      userVoicesAux: [],
    };
  },
  computed: {
    userVoicesList() {
      return this.$store.getters["uservoices/uservoices"]?.data;
    },
    metaPage() {
      return this.$store.getters["uservoices/uservoices"]?.meta;
    },
    currentUser() {
      return this.$store.getters["profile/profile"];
    },
  },
  watch: {
    metaPage: {
      handler: "reactivePagination",
      immediate: false,
      deep: true,
    },
    userVoicesList: {
      handler: "reactiveTable",
      immediate: false,
      deep: true,
    },
  },

  async mounted() {
    if (this.$refs.userVoicesList) {
      this.tableUserVoices = new DataTable(this.$refs.userVoicesList, {
        fixedHeight: false,
        perPage: 5,
      });

      const bottom = document.querySelector(".dataTable-bottom");
      if (bottom) bottom.remove();
      this.tableUserVoices.label = null;
      this.tableUserVoices.setMessage(
        `<img src="${loading}" width="100" height="100" alt="loading" />`
      );

      await this.getUserVoicesList({
        query: currentQuery,
        perpage: currentPerPage,
        nr: currentPage,
        sort: currentSort,
      });

      const self = this;
      this.tableUserVoices.on("datatable.perpage", async function (perpage) {
        this.setMessage(
          `<img src="${loading}" width="100" height="100" alt="loading" />`
        );
        await self.getUserVoicesList({
          query: currentQuery,
          perpage: (currentPerPage = perpage),
          nr: (currentPage = 1),
          sort: currentSort,
        });
      });

      this.tableUserVoices.on("datatable.sort", async function (column, direction) {
        this.setMessage(
          `<img src="${loading}" width="100" height="100" alt="loading" />`
        );
        direction = direction == "asc" ? "" : "-";
        column = this.headings[column].title;
        await self.getUserVoicesList({
          query: currentQuery,
          perpage: currentPerPage,
          nr: currentPage,
          sort: (currentSort = direction + column),
        });
      });

      // eslint-disable-next-line no-unused-vars
      this.tableUserVoices.on("datatable.search", async function (query) {
        this.setMessage(
          `<img src="${loading}" width="100" height="100" alt="loading" />`
        );
        await self.getUserVoicesList({
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
      await this.getUserVoicesList({
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
      this.userVoicesAux = [];
      if (this.userVoicesList?.length > 0) {
        this.userVoicesList.forEach((row) => {
          const authorName = row.user?.name || 'Utilizator necunoscut';
          const votesDisplay = `
            <div class="d-flex align-items-center gap-2">
              <span class="badge badge-sm bg-gradient-primary">
                <i class="fas fa-thumbs-up me-1"></i>${row.votes_up || 0}
              </span>
              <span class="badge badge-sm bg-gradient-danger">
                <i class="fas fa-thumbs-down me-1"></i>${row.votes_down || 0}
              </span>
            </div>
          `;
          
          this.userVoicesAux.push([
            `<div class="text-wrap" style="max-width: 300px;">${row.suggestion}</div>`,
            authorName,
            votesDisplay,
            row.created_at,
            this.actionEditButton(row.id, "Editează sugestie") +
              this.actionDeleteButton(row.id, "Șterge sugestie"),
          ]);
        });
        this.tableUserVoices.data = [];
        this.tableUserVoices.refresh();
        document.querySelector(".dataTable-input").value = currentQuery;
        this.tableUserVoices.insert({ data: this.userVoicesAux });
        this.removeEvent();
        this.eventToCall({
          table: this.tableUserVoices,
          redirectPath: "Edit User Voice",
          deletePath: "uservoices/deleteUserVoice",
          getPath: "uservoices/getUserVoices",
          textDelete: "Sugestie ștearsă cu succes!",
          textDefaultData: "uservoices",
          textDeleteError: "A apărut o eroare la ștergerea sugestiei.",
          params: {
            query: currentQuery,
            perpage: currentPerPage,
            nr: currentPage,
            sort: currentSort,
            include: "user",
          },
        });
      } else {
        this.tableUserVoices.setMessage("Nu există rezultate pentru căutarea ta");
      }
    },
  },
};
</script>

