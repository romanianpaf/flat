<template>
  <div class="py-4 container-fluid">
    <div class="row">
      <div class="col-xl-7">
        <div class="card">
          <div class="p-3 pb-0 card-header d-flex">
            <h6 class="my-auto">Bariere</h6>
            <div class="nav-wrapper position-relative ms-auto w-50">
              <ul class="p-1 nav nav-pills nav-fill" role="tablist">
                <li class="nav-item">
                  <a
                    class="px-0 py-1 mb-0 nav-link active"
                    data-bs-toggle="tab"
                    href="#cam1"
                    role="tab"
                    aria-controls="cam1"
                    aria-selected="true"
                  >
                    Barieră Mol
                  </a>
                </li>
                <li class="nav-item">
                  <a
                    class="px-0 py-1 mb-0 nav-link"
                    data-bs-toggle="tab"
                    href="#cam2"
                    role="tab"
                    aria-controls="cam2"
                    aria-selected="false"
                  >
                    Barieră Atac
                  </a>
                </li>
              </ul>
            </div>
            <div class="pt-2 dropdown">
              <a
                id="dropdownCam"
                href="#"
                class="text-secondary ps-4"
                :class="{ show: showMenu }"
                data-bs-toggle="dropdown"
                aria-expanded="false"
                @click="showMenu = !showMenu"
              >
                <i class="fas fa-ellipsis-v"></i>
              </a>
              <ul
                class="px-2 py-3 dropdown-menu dropdown-menu-end me-sm-n4"
                :class="{ show: showMenu }"
                aria-labelledby="dropdownCam"
              >
                <li>
                  <a class="dropdown-item border-radius-md" href="#">Pause</a>
                </li>
                <li>
                  <a class="dropdown-item border-radius-md" href="#">Stop</a>
                </li>
                <li>
                  <a class="dropdown-item border-radius-md" href="#"
                    >Schedule</a
                  >
                </li>
                <li>
                  <hr class="dropdown-divider" />
                </li>
                <li>
                  <a class="dropdown-item border-radius-md text-danger" href="#"
                    >Remove</a
                  >
                </li>
              </ul>
            </div>
          </div>
          <div class="p-3 mt-2 card-body">
            <div id="v-pills-tabContent" class="tab-content">
              <div
                id="cam1"
                class="tab-pane fade show position-relative active height-400 border-radius-lg"
                role="tabpanel"
                aria-labelledby="cam1"
                :style="{
                  backgroundImage:
                    'url(' + require('@/assets/img/bg-smart-home-1.jpg') + ')',
                  backgroundSize: 'cover'
                }"
              >
                <div class="top-0 position-absolute d-flex w-100">
                  <p class="p-3 mb-0 text-white">17.05.2021 4:34PM</p>
                  <div class="p-3 ms-auto">
                    <span class="badge badge-secondary">
                      <i class="fas fa-dot-circle text-danger"> </i>
                      Recording</span
                    >
                  </div>
                </div>
              </div>
              <div
                id="cam2"
                class="tab-pane fade position-relative height-400 border-radius-lg"
                role="tabpanel"
                aria-labelledby="cam2"
                :style="{
                  backgroundImage:
                    'url(' + require('@/assets/img/bg-smart-home-2.jpg') + ')',
                  backgroundSize: 'cover'
                }"
              >
                <div class="top-0 position-absolute d-flex w-100">
                  <p class="p-3 mb-0 text-white">17.05.2021 4:35PM</p>
                  <div class="p-3 ms-auto">
                    <span class="badge badge-secondary">
                      <i class="fas fa-dot-circle text-danger"> </i>
                      Recording</span
                    >
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="mt-4 col-xl-5 ms-auto mt-xl-0">
        <div class="row">
          <div class="col-12">
            <div class="card bg-gradient-primary">
              <div class="p-3 card-body">
                <div class="row">
                  <div class="my-auto col-8">
                    <div class="numbers">
                      <p
                        class="mb-0 text-sm text-white text-capitalize font-weight-bold opacity-7"
                      >
                        Vremea acum
                      </p>
                      <h5 class="mb-0 text-white font-weight-bolder">
                        {{ weather.location }} - {{ weather.temperature }} °C
                      </h5>
                      <p class="mb-0 text-xs text-white opacity-8">
                        Resimțită: {{ weather.feelsLike }} °C
                      </p>
                    </div>
                  </div>
                  <div class="col-4 text-end">
                    <div style="font-size: 3rem; line-height: 1;">
                      {{ weather.icon }}
                    </div>
                    <h5 class="mb-0 text-white text-end me-1 text-capitalize">
                      {{ weather.description }}
                    </h5>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="mt-4 row">
          <div class="col-md-6">
            <default-counter-card
              :count="weather.temperature"
              suffix=" °C"
              title="Temperatură"
              description="Exterior"
            />
          </div>
          <div class="mt-4 col-md-6 mt-md-0">
            <default-counter-card
              :count="weather.humidity"
              suffix=" %"
              title="Umiditate"
              description="Exterior"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- Sondaje și User Voice -->
    <div class="mt-4 row">
      <!-- Widget Sondaj Recent -->
      <div class="col-lg-6">
        <div class="card h-100">
          <div class="pb-0 card-header">
            <h6 class="mb-0">Sondaj Recent</h6>
          </div>
          <div class="p-3 card-body">
            <div v-if="latestPoll">
              <h6 class="mb-3 font-weight-bold">{{ latestPoll.title }}</h6>
              <p class="mb-3 text-sm">{{ latestPoll.description }}</p>
              
              <div v-if="latestPoll.options && latestPoll.options.length > 0" class="mt-3">
                <div 
                  v-for="option in latestPoll.options" 
                  :key="option.id"
                  class="mb-3"
                >
                  <div class="d-flex align-items-center mb-2">
                    <div class="form-check" @click="voteOnPollOption(option)" style="cursor: pointer;">
                      <input 
                        class="form-check-input" 
                        :type="latestPoll.allow_multiple_votes ? 'checkbox' : 'radio'"
                        :name="'poll-' + latestPoll.id"
                        :id="'option-' + option.id"
                        :value="option.id"
                        v-model="selectedOptions"
                        @change="voteOnPollOption(option)"
                      >
                      <label 
                        class="form-check-label" 
                        :for="'option-' + option.id"
                        style="cursor: pointer;"
                      >
                        {{ option.option_text }}
                      </label>
                    </div>
                    <span class="ms-auto text-sm font-weight-bold text-secondary">
                      {{ option.votes_count || 0 }} voturi
                    </span>
                  </div>
                  <div class="progress" style="height: 8px;">
                    <div 
                      class="progress-bar bg-gradient-primary" 
                      role="progressbar" 
                      :style="{ width: getVotePercentage(option) + '%' }"
                      :aria-valuenow="getVotePercentage(option)"
                      aria-valuemin="0" 
                      aria-valuemax="100"
                    >
                    </div>
                  </div>
                </div>
              </div>
              
              <div class="mt-3 text-sm text-secondary">
                <i class="fas fa-calendar me-1"></i>
                Creat: {{ formatDate(latestPoll.created_at) }}
              </div>
            </div>
            <div v-else class="text-center py-5">
              <i class="fas fa-poll fa-3x text-secondary opacity-6 mb-3"></i>
              <p class="text-secondary">Nu există sondaje active</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Widget User Voice -->
      <div class="col-lg-6 mt-4 mt-lg-0">
        <div class="card h-100">
          <div class="pb-0 card-header">
            <h6 class="mb-0">Propuneri Recente</h6>
          </div>
          <div class="p-3 card-body">
            <div v-if="recentUserVoices.length > 0">
              <div 
                v-for="voice in recentUserVoices" 
                :key="voice.id"
                class="mb-3 pb-3 border-bottom"
              >
                <div class="d-flex align-items-start">
                  <div class="flex-grow-1">
                    <h6 class="mb-1 text-sm font-weight-bold">{{ voice.title || voice.suggestion }}</h6>
                    <p class="mb-2 text-xs text-secondary">
                      {{ truncate(voice.description || voice.suggestion, 100) }}
                    </p>
                    <div class="d-flex align-items-center">
                      <span class="badge badge-sm" :class="getStatusBadgeClass(voice.status)">
                        {{ getStatusText(voice.status) }}
                      </span>
                      <div class="ms-3 d-flex align-items-center gap-3">
                        <!-- Thumbs Up Button -->
                        <div class="d-flex flex-column align-items-center">
                          <button 
                            class="vote-button vote-button-up"
                            @click="voteOnUserVoice(voice, 'up')"
                            :title="'Votează PRO pentru: ' + (voice.title || voice.suggestion)"
                          >
                            <i class="fas fa-thumbs-up"></i>
                          </button>
                          <span class="vote-count text-success font-weight-bold">
                            {{ voice.votes_up || 0 }}
                          </span>
                        </div>
                        
                        <!-- Thumbs Down Button -->
                        <div class="d-flex flex-column align-items-center">
                          <button 
                            class="vote-button vote-button-down"
                            @click="voteOnUserVoice(voice, 'down')"
                            :title="'Votează CONTRA pentru: ' + (voice.title || voice.suggestion)"
                          >
                            <i class="fas fa-thumbs-down"></i>
                          </button>
                          <span class="vote-count text-danger font-weight-bold">
                            {{ voice.votes_down || 0 }}
                          </span>
                        </div>
                      </div>
                      <span class="ms-auto text-xs text-secondary">
                        <i class="fas fa-calendar me-1"></i>
                        {{ formatDate(voice.created_at) }}
                      </span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div v-else class="text-center py-5">
              <i class="fas fa-lightbulb fa-3x text-secondary opacity-6 mb-3"></i>
              <p class="text-secondary">Nu există propuneri</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import DefaultCounterCard from "@/examples/Cards/DefaultCounterCard.vue";

