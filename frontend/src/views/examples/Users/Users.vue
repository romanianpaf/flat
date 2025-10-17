<template>
  <div class="py-4 container-fluid">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="pb-0 card-header">
            <div class="d-lg-flex">
              <div>
                <h5 class="mb-0">Listă Utilizatori</h5>
              </div>
              <div class="my-auto mt-4 ms-auto mt-lg-0">
                <div class="my-auto ms-auto">
                  <router-link to="/users/new" class="mb-0 btn bg-gradient-primary btn-sm"
                    >+&nbsp; Utilizator Nou</router-link
                  >
                </div>
              </div>
            </div>
          </div>
          <div class="px-0 pb-0 card-body">
            <div class="table-responsive">
              <table id="users-list" ref="usersList" class="table table-flush">
                <thead class="thead-light">
                  <tr>
                    <th data-sortable="false">Poză</th>
                    <th title="name">Nume</th>
                    <th title="email">Email</th>
                    <th data-sortable="false">Beneficiar</th>
                    <th title="roles.name">Rol</th>
                    <th title="created_at">Creat la</th>
                    <th data-sortable="false">Acțiuni</th>
                  </tr>
                </thead>
                <tbody class="text-sm"></tbody>
                <tfoot>
                  <tr>
                    <th>Poză</th>
                    <th>Nume</th>
                    <th>Email</th>
                    <th>Beneficiar</th>
                    <th>Rol</th>
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

const getUsersList = _.debounce(async function (params) {
  await store.dispatch("users/getUsers", {
    ...(params.sort ? { sort: params.sort } : {}),
    filter: {
      ...(params.query ? { name: params.query } : {}),
    },
    page: {
      number: params.nr,
      size: params.perpage,
    },
    include: "tenant,roles",
  });
}, 300);

export default {
  name: "Users",
  components: {
    BasePagination,
  },
  mixins: [eventTable],
  data() {
    return {
      usersAux: [],
      pagination: {},
      tableUsers: null,
    };
  },
  computed: {
    usersList() {
      return this.$store.getters["users/users"]?.data;
    },
    metaPage() {
      return this.$store.getters["users/users"]?.meta;
    },
  },
  watch: {
    metaPage: {
      handler: "reactivePagination",
      immediate: false,
      deep: true,
    },
    usersList: {
      handler: "reactiveTable",
      immediate: false,
      deep: true,
    },
  },

  async mounted() {
    if (this.$refs.usersList) {
      this.tableUsers = new DataTable(this.$refs.usersList, {
        fixedHeight: false,
        perPage: 5,
      });

      const bottom = document.querySelector(".dataTable-bottom");
      if (bottom) bottom.remove();
      this.tableUsers.label = null;
      this.tableUsers.setMessage(
        `<img src="${loading}" width="100" height="100" alt="loading" />`
      );

      await getUsersList({
        query: currentQuery,
        perpage: currentPerPage,
        nr: currentPage,
        sort: currentSort,
      });

      this.tableUsers.on("datatable.perpage", async function (perpage) {
        this.setMessage(
          `<img src="${loading}" width="100" height="100" alt="loading" />`
        );
        await getUsersList({
          query: currentQuery,
          perpage: (currentPerPage = perpage),
          nr: (currentPage = 1),
          sort: currentSort,
        });
      });

      this.tableUsers.on("datatable.sort", async function (column, direction) {
        this.setMessage(
          `<img src="${loading}" width="100" height="100" alt="loading" />`
        );
        direction = direction == "asc" ? "" : "-";
        column = this.headings[column].title;
        await getUsersList({
          query: currentQuery,
          perpage: currentPerPage,
          nr: currentPage,
          sort: (currentSort = direction + column),
        });
      });

      // eslint-disable-next-line no-unused-vars
      this.tableUsers.on("datatable.search", async function (query, matched) {
        this.setMessage(
          `<img src="${loading}" width="100" height="100" alt="loading" />`
        );
        await getUsersList({
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
      await getUsersList({
        query: currentQuery,
        perpage: currentPerPage,
        nr: (currentPage = page),
        sort: currentSort,
      });
    },

    async reactivePagination() {
      this.pagination = this.metaPage?.page;
    },

    getInitials(name) {
      // Obține inițialele din nume (max 2 litere)
      const parts = name.trim().split(' ');
      if (parts.length >= 2) {
        return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
      }
      return name.substring(0, 2).toUpperCase();
    },
    
    getAvatarColor(name) {
      // Generează o culoare consistentă pe baza numelui
      const colors = [
        '#e91e63', // pink
        '#9c27b0', // purple
        '#673ab7', // deep purple
        '#3f51b5', // indigo
        '#2196f3', // blue
        '#00bcd4', // cyan
        '#009688', // teal
        '#4caf50', // green
        '#ff9800', // orange
        '#ff5722', // deep orange
      ];
      let hash = 0;
      for (let i = 0; i < name.length; i++) {
        hash = name.charCodeAt(i) + ((hash << 5) - hash);
      }
      return colors[Math.abs(hash) % colors.length];
    },
    
    async reactiveTable() {
      this.usersAux = [];
      if (this.usersList?.length > 0) {
        this.usersList.forEach((row) => {
          const tenantName = row.tenant?.name || '<span class="badge badge-sm bg-gradient-secondary">Global</span>';
          
          // Construiește imaginea de profil sau avatar cu inițiale
          let profileImageHtml;
          if (!row.profile_image) {
            // Generează avatar cu inițiale
            const initials = this.getInitials(row.name);
            const bgColor = this.getAvatarColor(row.name);
            profileImageHtml = `
              <div style="
                border-radius: 50%; 
                width: 55px; 
                height: 55px; 
                background: ${bgColor}; 
                display: flex; 
                align-items: center; 
                justify-content: center; 
                color: white; 
                font-weight: 600; 
                font-size: 20px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
              ">${initials}</div>
            `;
          } else {
            // Construiește URL-ul pentru imagine
            let profileImageUrl;
            if (row.profile_image.startsWith('http')) {
              profileImageUrl = row.profile_image;
            } else {
              profileImageUrl = `${process.env.VUE_APP_API_BASE_URL}/storage/${row.profile_image}`;
            }
            profileImageHtml = `<img src="${profileImageUrl}" alt="Profile" style="border-radius:50%; width:55px; height:55px; object-fit:cover; box-shadow: 0 2px 4px rgba(0,0,0,0.1);"/>`;
          }
          
          this.usersAux.push([
            profileImageHtml,
            `<h6 class="my-auto">${row.name}</h6>`,
            row.email,
            tenantName,
            row.roles[0].name,
            row.created_at,
            this.actionEditButton(row.id, "Editează utilizator") +
              this.actionDeleteButton(row.id, "Șterge utilizator"),
          ]);
        });
        this.tableUsers.data = [];
        this.tableUsers.refresh();
        document.querySelector(".dataTable-input").value = currentQuery;
        this.tableUsers.insert({ data: this.usersAux });
        this.removeEvent();
        this.eventToCall({
          table: this.tableUsers,
          redirectPath: "Edit User",
          deletePath: "users/deleteUser",
          getPath: "users/getUsers",
          textDelete: "Utilizator șters cu succes!",
          textDefaultData: "users",
          params: {
            query: currentQuery,
            perpage: currentPerPage,
            nr: currentPage,
            sort: currentSort,
            include: "tenant,roles",
          },
        });
      } else {
        this.tableUsers.setMessage("Nu există rezultate pentru căutarea ta");
      }
    },
  },
};
</script>
