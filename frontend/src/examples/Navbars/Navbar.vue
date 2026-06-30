<template>
  <nav
    id="navbarBlur"
    class="shadow-none navbar navbar-main navbar-expand-lg border-radius-xl"
    :class="[isRTL ? 'top-1 position-sticky z-index-sticky' : '']"
    v-bind="$attrs"
    data-scroll="true"
  >
    <div class="px-3 py-1 container-fluid">
      <breadcrumbs
        :current-directory="currentDirectory"
        :current-page="currentRouteName"
        :text-white="textWhite"
      />
      <div
        class="sidenav-toggler sidenav-toggler-inner d-xl-block d-none"
        :class="isRTL ? 'me-3' : ''"
      >
        <a href="#" class="p-0 nav-link text-body" @click.prevent="minNav">
          <div class="sidenav-toggler-inner">
            <i
              class="sidenav-toggler-line"
              :class="textWhite ? 'bg-white' : ''"
            ></i>
            <i
              class="sidenav-toggler-line"
              :class="textWhite ? 'bg-white' : ''"
            ></i>
            <i
              class="sidenav-toggler-line"
              :class="textWhite ? 'bg-white' : ''"
            ></i>
          </div>
        </a>
      </div>
      <div
        id="navbar"
        class="mt-2 collapse navbar-collapse mt-sm-0 me-md-0 me-sm-4"
        :class="isRTL ? 'px-0' : 'me-sm-4'"
      >
        <div
          class="pe-md-3 d-flex align-items-center"
          :class="isRTL ? 'me-md-auto' : 'ms-md-auto'"
        >
          <div class="input-group">
            <span class="input-group-text text-body"
              ><i class="fas fa-search" aria-hidden="true"></i
            ></span>
            <input
              type="text"
              class="form-control"
              :placeholder="isRTL ? 'أكتب هنا...' : 'Caută aici...'"
            />
          </div>
        </div>
        <ul class="navbar-nav justify-content-end">
          <li class="nav-item d-flex align-items-center">
            <router-link
              v-if="!loggedIn"
              :to="{ name: 'Login' }"
              class="px-0 nav-link font-weight-bold"
              :class="textWhite ? textWhite : 'text-body'"
            >
              <i class="fa fa-user" :class="isRTL ? 'ms-sm-2' : 'me-sm-1'"></i>
              <span v-if="isRTL" class="d-sm-inline d-none">يسجل دخول</span>
              <span v-else class="d-sm-inline d-none">Sign In </span>
            </router-link>
          </li>
          <li
            v-if="loggedIn"
            class="nav-item dropdown d-flex align-items-center"
            :class="isRTL ? 'ps-2' : 'pe-2'"
          >
            <a
              id="dropdownMenuProfile"
              href="#"
              class="p-0 nav-link d-flex align-items-center"
              :class="[
                textWhite ? textWhite : 'text-body',
                showProfileMenu ? 'show' : '',
              ]"
              data-bs-toggle="dropdown"
              aria-expanded="false"
              @click="showProfileMenu = !showProfileMenu"
            >
              <img
                :src="profileImage"
                class="avatar avatar-sm rounded-circle"
                alt="profil"
              />
              <span class="d-sm-inline d-none ms-2 font-weight-bold">{{ profileName }} <b class="caret"></b></span>
            </a>
            <ul
              class="px-2 py-3 dropdown-menu dropdown-menu-end me-sm-n4"
              :class="showProfileMenu ? 'show' : ''"
              aria-labelledby="dropdownMenuProfile"
            >
              <li>
                <router-link
                  :to="{ name: 'Profilul Utilizatorului' }"
                  class="dropdown-item border-radius-md"
                  @click="showProfileMenu = false"
                >
                  <div class="py-1 d-flex align-items-center">
                    <i class="fa fa-user me-2 text-secondary"></i>
                    <span>Profil</span>
                  </div>
                </router-link>
              </li>
              <li>
                <hr class="dropdown-divider" />
              </li>
              <li>
                <a
                  class="dropdown-item border-radius-md cursor-pointer"
                  @click="logoutUser"
                >
                  <div class="py-1 d-flex align-items-center">
                    <i class="fa fa-sign-out-alt me-2 text-secondary"></i>
                    <span>Deautentificare</span>
                  </div>
                </a>
              </li>
            </ul>
          </li>
          <li class="nav-item d-xl-none ps-3 d-flex align-items-center">
            <a
              id="iconNavbarSidenav"
              href="#"
              class="p-0 nav-link text-body"
              @click="toggleSidebar"
            >
              <div class="sidenav-toggler-inner">
                <i class="sidenav-toggler-line"></i>
                <i class="sidenav-toggler-line"></i>
                <i class="sidenav-toggler-line"></i>
              </div>
            </a>
          </li>
          <li
            class="nav-item dropdown d-flex align-items-center"
            :class="isRTL ? 'ps-2' : 'pe-2'"
          >
            <a
              id="dropdownMenuButton"
              href="#"
              class="p-0 nav-link position-relative"
              :class="[
                textWhite ? textWhite : 'text-body',
                showMenu ? 'show' : '',
              ]"
              data-bs-toggle="dropdown"
              aria-expanded="false"
              @click="onBellClick"
            >
              <i class="cursor-pointer fa fa-bell"></i>
              <span
                v-if="unreadCount > 0"
                class="badge badge-sm bg-gradient-danger position-absolute"
                style="top: -8px; right: -10px"
                >{{ unreadCount > 9 ? "9+" : unreadCount }}</span
              >
            </a>
            <ul
              class="px-2 py-3 dropdown-menu dropdown-menu-end me-sm-n4"
              :class="showMenu ? 'show' : ''"
              aria-labelledby="dropdownMenuButton"
              style="min-width: 320px; max-height: 420px; overflow-y: auto"
            >
              <li class="d-flex justify-content-between align-items-center px-2 mb-2">
                <span class="text-sm font-weight-bold">Notificări</span>
                <a
                  v-if="unreadCount > 0"
                  href="javascript:;"
                  class="text-xs text-primary"
                  @click.stop="markAllNotificationsRead"
                  >Marchează citite</a
                >
              </li>
              <li v-if="notifications.length === 0" class="text-center text-muted text-sm py-3">
                Nu ai notificări.
              </li>
              <li v-for="n in notifications" :key="n.id" class="mb-1">
                <a
                  class="dropdown-item border-radius-md"
                  :class="{ 'bg-light': !n.read_at }"
                  href="javascript:;"
                  @click="onNotificationClick(n)"
                >
                  <div class="py-1">
                    <h6 class="mb-1 text-sm font-weight-normal">
                      <span class="font-weight-bold">{{ n.data.title || "Notificare" }}</span>
                    </h6>
                    <p class="mb-1 text-xs">{{ n.data.message }}</p>
                    <p class="mb-0 text-xs text-secondary">
                      <i class="fa fa-clock me-1"></i>{{ n.created_at }}
                    </p>
                  </div>
                </a>
              </li>
            </ul>
          </li>
        </ul>
      </div>
    </div>
  </nav>
