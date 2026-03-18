<template>
  <section class="buy-tickets-view container py-4">
    <div class="match-calendar full-page-calendar">
      <div class="calendar-toolbar mb-3">
        <div class="calendar-title-wrap">
          <h1 class="m-0">Buy Tickets</h1>
          <span class="selected-month">{{ selectedMonthLabel }}</span>
        </div>
        <div class="calendar-picker-wrap">
          <button class="calendar-pick-btn" type="button" @click="openCalendarPicker">
            <i class="bi bi-calendar2-week me-2"></i>
            Choose Week
          </button>
          <input
            ref="weekPicker"
            type="week"
            class="day-picker-input"
            :value="selectedWeek"
            @change="onWeekChange"
          />
        </div>
      </div>

      <div class="week-nav mb-4">
        <button type="button" class="today-btn" @click="goToToday">
          Today
        </button>

        <button type="button" class="week-arrow-btn" @click="goToPreviousWeek" aria-label="Previous week">
          <i class="bi bi-chevron-left"></i>
        </button>

        <div class="week-days d-flex justify-content-center flex-wrap gap-2">
          <div v-for="(day, index) in weekDays" :key="index">
            <div
              class="calendar-day text-center"
              :class="{ active: selectedDay === index }"
              @click="selectDay(index)"
            >
              <div class="day-name">{{ day.name }}</div>
              <div class="day-date">{{ day.date }}</div>
            </div>
          </div>
        </div>

        <button type="button" class="week-arrow-btn" @click="goToNextWeek" aria-label="Next week">
          <i class="bi bi-chevron-right"></i>
        </button>
      </div>

      <div class="matches-section">
        <h5 class="mb-3">Matches on {{ weekDays[selectedDay]?.name }} {{ weekDays[selectedDay]?.date }}</h5>

        <div class="row">
          <div
            v-for="match in matches"
            :key="match.id"
            class="col-12 col-md-6 col-lg-4 mb-3"
          >
            <article class="card match-card h-100">
              <div class="card-body text-center">
                <div class="match-time">{{ match.time }}</div>
                <div class="match-teams">
                  <div class="team-side">
                    <img
                      v-if="match.homeLogo"
                      class="team-logo"
                      :src="match.homeLogo"
                      :alt="`${match.homeTeam} logo`"
                      @error="onLogoError"
                    />
                    <span class="team-name">{{ match.homeTeam }}</span>
                  </div>
                  <span class="vs">vs</span>
                  <div class="team-side">
                    <img
                      v-if="match.awayLogo"
                      class="team-logo"
                      :src="match.awayLogo"
                      :alt="`${match.awayTeam} logo`"
                      @error="onLogoError"
                    />
                    <span class="team-name">{{ match.awayTeam }}</span>
                  </div>
                </div>
                <div v-if="match.venue" class="match-venue">{{ match.venue }}</div>
                <button class="btn btn-sm btn-outline-primary mt-3" @click="openMapModal(match)">
                  Buy Tickets
                </button>
              </div>
            </article>
          </div>
        </div>

        <div v-if="loadingMatches" class="text-center text-muted py-4">
          <p class="mb-0">Loading matches...</p>
        </div>
        <div v-else-if="matches.length === 0" class="text-center text-muted py-4">
          <p class="mb-0">No matches scheduled for this day.</p>
        </div>
      </div>
    </div>

    <TicketMapModal v-model="showMapModal" :match="selectedMatch" />
  </section>
</template>

<script>
import gameService from "@/api/gameService";
import { resolveTeamLogo } from "@/constants/teamLogos";
import TicketMapModal from "@/components/Modal/TicketMapModal.vue";

