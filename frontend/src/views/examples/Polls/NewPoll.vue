<template>
  <div class="py-4 container-fluid">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="pb-0 card-header">
            <div class="d-lg-flex">
              <div>
                <h5 class="mb-0">Adaugă sondaj nou</h5>
              </div>
              <div class="my-auto mt-4 ms-auto mt-lg-0">
                <div class="my-auto ms-auto">
                  <router-link to="/polls/list" class="mb-0 btn bg-gradient-primary btn-sm"
                    >&nbsp; Înapoi la listă</router-link
                  >
                </div>
              </div>
            </div>
          </div>

          <div class="card-body">
            <div class="row mt-4">
              <div class="col-md-8">
                <label class="form-label">Titlu sondaj</label>
                <soft-model-input
                  id="title"
                  v-model="poll.title"
                  type="text"
                  placeholder="Ex: Care este preferința ta pentru..."
                />
                <validation-error :errors="apiValidationErrors.title" />
              </div>
              <div class="col-md-4">
                <label class="form-label">Status</label>
                <div class="form-check form-switch">
                  <input
                    id="is_active"
                    v-model="poll.is_active"
                    class="form-check-input"
                    type="checkbox"
                  />
                  <label class="form-check-label" for="is_active">
                    {{ poll.is_active ? 'Activ' : 'Inactiv' }}
                  </label>
                </div>
              </div>
            </div>

            <div class="row mt-4">
              <div class="col-12">
                <label class="form-label">Descriere</label>
                <textarea
                  v-model="poll.description"
                  class="form-control"
                  rows="3"
                  placeholder="Descriere opțională pentru sondaj"
                ></textarea>
                <validation-error :errors="apiValidationErrors.description" />
              </div>
            </div>

            <div class="row mt-4">
              <div class="col-md-6">
                <label class="form-label">Data de start (opțional)</label>
                <soft-model-input
                  v-model="poll.start_date"
                  type="datetime-local"
                />
                <validation-error :errors="apiValidationErrors.start_date" />
              </div>
              <div class="col-md-6">
                <label class="form-label">Data de încheiere (opțional)</label>
                <soft-model-input
                  v-model="poll.end_date"
                  type="datetime-local"
                />
                <validation-error :errors="apiValidationErrors.end_date" />
              </div>
            </div>

            <div class="row mt-4">
              <div class="col-12">
                <div class="form-check">
                  <input
                    id="allow_multiple_votes"
                    v-model="poll.allow_multiple_votes"
                    class="form-check-input"
                    type="checkbox"
                  />
                  <label class="form-check-label" for="allow_multiple_votes">
                    Permite votare multiplă (utilizatorii pot selecta mai multe opțiuni)
                  </label>
                </div>
              </div>
            </div>

            <h6 class="mt-5 mb-3">📊 Opțiuni de vot</h6>
            <p class="text-sm text-muted mb-3">Adaugă minimum 2 opțiuni de răspuns:</p>

            <div
              v-for="(option, index) in poll.options"
              :key="index"
              class="row mt-3 align-items-center"
            >
              <div class="col-md-10">
                <label class="form-label">Opțiunea {{ index + 1 }}</label>
                <soft-model-input
                  v-model="option.option_text"
                  type="text"
                  :placeholder="'Ex: Opțiunea ' + (index + 1)"
                />
              </div>
              <div class="col-md-2 mt-4">
                <button
                  v-if="poll.options.length > 2"
                  class="btn btn-outline-danger btn-sm w-100"
                  type="button"
                  @click="removeOption(index)"
                >
                  <i class="fas fa-trash"></i> Șterge
                </button>
              </div>
            </div>

            <div class="row mt-3">
              <div class="col-12">
                <button
                  class="btn btn-outline-success btn-sm"
                  type="button"
                  @click="addOption"
                >
                  <i class="fas fa-plus"></i> Adaugă opțiune
                </button>
              </div>
            </div>

            <validation-error :errors="apiValidationErrors.options" />

            <soft-button
              color="dark"
              variant="gradient"
              class="float-end mt-4 mb-0"
              size="sm"
              :is-disabled="loading ? true : false"
              @click="addPoll"
              ><span v-if="loading" class="spinner-border spinner-border-sm"></span>
              <span v-else>Salvează</span></soft-button
            >
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import SoftModelInput from "@/components/SoftModelInput.vue";
import SoftButton from "@/components/SoftButton.vue";
import showSwal from "@/mixins/showSwal.js";
import formMixin from "@/mixins/form-mixin.js";
import ValidationError from "@/components/ValidationError.vue";

export default {
  name: "NewPoll",
  components: {
    SoftModelInput,
    SoftButton,
    ValidationError,
  },
  mixins: [formMixin],
  data() {
    return {
      poll: {
        title: '',
        description: '',
        is_active: true,
        allow_multiple_votes: false,
        start_date: null,
        end_date: null,
        options: [
          { option_text: '' },
          { option_text: '' },
        ],
      },
      loading: false,
    };
  },
  methods: {
    addOption() {
      this.poll.options.push({ option_text: '' });
    },
    removeOption(index) {
      if (this.poll.options.length > 2) {
        this.poll.options.splice(index, 1);
      }
    },
    async addPoll() {
      this.resetApiValidation();
      
      // Validare locală
      if (!this.poll.title) {
        showSwal.methods.showSwal({
          type: "error",
          message: "Titlul este obligatoriu!",
          width: 350,
        });
        return;
      }

      // Verifică că toate opțiunile au text
      const emptyOptions = this.poll.options.filter(opt => !opt.option_text.trim());
      if (emptyOptions.length > 0) {
        showSwal.methods.showSwal({
          type: "error",
          message: "Toate opțiunile trebuie completate!",
          width: 350,
        });
        return;
      }

      if (this.poll.options.length < 2) {
        showSwal.methods.showSwal({
          type: "error",
          message: "Adaugă minimum 2 opțiuni!",
          width: 350,
        });
        return;
      }

      this.loading = true;
      try {
        await this.$store.dispatch("polls/addPoll", this.poll);
        showSwal.methods.showSwal({
          type: "success",
          message: "Sondaj adăugat cu succes!",
          width: 350,
        });
        this.$router.push("/polls/list");
      } catch (error) {
        if (error.response?.data?.errors) this.setApiValidation(error.response.data.errors);
        else
          showSwal.methods.showSwal({
            type: "error",
            message: "A apărut o eroare!",
            width: 350,
          });
        this.loading = false;
      }
    },
  },
};
</script>

