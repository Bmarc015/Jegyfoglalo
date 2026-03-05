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

      <div class="week-days d-flex justify-content-center flex-wrap gap-2 mb-4">
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
                      class="team-logo"
                      :src="match.awayLogo"
                      :alt="`${match.awayTeam} logo`"
                      @error="onLogoError"
                    />
                    <span class="team-name">{{ match.awayTeam }}</span>
                  </div>
                </div>
                <div v-if="match.venue" class="match-venue">{{ match.venue }}</div>
                <button class="btn btn-sm btn-outline-primary mt-3">Buy Tickets</button>
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
  </section>
</template>

<script>
import gameService from "@/api/gameService";

const DEFAULT_TEAM_LOGO = "/Home_Logo.png";

const TEAM_LOGO_ALIASES = {
  "real madrid": "real madrid.png",
  "fc barcelona": "barka.webp",
  "manchester city": "city.png",
  "liverpool fc": "pool.png",
  "bayern munchen": "bayern.png",
  "paris saint germain": "psg.png",
  "arsenal fc": "arsenal.svg",
  "inter milan": "inter.png",
  "juventus": "juventus.svg",
  "atletico madrid": "atma.png",
  "borussia dortmund": "bvb.png",
  "bayer leverkusen": "bayern kusen.png",
  "tottenham hotspur": "tottenham.png",
  "manchester united": "united.png",
  "rb leipzig": "leipzih.png",
  "sporting cp": "sportin.png",
  "feyenoord": "feye.png",
  "olympique marseille": "marseille.png",
  "lazio": "laziooo.png",
  "fiorentina": "fio.png",
  "real sociedad": "sociedad.png",
  "athletic bilbao": "bilbao.png",
  "newcastle united": "newcastle.svg",
  "west ham united": "west ham.png",
  "eintracht frankfurt": "frnakfurt.png",
  "fenerbahce": "fener.png",
  "galatasaray": "galata.png",
  "besiktas": "besiktas.png",
  "panathinaikos": "panathi.png",
  "rangers fc": "rangers.png",
  "club brugge": "clubb brugge.svg",
  "anderlecht": "anderl.png",
  "red bull salzburg": "salzburgi.png",
  "sturm graz": "strum.png",
  "dinamo zagreb": "dinamo.png",
  "hajduk split": "hajduk split.png",
  "slavia praha": "slavia praha.svg",
  "sparta praha": "sparta praha.svg",
  "ferencvaros": "ftc.png",
  "mol fehervar": "mol fehervar.png",
  "debreceni vsc": "dvsc.png",
  "puskas akademia": "puskas.png",
  "mtk budapest": "mtk budapest.png",
  "ujpest fc": "ujpest.svg",
  "boca juniors": "boca.png",
  "river plate": "river plate.png",
  "sao paulo fc": "sao paulo.png",
  "inter miami": "messi.png",
  "la galaxy": "la galaxy.png",
  "al hilal": "al hilal.svg",
  "al ittihad": "ittihad.png",
  "al ahli": "al ahli.svg",
  "shakhtar donetsk": "shaktar.png",
  "dynamo kyiv": "dynamo.svg",
  "fc basel": "basel.png",
  "young boys": "young b.png",
  "sheriff tiraspol": "sheriff.svg",
  "ludogorets": "ludooo.png",
  "crvena zvezda": "crvena.png",
  "partizan": "partizan.svg",
  "malmo ff": "malmo.png",
  "fc copenhagen": "koppenhaga.png",
  "fc midtjylland": "midtjylland.png",
  "bodo glimt": "bodo.png",
  "maccabi haifa": "maccabi.png",
  "aek athens": "athen.png",
  "girona fc": "girona.png",
  "ogc nice": "nice.png",
  "rc lens": "lens.png",
  "stade rennais": "rennes.png",
  "tsg hoffenheim": "hoffeinheim.png",
  "valencia cf": "valencia.png",
};

