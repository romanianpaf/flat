<template>
  <div class="py-4 container-fluid">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="pb-0 card-header">
            <div class="d-lg-flex">
              <div>
                <h5 class="mb-0">Listă Membri</h5>
                <p class="text-sm text-muted mb-0">Membrii asociației tale</p>
              </div>
              <div class="my-auto mt-4 ms-auto mt-lg-0">
                <div class="my-auto ms-auto">
                  <router-link 
                    v-if="canCreate"
                    to="/members/new" 
                    class="mb-0 btn bg-gradient-primary btn-sm"
                  >+&nbsp; Membru Nou</router-link>
                </div>
              </div>
            </div>
          </div>
          <div class="px-0 pb-0 card-body">
            <div v-if="loadError" class="alert alert-warning mx-3">
              <i class="fas fa-exclamation-triangle me-2"></i>
              {{ loadError }}
            </div>
            <div class="table-responsive">
              <table id="members-list" ref="membersList" class="table table-flush">
                <thead class="thead-light">
                  <tr>
                    <th data-sortable="false">Poză</th>
                    <th title="name">Nume</th>
                    <th title="email">Email</th>
                    <th>Telefon</th>
                    <th>Apartament</th>
                    <th title="roles.name">Rol</th>
                    <th title="created_at">Înregistrat</th>
                    <th data-sortable="false">Acțiuni</th>
                  </tr>
                </thead>
                <tbody class="text-sm"></tbody>
                <tfoot>
                  <tr>
                    <th>Poză</th>
                    <th>Nume</th>
                    <th>Email</th>
                    <th>Telefon</th>
                    <th>Apartament</th>
                    <th>Rol</th>
                    <th>Înregistrat</th>
                    <th>Acțiuni</th>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>
          <div class="d-flex justify-content-center justify-content-sm-between flex-wrap">
            <div class="ms-3">
              <p>
                Afișare {{ pagination?.from || 0 }} - {{ pagination?.to || 0 }} din
                {{ pagination?.total || 0 }} membri
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
import { hasPermission } from "/src/utils/permissions.js";

var currentQuery = "";
var currentPerPage = 5;
var currentPage = 1;
var currentSort = "created_at";
import loadingImg from "@/assets/img/loading.gif";
var loading = loadingImg;

const getMembersList = _.debounce(async function (params) {
  await store.dispatch("users/getUsers", {
    ...(params.sort ? { sort: params.sort } : {}),
    filter: {
      ...(params.query ? { name: params.query } : {}),
    },
    page: {
      number: params.nr,
      size: params.perpage,
    },
    include: "roles",
  });
}, 300);

