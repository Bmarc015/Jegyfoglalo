import { defineStore } from "pinia";
import gameService from "@/api/gameService";
import { resolveTeamLogo } from "@/constants/teamLogos";

export const useBuyTicketsStore = defineStore("buyTickets", {
  state: () => ({
    selectedDay: 0,
    selectedWeek: "",
    weekDays: [],
    allGames: [],
    matches: [],
    loadingMatches: false,
  }),
  getters: {
    selectedMonthLabel(state) {
      if (!state.weekDays.length) return "";
      const baseDate = new Date(`${state.weekDays[0].fullDate}T00:00:00`);
      return baseDate.toLocaleDateString("en-US", {
        month: "long",
        year: "numeric",
      });
    },
  },
  actions: {
    async initializeCalendar() {
      await this.fetchGames();
      const nextMatchDate = this.getNextMatchDate();
      const baseDate = nextMatchDate ?? new Date();
      this.selectedWeek = this.getISOWeekValue(baseDate);
      const startOfWeek = this.getISOWeekStartDate(baseDate);
      this.generateWeekDays(startOfWeek);
      if (nextMatchDate) {
        const targetIso = nextMatchDate.toISOString().split("T")[0];
        const dayIndex = this.weekDays.findIndex((day) => day.fullDate === targetIso);
        this.selectedDay = dayIndex >= 0 ? dayIndex : 0;
      } else {
        const today = new Date();
        this.selectedDay = (today.getDay() + 6) % 7; // Monday=0 ... Sunday=6
      }
      this.loadMatches();
    },
    applyRouteMatchFocus(matchId) {
      if (!matchId || !Array.isArray(this.allGames)) return null;

      const targetGame = this.allGames.find(
        (game) => String(game?.id) === String(matchId),
      );
      if (!targetGame?.game_date) return null;

      const datePart = String(targetGame.game_date).split(" ")[0];
      const targetDate = new Date(`${datePart}T00:00:00`);
      if (Number.isNaN(targetDate.getTime())) return null;

      const startOfWeek = this.getISOWeekStartDate(targetDate);
      this.selectedWeek = this.getISOWeekValue(targetDate);
      this.generateWeekDays(startOfWeek);
      const dayIndex = this.weekDays.findIndex((day) => day.fullDate === datePart);
      this.selectedDay = dayIndex >= 0 ? dayIndex : 0;
      this.loadMatches();

      return (
        this.matches.find((match) => String(match?.id) === String(matchId)) || null
      );
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
    getNextMatchDate() {
      if (!Array.isArray(this.allGames) || this.allGames.length === 0) return null;
      const today = new Date();
      today.setHours(0, 0, 0, 0);
      let nextDate = null;

      this.allGames.forEach((game) => {
        if (!game?.game_date) return;
        const datePart = String(game.game_date).split(" ")[0];
        const parsed = new Date(`${datePart}T00:00:00`);
        if (Number.isNaN(parsed.getTime())) return;
        if (parsed < today) return;
        if (!nextDate || parsed < nextDate) {
          nextDate = parsed;
        }
      });

      return nextDate;
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
          fullDate: this.formatLocalDate(date),
        });
      }
    },
    selectDay(index) {
      this.selectedDay = index;
      this.loadMatches();
    },
    setWeekFromIsoValue(isoWeek) {
      const startOfWeek = this.getDateFromISOWeekValue(isoWeek);
      this.selectedWeek = isoWeek;
      this.generateWeekDays(startOfWeek);
      this.selectedDay = 0;
      this.loadMatches();
    },
    goToPreviousWeek() {
      const start = this.getDateFromISOWeekValue(this.selectedWeek);
      start.setDate(start.getDate() - 7);
      this.setWeekFromIsoValue(this.getISOWeekValue(start));
    },
    goToNextWeek() {
      const start = this.getDateFromISOWeekValue(this.selectedWeek);
      start.setDate(start.getDate() + 7);
      this.setWeekFromIsoValue(this.getISOWeekValue(start));
    },
    goToToday() {
      const today = new Date();
      const startOfWeek = this.getISOWeekStartDate(today);
      this.selectedWeek = this.getISOWeekValue(today);
      this.generateWeekDays(startOfWeek);
      this.selectedDay = (today.getDay() + 6) % 7; // Monday=0 ... Sunday=6
      this.loadMatches();
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
    formatLocalDate(date) {
      const y = date.getFullYear();
      const m = String(date.getMonth() + 1).padStart(2, "0");
      const d = String(date.getDate()).padStart(2, "0");
      return `${y}-${m}-${d}`;
    },
  },
});