export default {
  name: "BuyTicketsView",
  data() {
    return {
      selectedDay: 0,
      selectedWeek: "",
      weekDays: [],
      allGames: [],
      matches: [],
      loadingMatches: false,
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
          homeLogo: this.resolveTeamLogo(game.home_team?.team_name),
          awayLogo: this.resolveTeamLogo(game.away_team?.team_name),
          venue: game.venue || game.stadium || "",
        }));
    },
    normalizeTeamKey(value) {
      if (!value) return "";
      return String(value)
        .toLowerCase()
        .normalize("NFD")
        .replace(/[\u0300-\u036f]/g, "")
        .replace(/[^a-z0-9 ]+/g, " ")
        .replace(/\s+/g, " ")
        .trim();
    },
    resolveTeamLogo(teamName) {
      const teamKey = this.normalizeTeamKey(teamName);
      if (!teamKey) return DEFAULT_TEAM_LOGO;

      const aliasFileName = TEAM_LOGO_ALIASES[teamKey];
      if (aliasFileName) {
        return `/csapat%20kepek/${encodeURIComponent(aliasFileName)}`;
      }

      return `/csapat%20kepek/${encodeURIComponent(`${teamKey}.png`)}`;
    },
    onLogoError(event) {
      if (!event?.target) return;
      event.target.onerror = null;
      event.target.src = DEFAULT_TEAM_LOGO;
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
};
</script>

<style scoped>
.buy-tickets-view {
  min-height: calc(100vh - var(--app-menu-height, 92px));
}

.full-page-calendar {
  background: #ffffff;
  border: 1px solid #dfe6ef;
  border-radius: 12px;
  box-shadow: 0 10px 25px rgba(34, 63, 95, 0.08);
  padding: 1.25rem;
}

.calendar-toolbar {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
  flex-wrap: wrap;
}

.calendar-title-wrap {
  display: flex;
  flex-direction: column;
  align-items: center;
  line-height: 1.2;
}

.selected-month {
  margin-top: 0.15rem;
  font-size: 0.85rem;
  color: #40638f;
  font-weight: 600;
}

.calendar-pick-btn {
  border: 1px solid #c9d6ea;
  background: linear-gradient(135deg, #f8fbff 0%, #eaf2ff 100%);
  color: #163a6b;
  border-radius: 10px;
  padding: 0.45rem 0.9rem;
  font-weight: 600;
  box-shadow: 0 4px 12px rgba(22, 58, 107, 0.12);
  transition: all 0.2s ease;
}

.calendar-pick-btn:hover {
  background: linear-gradient(135deg, #eaf2ff 0%, #dce9ff 100%);
  transform: translateY(-1px);
}

.day-picker-input {
  position: absolute;
  opacity: 0;
  width: 0;
  height: 0;
  pointer-events: none;
}

.calendar-day {
  cursor: pointer;
  transition: all 0.2s ease;
  border: 2px solid #dee2e6;
  background-color: white;
  padding: 8px 16px;
  border-radius: 8px;
  min-width: 70px;
}

.calendar-day:hover {
  border-color: #0d6efd;
  background-color: #f8f9fa;
}

.calendar-day.active {
  background-color: #0d6efd;
  color: white;
  border-color: #0d6efd;
}

.day-name {
  font-size: 0.75rem;
  font-weight: bold;
  text-transform: uppercase;
}

.day-date {
  font-size: 1.1rem;
  font-weight: bold;
}

.match-card {
  border: 1px solid #dee2e6;
  border-radius: 10px;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.match-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.match-time {
  font-size: 0.9rem;
  color: #6c757d;
  font-weight: bold;
}

.match-teams {
  font-size: 1rem;
  font-weight: bold;
  margin: 10px 0;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.65rem;
  flex-wrap: wrap;
}

.team-side {
  display: inline-flex;
  align-items: center;
  gap: 0.45rem;
}

.team-logo {
  width: 28px;
  height: 28px;
  object-fit: contain;
  border-radius: 50%;
  background: #ffffff;
  border: 1px solid #dbe3ef;
  padding: 2px;
}

.team-name {
  color: #212529;
}

.vs {
  color: #6c757d;
  margin: 0 8px;
  font-size: 0.85rem;
}

.match-venue {
  font-size: 0.85rem;
  color: #6c757d;
}
</style>
