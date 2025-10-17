<template>
  <div class="py-4 container-fluid">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h5 class="mb-0">Editează Sugestie</h5>
            <p class="mb-0 text-sm">Modifică textul sugestiei tale</p>
          </div>
          <div class="card-body">
            <div v-if="loadingData" class="text-center py-5">
              <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Se încarcă...</span>
              </div>
            </div>
            <form v-else @submit.prevent="submitForm">
              <div class="mb-3">
                <label for="suggestion" class="form-label">Sugestia ta *</label>
                <textarea
                  id="suggestion"
                  v-model="userVoice.suggestion"
                  class="form-control"
                  rows="5"
                  placeholder="Descrie sugestia ta aici... (minim 10 caractere)"
                  required
                  minlength="10"
                ></textarea>
                <small class="text-muted">
                  Caractere: {{ userVoice.suggestion?.length || 0 }}
                </small>
              </div>

              <div class="d-flex justify-content-end">
                <router-link
                  to="/user-voices/list"
                  class="btn btn-outline-secondary me-2"
                >
                  Anulează
                </router-link>
                <button
                  type="submit"
                  class="btn bg-gradient-primary"
                  :disabled="loading || (userVoice.suggestion?.length || 0) < 10"
                >
                  <span v-if="loading">
                    <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                    Se salvează...
                  </span>
                  <span v-else>Actualizează Sugestia</span>
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "EditUserVoice",
  data() {
    return {
      loading: false,
      loadingData: true,
      userVoice: {
        id: null,
        suggestion: "",
      },
    };
  },
  async mounted() {
    await this.loadUserVoice();
  },
  methods: {
    async loadUserVoice() {
      this.loadingData = true;
      try {
        const id = this.$route.params.id;
        const data = await this.$store.dispatch("uservoices/getUserVoice", id);
        this.userVoice = {
          id: data.id,
          suggestion: data.suggestion || "",
        };
      } catch (error) {
        console.error("Error loading user voice:", error);
        this.$swal({
          icon: "error",
          title: "Eroare",
          text: "Nu s-a putut încărca sugestia.",
        });
        this.$router.push("/user-voices/list");
      } finally {
        this.loadingData = false;
      }
    },
    async submitForm() {
      if ((this.userVoice.suggestion?.length || 0) < 10) {
        this.$swal({
          icon: "warning",
          title: "Atenție",
          text: "Sugestia trebuie să aibă cel puțin 10 caractere.",
        });
        return;
      }

      this.loading = true;
      try {
        await this.$store.dispatch("uservoices/editUserVoice", this.userVoice);
        this.$swal({
          icon: "success",
          title: "Succes",
          text: "Sugestia ta a fost actualizată cu succes!",
        });
        this.$router.push("/user-voices/list");
      } catch (error) {
        console.error("Error updating user voice:", error);
        const errorMessage = error.response?.data?.errors?.[0]?.detail || "Nu s-a putut actualiza sugestia.";
        this.$swal({
          icon: "error",
          title: "Eroare",
          text: errorMessage,
        });
      } finally {
        this.loading = false;
      }
    },
  },
};
</script>