export default {
  name: "BuyTicketsView",
  components: { TicketMapModal },
  data() {
    return {
      selectedDay: 0,
      selectedWeek: "",
      weekDays: [],
      allGames: [],
      matches: [],
      loadingMatches: false,
      showMapModal: false,
      selectedMatch: null,
    };
  },
  computed: {
    selectedMonthLabel() {
      if (!this.weekDays.length) return "";
      const baseDate = new Date(`${this.weekDays[0].fullDate}T00:00:00`);
      return baseDate.toLocaleDateString("en-US", {
        month: "long",
        year: "numeric",
      });
    },
  },
  async mounted() {
    const today = new Date();
    this.selectedWeek = this.getISOWeekValue(today);
    this.generateWeekDays(this.getISOWeekStartDate(today));
    await this.fetchGames();
    this.loadMatches();
  },
  methods: {
    openMapModal(match) {
      this.selectedMatch = match;
      this.showMapModal = true;
    },
    generateWeekDays(startDate = new Date()) {
      const days = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];
      this.weekDays = [];
      const anchorDate = new Date(startDate);

      for (let i = 0; i < 7; i++) {
        const date = new Date(anchorDate);
        date.setDate(anchorDate.getDate() + i);

        this.weekDays.push({
          name: days[date.getDay()],
          date: date.getDate(),
          fullDate: date.toISOString().split("T")[0],
        });
      }
    },
    selectDay(index) {
      this.selectedDay = index;
      this.loadMatches();
    },
    openCalendarPicker() {
      const picker = this.$refs.weekPicker;
      if (!picker) return;

      if (typeof picker.showPicker === "function") {
        picker.showPicker();
      } else {
        picker.click();
      }
    },
    onWeekChange(event) {
      const selected = event?.target?.value;
      if (!selected) return;

      const startOfWeek = this.getDateFromISOWeekValue(selected);
      this.selectedWeek = selected;
      this.generateWeekDays(startOfWeek);
      this.selectedDay = 0;
      this.loadMatches();
    },
    setWeekFromStartDate(startOfWeek) {
      this.selectedWeek = this.getISOWeekValue(startOfWeek);
      this.generateWeekDays(startOfWeek);
      this.selectedDay = 0;
      this.loadMatches();
    },
    goToPreviousWeek() {
      const start = this.getDateFromISOWeekValue(this.selectedWeek);
      start.setDate(start.getDate() - 7);
      this.setWeekFromStartDate(start);
    },
    goToNextWeek() {
      const start = this.getDateFromISOWeekValue(this.selectedWeek);
      start.setDate(start.getDate() + 7);
      this.setWeekFromStartDate(start);
    },
    goToToday() {
      const today = new Date();
      const startOfWeek = this.getISOWeekStartDate(today);
      this.selectedWeek = this.getISOWeekValue(today);
      this.generateWeekDays(startOfWeek);
      this.selectedDay = (today.getDay() + 6) % 7; // Monday=0 ... Sunday=6
      this.loadMatches();
    },
    async fetchGames() {
      this.loadingMatches = true;
      try {
        const response = await gameService.getAll();
        this.allGames = Array.isArray(response?.data) ? response.data : [];
      } catch (error) {
        this.allGames = [];
      } finally {
        this.loadingMatches = false;
      }
    },
    getISOWeekStartDate(date) {
      const d = new Date(date);
      const day = (d.getDay() + 6) % 7;
      d.setDate(d.getDate() - day);
      d.setHours(0, 0, 0, 0);
      return d;
    },
    getISOWeekValue(date) {
      const tmp = new Date(date);
      tmp.setHours(0, 0, 0, 0);
      tmp.setDate(tmp.getDate() + 3 - ((tmp.getDay() + 6) % 7));
      const week1 = new Date(tmp.getFullYear(), 0, 4);
      const weekNo =
        1 +
        Math.round(
          ((tmp - week1) / 86400000 - 3 + ((week1.getDay() + 6) % 7)) / 7,
        );
      return `${tmp.getFullYear()}-W${String(weekNo).padStart(2, "0")}`;
    },
    getDateFromISOWeekValue(isoWeek) {
      const [yearText, weekText] = isoWeek.split("-W");
      const year = Number(yearText);
      const week = Number(weekText);
      const jan4 = new Date(year, 0, 4);
      const jan4WeekDay = (jan4.getDay() + 6) % 7;
      const mondayWeek1 = new Date(year, 0, 4 - jan4WeekDay);
      const weekStart = new Date(mondayWeek1);
      weekStart.setDate(mondayWeek1.getDate() + (week - 1) * 7);
      weekStart.setHours(0, 0, 0, 0);
      return weekStart;
    },
    loadMatches() {
      const selectedDate = this.weekDays[this.selectedDay]?.fullDate;
      if (!selectedDate || !Array.isArray(this.allGames)) {
        this.matches = [];
        return;
      }

      this.matches = this.allGames
        .filter((game) => {
          if (!game?.game_date) return false;
          const gameDate = String(game.game_date).split(" ")[0];
          return gameDate === selectedDate;
        })
        .map((game) => ({
          id: game.id,
          time: this.formatTime(game.game_date),
          homeTeam: game.home_team?.team_name || "Unknown Home Team",
          awayTeam: game.away_team?.team_name || "Unknown Away Team",
          homeLogo: resolveTeamLogo(game.home_team?.team_name),
          awayLogo: resolveTeamLogo(game.away_team?.team_name),
          venue: game.venue || game.stadium || "",
        }));
    },
    onLogoError(event) {
      if (!event?.target) return;
      event.target.onerror = null;
      event.target.style.display = "none";
    },
    formatTime(dateTimeValue) {
      if (!dateTimeValue) return "--:--";
      const source = String(dateTimeValue).replace(" ", "T");
      const parsedDate = new Date(source);
      if (Number.isNaN(parsedDate.getTime())) {
        const fallbackTime = String(dateTimeValue).split(" ")[1];
        return fallbackTime ? fallbackTime.slice(0, 5) : "--:--";
      }
      return parsedDate.toLocaleTimeString("hu-HU", {
        hour: "2-digit",
        minute: "2-digit",
        hour12: false,
      });
    },
  },
  beforeUnmount() {
    document.body.style.overflow = "";
  },
};
</script>

<style scoped src="@/assets/buy-tickets.css"></style>