import setNavPills from "@/assets/js/nav-pills.js";
import setTooltip from "@/assets/js/tooltip.js";
import WeatherService from "@/services/weather.service.js";
import PollsService from "@/services/polls.service.js";
import UserVoicesService from "@/services/user-voices.service.js";

export default {
  name: "Home",
  components: {
    DefaultCounterCard,
  },
  data() {
    return {
      showMenu: false,
      weather: {
        location: "București, Chitilei",
        temperature: 22,
        feelsLike: 20,
        description: "Se încarcă...",
        icon: "⛅",
        humidity: 65,
        pressure: 1013,
        windSpeed: 3.5,
        cloudiness: 40,
      },
      latestPoll: null,
      recentUserVoices: [],
      selectedOptions: [], // Pentru multiple votes (checkbox)
    };
  },
  async mounted() {
    // Încarcă vremea
    await this.loadWeather();
    
    // Încarcă sondajul recent
    await this.loadLatestPoll();
    
    // Încarcă propuneri recente
    await this.loadRecentUserVoices();
    
    // Reîncarcă vremea la fiecare 10 minute
    this.weatherInterval = setInterval(() => {
      this.loadWeather();
    }, 10 * 60 * 1000);

    setNavPills();
    setTooltip(this.$store.state.bootstrap);
  },
  methods: {
    async loadWeather() {
      try {
        const weatherData = await WeatherService.getCurrentWeather();
        this.weather = weatherData;
      } catch (error) {
        console.error("Nu s-a putut încărca vremea:", error);
      }
    },
    async loadLatestPoll() {
      try {
        // Încarcă direct prin serviciu, sortate descrescător
        const response = await PollsService.getPolls({
          sort: '-created_at',
          page: { size: 1 },
          include: 'options'
        });
        
        if (response.data && response.data.length > 0) {
          this.latestPoll = response.data[0];
          console.log('✅ Sondaj încărcat:', this.latestPoll);
        } else {
          console.log('ℹ️ Nu există sondaje');
        }
      } catch (error) {
        console.error("❌ Eroare la încărcarea sondajului:", error);
      }
    },
    async loadRecentUserVoices() {
      try {
        // Încarcă direct prin serviciu, sortate descrescător
        const response = await UserVoicesService.getUserVoices({
          sort: '-created_at',
          page: { size: 3 }
        });
        
        if (response.data && response.data.length > 0) {
          this.recentUserVoices = response.data;
          console.log('✅ Propuneri încărcate:', this.recentUserVoices.length);
        } else {
          console.log('ℹ️ Nu există propuneri');
        }
      } catch (error) {
        console.error("❌ Eroare la încărcarea propunerilor:", error);
      }
    },
    getVotePercentage(option) {
      if (!this.latestPoll || !this.latestPoll.options) return 0;
      
      const totalVotes = this.latestPoll.options.reduce(
        (sum, opt) => sum + (opt.votes_count || 0), 
        0
      );
      
      if (totalVotes === 0) return 0;
      
      return Math.round(((option.votes_count || 0) / totalVotes) * 100);
    },
    formatDate(dateString) {
      if (!dateString) return '';
      const date = new Date(dateString);
      const now = new Date();
      const diffTime = Math.abs(now - date);
      const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));
      
      if (diffDays === 0) return 'Astăzi';
      if (diffDays === 1) return 'Ieri';
      if (diffDays < 7) return `${diffDays} zile`;
      if (diffDays < 30) return `${Math.floor(diffDays / 7)} săptămâni`;
      return date.toLocaleDateString('ro-RO');
    },
    truncate(text, length) {
      if (!text) return '';
      if (text.length <= length) return text;
      return text.substring(0, length) + '...';
    },
    getStatusText(status) {
      const statusMap = {
        'open': 'Deschis',
        'in_progress': 'În lucru',
        'completed': 'Finalizat',
        'rejected': 'Respins',
      };
      return statusMap[status] || status;
    },
    getStatusBadgeClass(status) {
      const classMap = {
        'open': 'bg-gradient-info',
        'in_progress': 'bg-gradient-warning',
        'completed': 'bg-gradient-success',
        'rejected': 'bg-gradient-danger',
      };
      return classMap[status] || 'bg-gradient-secondary';
    },
    async voteOnPollOption(option) {
      try {
        await PollsService.voteOnOption(option.id);
        
        this.$swal({
          icon: 'success',
          title: 'Votat!',
          text: `Ai votat pentru: "${option.option_text}"`,
          timer: 2000,
          heightAuto: false,
          backdrop: true,
        });
        
        // Reîncarcă sondajul pentru a actualiza numerele
        await this.loadLatestPoll();
      } catch (error) {
        console.error('Eroare la votare:', error);
        
        let errorMessage = 'Nu s-a putut înregistra votul. Te rugăm să încerci din nou.';
        if (error.response?.status === 409) {
          errorMessage = 'Ai votat deja la acest sondaj!';
        }
        
        this.$swal({
          icon: 'error',
          title: 'Eroare!',
          text: errorMessage,
          heightAuto: false,
          backdrop: true,
        });
      }
    },
    async voteOnUserVoice(voice, type) {
      try {
        await UserVoicesService.voteUserVoice(voice.id, type);
        
        const voteText = type === 'up' ? '👍 PRO' : '👎 CONTRA';
        const voiceTitle = voice.title || voice.suggestion;
        this.$swal({
          icon: 'success',
          title: 'Votat!',
          text: `Ai votat ${voteText} pentru: "${voiceTitle}"`,
          timer: 2000,
          heightAuto: false,
          backdrop: true,
        });
        
        // Reîncarcă propunerile pentru a actualiza numerele
        await this.loadRecentUserVoices();
      } catch (error) {
        console.error('Eroare la votare:', error);
        
        let errorMessage = 'Nu s-a putut înregistra votul. Te rugăm să încerci din nou.';
        if (error.response?.status === 409) {
          errorMessage = 'Ai votat deja pentru această propunere!';
        } else if (error.response?.status === 422) {
          errorMessage = 'Date invalide pentru votare.';
        }
        
        this.$swal({
          icon: 'error',
          title: 'Eroare!',
          text: errorMessage,
          heightAuto: false,
          backdrop: true,
        });
      }
    },
  },
  beforeUnmount() {
    // Curăță intervalul când componenta este distrusă
    if (this.weatherInterval) {
      clearInterval(this.weatherInterval);
    }
  }
};
</script>

