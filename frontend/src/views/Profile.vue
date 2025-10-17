<template>
  <div class="container-fluid">
    <div
      class="mt-4 page-header min-height-300 border-radius-xl"
      :style="{
        backgroundImage:
          'url(' + require('@/assets/img/curved-images/curved14.jpg') + ')',
        backgroundPositionY: '50%'
      }"
    >
      <span class="mask bg-gradient-primary opacity-6"></span>
    </div>
    <div class="mx-4 overflow-hidden card card-body blur shadow-blur mt-n6">
      <div class="row gx-4">
        <div class="col-auto">
          <div class="avatar avatar-xl position-relative">
            <img
              :src="profileImage"
              alt="profile_image"
              class="shadow-sm w-100 border-radius-lg"
            />
            <label for="profile-upload" class="position-absolute bottom-0 end-0 mb-n2 me-n2">
              <span class="btn btn-sm btn-icon-only bg-gradient-primary cursor-pointer">
                <i class="fas fa-camera"></i>
              </span>
            </label>
            <input
              id="profile-upload"
              type="file"
              accept="image/*"
              class="d-none"
              @change="handleImageUpload"
            />
          </div>
        </div>
        <div class="col-auto my-auto">
          <div class="h-100">
            <h5 class="mb-1">{{ profile.name }}</h5>
            <p class="mb-0 text-sm font-weight-bold">{{ profile.email }}</p>
            <p v-if="profile.tenant" class="mb-0 text-sm">{{ profile.tenant.name }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="py-4 container-fluid">
    <div class="row">
      <div class="col-12 col-md-6">
        <div class="card h-100">
          <div class="p-3 pb-0 card-header">
            <h6 class="mb-0">Informații Profil</h6>
          </div>
          <div class="p-3 card-body">
            <form @submit.prevent="updateProfile">
              <div class="row">
                <div class="col-12">
                  <label class="form-label">Nume</label>
                  <soft-input
                    v-model="form.name"
                    type="text"
                    placeholder="Nume complet"
                  />
                </div>
              </div>
              <div class="row mt-3">
                <div class="col-12">
                  <label class="form-label">Email</label>
                  <soft-input
                    v-model="form.email"
                    type="email"
                    placeholder="email@example.com"
                  />
                </div>
              </div>
              <div class="row mt-3">
                <div class="col-12">
                  <label class="form-label">Telefon</label>
                  <soft-input
                    v-model="form.phone"
                    type="text"
                    placeholder="+40 XXX XXX XXX"
                    @input="formatPhone"
                  />
                </div>
              </div>
              <div class="row mt-3">
                <div class="col-12 col-md-4">
                  <label class="form-label">Apartament</label>
                  <soft-input
                    v-model="form.apartment"
                    type="text"
                    placeholder="ex: 12"
                  />
                </div>
                <div class="col-12 col-md-4 mt-3 mt-md-0">
                  <label class="form-label">Scara</label>
                  <soft-input
                    v-model="form.staircase"
                    type="text"
                    placeholder="ex: A"
                  />
                </div>
                <div class="col-12 col-md-4 mt-3 mt-md-0">
                  <label class="form-label">Etaj</label>
                  <soft-input
                    v-model="form.floor"
                    type="text"
                    placeholder="ex: 3"
                  />
                </div>
              </div>
              <div class="mt-4">
                <soft-button
                  type="submit"
                  color="primary"
                  variant="gradient"
                  size="sm"
                  :is-disabled="loading"
                >
                  <span v-if="loading" class="spinner-border spinner-border-sm"></span>
                  <span v-else>Salvează Modificările</span>
                </soft-button>
              </div>
            </form>
          </div>
        </div>
      </div>
      <div class="col-12 col-md-6 mt-4 mt-md-0">
        <div class="card h-100">
          <div class="p-3 pb-0 card-header">
            <h6 class="mb-0">Schimbă Parola</h6>
          </div>
          <div class="p-3 card-body">
            <form @submit.prevent="updatePassword">
              <label class="form-label">Parola Curentă</label>
              <soft-input
                v-model="passwordForm.current_password"
                type="password"
                placeholder="Parola curentă"
              />
              <label class="form-label mt-3">Parola Nouă</label>
              <soft-input
                v-model="passwordForm.new_password"
                type="password"
                placeholder="Parola nouă"
              />
              <label class="form-label mt-3">Confirmă Parola Nouă</label>
              <soft-input
                v-model="passwordForm.new_password_confirmation"
                type="password"
                placeholder="Confirmă parola nouă"
              />
              <h6 class="mt-4 text-sm">Cerințe parolă:</h6>
              <ul class="text-muted ps-4 mb-0 text-sm">
                <li>Minim 8 caractere</li>
                <li>Cel puțin o literă mare</li>
                <li>Cel puțin un număr</li>
                <li>Cel puțin un caracter special</li>
              </ul>
              <soft-button
                type="submit"
                color="primary"
                variant="gradient"
                size="sm"
                class="mt-4"
                :is-disabled="loadingPassword"
              >
                <span v-if="loadingPassword" class="spinner-border spinner-border-sm"></span>
                <span v-else>Actualizează Parola</span>
              </soft-button>
            </form>
          </div>
        </div>
      </div>
    </div>
    <div class="row mt-4">
      <div class="col-12 col-md-6">
        <div class="card h-100">
          <div class="p-3 pb-0 card-header">
            <h6 class="mb-0">Roluri și Permisiuni</h6>
          </div>
          <div class="p-3 card-body">
            <div v-if="profile.roles && profile.roles.length" class="d-flex flex-wrap">
              <soft-badge
                v-for="role in profile.roles"
                :key="role.id"
                color="primary"
                class="me-2 mb-2"
              >
                {{ role.name }}
              </soft-badge>
            </div>
            <p v-else class="text-sm text-muted">Nu ai roluri atribuite.</p>
          </div>
        </div>
      </div>
      <div class="col-12 col-md-6 mt-4 mt-md-0">
        <div class="card h-100">
          <div class="p-3 pb-0 card-header">
            <h6 class="mb-0">Beneficiar</h6>
          </div>
          <div class="p-3 card-body">
            <div v-if="profile.tenant">
              <h5 class="mb-1">{{ profile.tenant.name }}</h5>
              <p class="text-sm text-muted mb-2">{{ profile.tenant.address }}</p>
              <p v-if="profile.tenant.fiscal_code" class="text-sm mb-0">
                <strong>CUI:</strong> {{ profile.tenant.fiscal_code }}
              </p>
            </div>
            <p v-else class="text-sm text-muted">Nu ești asociat cu niciun beneficiar.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import SoftInput from "@/components/SoftInput.vue";
import SoftButton from "@/components/SoftButton.vue";
import SoftBadge from "@/components/SoftBadge.vue";
import showSwal from "/src/mixins/showSwal.js";
import { mapGetters } from "vuex";
import axios from "axios";
import authHeader from "@/services/auth-header";

const API_URL = process.env.VUE_APP_API_BASE_URL + "/";

export default {
  name: "Profile",
  components: {
    SoftInput,
    SoftButton,
    SoftBadge,
  },
  data() {
    return {
      loading: false,
      loadingPassword: false,
      form: {
        name: "",
        email: "",
        phone: "",
        apartment: "",
        staircase: "",
        floor: "",
      },
      passwordForm: {
        current_password: "",
        new_password: "",
        new_password_confirmation: "",
      },
    };
  },
  computed: {
    ...mapGetters({
      profile: "profile/profile",
    }),
    profileImage() {
      return this.profile?.profile_image || require("@/assets/img/bruce-mars.jpg");
    },
  },
  mounted() {
    this.$store.state.isAbsolute = true;
    this.loadProfile();
  },
  beforeUnmount() {
    this.$store.state.isAbsolute = false;
  },
  methods: {
    async loadProfile() {
      try {
        await this.$store.dispatch("profile/getProfile");
        // Așteaptă următorul tick pentru ca store-ul să fie actualizat
        await this.$nextTick();
        this.form.name = this.profile.name || "";
        this.form.email = this.profile.email || "";
        this.form.phone = this.profile.phone || "";
        this.form.apartment = this.profile.apartment || "";
        this.form.staircase = this.profile.staircase || "";
        this.form.floor = this.profile.floor || "";
      } catch (error) {
        console.error("Error loading profile:", error);
      }
    },
    async updateProfile() {
      this.loading = true;
      try {
        const payload = {
          data: {
            type: "users",
            id: this.profile.id,
            attributes: {
              name: this.form.name,
              email: this.form.email,
              phone: this.form.phone,
              apartment: this.form.apartment,
              staircase: this.form.staircase,
              floor: this.form.floor,
            },
          },
        };
        console.log("Updating profile with:", payload);
        
        await axios.patch(
          `${API_URL}users/${this.profile.id}`,
          payload,
          { headers: authHeader() }
        );
        
        // Update local storage
        const user = JSON.parse(localStorage.getItem("user"));
        user.name = this.form.name;
        user.email = this.form.email;
        localStorage.setItem("user", JSON.stringify(user));
        
        await this.$store.dispatch("profile/getProfile");
        
        showSwal.methods.showSwal({
          type: "success",
          message: "Profilul a fost actualizat cu succes!",
        });
      } catch (error) {
        console.error("Error updating profile:", error);
        showSwal.methods.showSwal({
          type: "error",
          message: "Eroare la actualizarea profilului!",
        });
      } finally {
        this.loading = false;
      }
    },
    async updatePassword() {
      if (this.passwordForm.new_password !== this.passwordForm.new_password_confirmation) {
        showSwal.methods.showSwal({
          type: "error",
          message: "Parolele nu coincid!",
        });
        return;
      }
      
      this.loadingPassword = true;
      try {
        await axios.post(
          `${API_URL}change-password`,
          {
            current_password: this.passwordForm.current_password,
            new_password: this.passwordForm.new_password,
            new_password_confirmation: this.passwordForm.new_password_confirmation,
          },
          { headers: authHeader() }
        );
        
        this.passwordForm = {
          current_password: "",
          new_password: "",
          new_password_confirmation: "",
        };
        
        showSwal.methods.showSwal({
          type: "success",
          message: "Parola a fost actualizată cu succes! Vei fi delogat pentru a te loga cu noua parolă.",
        });
        
        // Așteaptă 2 secunde și apoi face logout automat
        setTimeout(async () => {
          await this.$store.dispatch("auth/logout");
          this.$router.push({ name: "Login" });
        }, 2000);
      } catch (error) {
        console.error("Error updating password:", error);
        showSwal.methods.showSwal({
          type: "error",
          message: error.response?.data?.message || "Eroare la actualizarea parolei!",
        });
      } finally {
        this.loadingPassword = false;
      }
    },
    formatPhone(event) {
      // Elimină tot ce nu e cifră
      let phone = event.target.value.replace(/\D/g, '');
      
      // Dacă începe cu 40, păstrează-l
      // Altfel, dacă începe cu 0, înlocuiește cu 40
      if (phone.startsWith('0')) {
        phone = '40' + phone.substring(1);
      }
      
      // Formatează: +40 XXX XXX XXX
      let formatted = '';
      if (phone.length > 0) {
        formatted = '+';
        if (phone.length <= 2) {
          formatted += phone;
        } else if (phone.length <= 5) {
          formatted += phone.substring(0, 2) + ' ' + phone.substring(2);
        } else if (phone.length <= 8) {
          formatted += phone.substring(0, 2) + ' ' + phone.substring(2, 5) + ' ' + phone.substring(5);
        } else {
          formatted += phone.substring(0, 2) + ' ' + phone.substring(2, 5) + ' ' + phone.substring(5, 8) + ' ' + phone.substring(8, 11);
        }
      }
      
      this.form.phone = formatted;
    },
    async handleImageUpload(event) {
      const file = event.target.files[0];
      if (!file) return;
      
      // Validate file type
      if (!file.type.startsWith("image/")) {
        showSwal.methods.showSwal({
          type: "error",
          message: "Te rog selectează un fișier imagine valid!",
        });
        return;
      }
      
      // Validate file size (max 2MB)
      if (file.size > 2 * 1024 * 1024) {
        showSwal.methods.showSwal({
          type: "error",
          message: "Imaginea este prea mare! Dimensiunea maximă este 2MB.",
        });
        return;
      }
      
      const formData = new FormData();
      formData.append("attachment", file);
      
      try {
        // Get token for upload (don't use authHeader as it sets JSON API headers)
        const token = localStorage.getItem("token");
        const baseUrl = process.env.VUE_APP_API_BASE_URL;
        
        const uploadResponse = await axios.post(
          `${baseUrl}/uploads/users/${this.profile.id}/profile-image`,
          formData,
          {
            headers: {
              Authorization: `Bearer ${token}`,
              // Content-Type will be set automatically by axios for FormData
            },
          }
        );
        
        // Update user profile with new image URL
        if (uploadResponse.data.url) {
          await axios.patch(
            `${API_URL}users/${this.profile.id}`,
            {
              data: {
                type: "users",
                id: this.profile.id,
                attributes: {
                  profile_image: uploadResponse.data.url,
                },
              },
            },
            { headers: authHeader() }
          );
        }
        
        await this.$store.dispatch("profile/getProfile");
        
        showSwal.methods.showSwal({
          type: "success",
          message: "Poza de profil a fost actualizată cu succes!",
        });
      } catch (error) {
        console.error("Error uploading image:", error);
        showSwal.methods.showSwal({
          type: "error",
          message: error.response?.data?.detail || "Eroare la încărcarea imaginii!",
        });
      }
    },
  },
};
</script>

