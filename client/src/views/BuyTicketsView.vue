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
                <button
                  v-if="isLoggedIn"
                  class="btn btn-sm btn-outline-primary mt-3"
                  @click="handleBuyTickets(match)"
                >
                  Buy Tickets
                </button>
                <p v-else class="text-muted small mt-3 mb-0">Please sign in to purchase tickets.</p>
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
import { mapActions, mapState } from "pinia";
import TicketMapModal from "@/components/Modal/TicketMapModal.vue";
import { useBuyTicketsStore } from "@/stores/buyTicketsStore";
import { useUserLoginLogoutStore } from "@/stores/userLoginLogoutStore";

export default {
  name: "BuyTicketsView",
  components: { TicketMapModal },
  data() {
    return {
      showMapModal: false,
      selectedMatch: null,
    };
  },
  computed: {
    ...mapState(useBuyTicketsStore, [
      "selectedDay",
      "selectedWeek",
      "weekDays",
      "matches",
      "loadingMatches",
      "selectedMonthLabel",
    ]),
    ...mapState(useUserLoginLogoutStore, ["isLoggedIn"]),
  },
  async mounted() {
    await this.initializeCalendar();
    const focusedMatch = this.applyRouteMatchFocus(this.$route?.query?.matchId);
    if (focusedMatch) {
      this.openMapModal(focusedMatch);
    }
  },
  methods: {
    ...mapActions(useBuyTicketsStore, [
      "initializeCalendar",
      "applyRouteMatchFocus",
      "selectDay",
      "setWeekFromIsoValue",
      "goToPreviousWeek",
      "goToNextWeek",
      "goToToday",
    ]),
    handleBuyTickets(match) {
      this.openMapModal(match);
    },
    openMapModal(match) {
      this.selectedMatch = match;
      this.showMapModal = true;
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
      this.setWeekFromIsoValue(selected);
    },
    onLogoError(event) {
      if (!event?.target) return;
      event.target.onerror = null;
      event.target.style.display = "none";
    },
  },
  beforeUnmount() {
    document.body.style.overflow = "";
  },
  watch: {
    "$route.query.matchId"() {
      const focusedMatch = this.applyRouteMatchFocus(this.$route?.query?.matchId);
      if (focusedMatch) {
        this.openMapModal(focusedMatch);
      }
    },
  },
};
</script>

<style scoped src="@/assets/buy-tickets.css"></style>