<style scoped>
/* Vote Buttons - Stil similar cu pagina de referral */
.vote-button {
  width: 50px;
  height: 50px;
  border: none;
  border-radius: 16px; /* Rounded corners ca în imagine */
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  position: relative;
  overflow: hidden;
}

.vote-button::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.2) 0%, rgba(255, 255, 255, 0.05) 100%);
  border-radius: 16px;
  pointer-events: none;
}

.vote-button-up {
  background: linear-gradient(135deg, #28a745 0%, #20c997 100%); /* Verde gradient */
}

.vote-button-down {
  background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%); /* Roșu gradient */
}

.vote-button:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.25);
}

.vote-button:active {
  transform: translateY(0);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
}

.vote-button i {
  color: white;
  font-size: 20px;
  z-index: 1;
  position: relative;
}

.vote-count {
  margin-top: 8px;
  font-size: 12px;
  font-weight: 600;
  min-width: 20px;
  text-align: center;
}

/* Hover effects pentru butoane */
.vote-button-up:hover {
  background: linear-gradient(135deg, #218838 0%, #1ea085 100%);
}

.vote-button-down:hover {
  background: linear-gradient(135deg, #c82333 0%, #e55a00 100%);
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .vote-button {
    width: 45px;
    height: 45px;
  }
  
  .vote-button i {
    font-size: 18px;
  }
  
  .vote-count {
    font-size: 11px;
  }
}
</style>