export default {
  name: "Members",
  components: {
    BasePagination,
  },
  mixins: [eventTable],
  data() {
    return {
      membersAux: [],
      pagination: {},
      tableMembers: null,
      loadError: null,
    };
  },
  computed: {
    membersList() {
      return this.$store.getters["users/users"]?.data;
    },
    metaPage() {
      return this.$store.getters["users/users"]?.meta;
    },
    canCreate() {
      return hasPermission('create members') || hasPermission('create users');
    },
    canEdit() {
      return hasPermission('edit members') || hasPermission('edit users');
    },
    canDelete() {
      return hasPermission('delete members') || hasPermission('delete users');
    },
  },
  watch: {
    metaPage: {
      handler: "reactivePagination",
      immediate: false,
      deep: true,
    },
    membersList: {
      handler: "reactiveTable",
      immediate: false,
      deep: true,
    },
  },

  async mounted() {
    if (this.$refs.membersList) {
      this.tableMembers = new DataTable(this.$refs.membersList, {
        fixedHeight: false,
        perPage: 5,
      });

      const bottom = document.querySelector(".dataTable-bottom");
      if (bottom) bottom.remove();
      this.tableMembers.label = null;
      this.tableMembers.setMessage(
        `<img src="${loading}" width="100" height="100" alt="loading" />`
      );

      try {
        await getMembersList({
          query: currentQuery,
          perpage: currentPerPage,
          nr: currentPage,
          sort: currentSort,
        });
      } catch (error) {
        if (error.response?.status === 403) {
          this.loadError = "Nu ai permisiunea de a vizualiza membrii.";
          this.tableMembers.setMessage("Nu ai permisiunea de a vizualiza membrii.");
        } else {
          this.loadError = "A apărut o eroare la încărcarea membrilor.";
          this.tableMembers.setMessage("Eroare la încărcare.");
        }
        return;
      }

      this.tableMembers.on("datatable.perpage", async function (perpage) {
        this.setMessage(
          `<img src="${loading}" width="100" height="100" alt="loading" />`
        );
        await getMembersList({
          query: currentQuery,
          perpage: (currentPerPage = perpage),
          nr: (currentPage = 1),
          sort: currentSort,
        });
      });

      this.tableMembers.on("datatable.sort", async function (column, direction) {
        this.setMessage(
          `<img src="${loading}" width="100" height="100" alt="loading" />`
        );
        direction = direction == "asc" ? "" : "-";
        column = this.headings[column].title;
        await getMembersList({
          query: currentQuery,
          perpage: currentPerPage,
          nr: currentPage,
          sort: (currentSort = direction + column),
        });
      });

      // eslint-disable-next-line no-unused-vars
      this.tableMembers.on("datatable.search", async function (query, matched) {
        this.setMessage(
          `<img src="${loading}" width="100" height="100" alt="loading" />`
        );
        await getMembersList({
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
      await getMembersList({
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
      const parts = name.trim().split(' ');
      if (parts.length >= 2) {
        return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
      }
      return name.substring(0, 2).toUpperCase();
    },
    
    getAvatarColor(name) {
      const colors = [
        '#e91e63', '#9c27b0', '#673ab7', '#3f51b5', '#2196f3',
        '#00bcd4', '#009688', '#4caf50', '#ff9800', '#ff5722',
      ];
      let hash = 0;
      for (let i = 0; i < name.length; i++) {
        hash = name.charCodeAt(i) + ((hash << 5) - hash);
      }
      return colors[Math.abs(hash) % colors.length];
    },
    
    async reactiveTable() {
      this.membersAux = [];
      if (this.membersList?.length > 0) {
        this.membersList.forEach((row) => {
          const fullName = [row.first_name, row.last_name].filter(Boolean).join(' ') || '-';
          
          // Avatar
          let profileImageHtml;
          if (!row.profile_image) {
            const initials = this.getInitials(fullName);
            const bgColor = this.getAvatarColor(fullName);
            profileImageHtml = `
              <div style="
                border-radius: 50%; 
                width: 45px; 
                height: 45px; 
                background: ${bgColor}; 
                display: flex; 
                align-items: center; 
                justify-content: center; 
                color: white; 
                font-weight: 600; 
                font-size: 16px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
              ">${initials}</div>
            `;
          } else {
            let profileImageUrl;
            if (row.profile_image.startsWith('http')) {
              profileImageUrl = row.profile_image;
            } else {
              profileImageUrl = `${import.meta.env.VITE_API_BASE_URL}/storage/${row.profile_image}`;
            }
            profileImageHtml = `<img src="${profileImageUrl}" alt="Profile" style="border-radius:50%; width:45px; height:45px; object-fit:cover; box-shadow: 0 2px 4px rgba(0,0,0,0.1);"/>`;
          }

          // Apartament info
          const apartmentInfo = row.apartment 
            ? `Ap. ${row.apartment}${row.staircase ? ` / Sc. ${row.staircase}` : ''}${row.floor ? ` / Et. ${row.floor}` : ''}`
            : '-';
          
          // Rol
          const roleName = row.roles?.[0]?.name || '-';
          
          // Acțiuni - afișează doar dacă utilizatorul are permisiuni
          let actions = '<span class="text-muted text-sm">-</span>';
          if (this.canEdit || this.canDelete) {
            actions = '';
            if (this.canEdit) {
              actions += this.actionEditButton(row.id, "Editează membru");
            }
            if (this.canDelete) {
              actions += this.actionDeleteButton(row.id, "Șterge membru");
            }
          }
          
          this.membersAux.push([
            profileImageHtml,
            `<h6 class="my-auto text-sm">${fullName}</h6>`,
            row.email || '-',
            row.phone || '-',
            apartmentInfo,
            `<span class="badge badge-sm bg-gradient-info">${roleName}</span>`,
            row.created_at,
            actions,
          ]);
        });
        this.tableMembers.data = [];
        this.tableMembers.refresh();
        document.querySelector(".dataTable-input").value = currentQuery;
        this.tableMembers.insert({ data: this.membersAux });
        this.removeEvent();
        
        if (this.canEdit || this.canDelete) {
          this.eventToCall({
            table: this.tableMembers,
            redirectPath: "Edit Member",
            deletePath: "users/deleteUser",
            getPath: "users/getUsers",
            textDelete: "Membru șters cu succes!",
            textDefaultData: "users",
            textDeleteError: "A apărut o eroare la ștergerea membrului.",
            params: {
              query: currentQuery,
              perpage: currentPerPage,
              nr: currentPage,
              sort: currentSort,
              include: "roles",
            },
          });
        }
      } else {
        this.tableMembers.setMessage("Nu există membri înregistrați");
      }
    },
  },
};
</script>