</template>
<script>
import Breadcrumbs from "../Breadcrumbs.vue";
import { mapMutations, mapActions, mapState, mapGetters } from "vuex";
import defaultAvatarImg from "@/assets/img/bruce-mars.jpg";
import notificationService from "@/services/notification.service.js";

export default {
  name: "Navbar",
  components: {
    Breadcrumbs,
  },
  props: {
    minNav: {
      type: Function,
      default: () => {},
    },
    textWhite: {
      type: String,
      default: "",
    },
  },
  data() {
    return {
      showMenu: false,
      showProfileMenu: false,
      notifications: [],
      unreadCount: 0,
    };
  },
  computed: {
    ...mapState(["isRTL"]),
    ...mapGetters({
      profile: "profile/profile",
    }),
    currentRouteName() {
      return this.$route.name;
    },
    currentDirectory() {
      let dir = this.$route.path.split("/")[1];
      return dir.charAt(0).toUpperCase() + dir.slice(1);
    },
    loggedIn() {
      return this.$store.getters["auth/loggedIn"];
    },
    profileImage() {
      return this.profile?.profile_image || defaultAvatarImg;
    },
    profileName() {
      if (this.profile?.first_name || this.profile?.last_name) {
        return [this.profile.first_name, this.profile.last_name].filter(Boolean).join(" ");
      }
      return this.profile?.email?.split("@")[0] || "";
    },
  },

  created() {
    this.minNav;
    if (this.loggedIn) {
      this.$store.dispatch("profile/getProfile");
      this.loadNotifications();
    }
  },
  methods: {
    ...mapMutations(["navbarMinimize", "toggleConfigurator"]),
    ...mapActions(["toggleSidebarColor"]),

    async loadNotifications() {
      try {
        const res = await notificationService.list();
        this.notifications = res?.data?.notifications || [];
        this.unreadCount = res?.data?.unread_count || 0;
      } catch (e) {
        // notificările sunt opționale; nu blocăm navbar-ul
      }
    },
    onBellClick() {
      this.showMenu = !this.showMenu;
      if (this.showMenu) this.loadNotifications();
    },
    async onNotificationClick(n) {
      try {
        if (!n.read_at) await notificationService.markRead(n.id);
      } catch (e) {
        // ignore
      }
      this.showMenu = false;
      await this.loadNotifications();
      const url = n?.data?.url;
      if (url && this.$route.path !== url) {
        this.$router.push(url);
      }
    },
    async markAllNotificationsRead() {
      try {
        await notificationService.markAllRead();
      } catch (e) {
        // ignore
      }
      await this.loadNotifications();
    },

    toggleSidebar() {
      this.toggleSidebarColor("bg-white");
      this.navbarMinimize();
    },

    async logoutUser() {
      this.showProfileMenu = false;
      try {
        await this.$store.dispatch("auth/logout");
      } finally {
        this.$router.push("/login");
      }
    },
  },
};
</script>
<style>
a:hover {
  cursor: pointer;
}
</style>
