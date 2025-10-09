<template>
  <div class="py-4 container-fluid">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h5 class="mb-0">Sugestie Nouă</h5>
            <p class="mb-0 text-sm">Propune o îmbunătățire pentru aplicație</p>
          </div>
          <div class="card-body">
            <form @submit.prevent="submitForm">
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
                  Caractere: {{ userVoice.suggestion.length }}
                </small>
              </div>

              <div class="d-flex justify-content-end">
                <router-link
                  to="/examples/user-voices/list"
                  class="btn btn-outline-secondary me-2"
                >
                  Anulează
                </router-link>
                <button
                  type="submit"
                  class="btn bg-gradient-success"
                  :disabled="loading || userVoice.suggestion.length < 10"
                >
                  <span v-if="loading">
                    <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                    Se salvează...
                  </span>
                  <span v-else>Salvează Sugestia</span>
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
  name: "NewUserVoice",
  data() {
    return {
      loading: false,
      userVoice: {
        suggestion: "",
      },
    };
  },
  methods: {
    async submitForm() {
      if (this.userVoice.suggestion.length < 10) {
        this.$swal({
          icon: "warning",
          title: "Atenție",
          text: "Sugestia trebuie să aibă cel puțin 10 caractere.",
        });
        return;
      }

      this.loading = true;
      try {
        await this.$store.dispatch("uservoices/addUserVoice", this.userVoice);
        this.$swal({
          icon: "success",
          title: "Succes",
          text: "Sugestia ta a fost salvată cu succes!",
        });
        this.$router.push("/examples/user-voices/list");
      } catch (error) {
        console.error("Error saving user voice:", error);
        const errorMessage = error.response?.data?.errors?.[0]?.detail || "Nu s-a putut salva sugestia.";
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

