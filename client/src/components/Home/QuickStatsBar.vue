<template>
  <section class="quick-stats">
    <div class="container">
      <div class="quick-stats-card">
        <div class="stat">
          <span class="stat-label">Venue</span>
          <span class="stat-value">{{ stats.venue }}</span>
        </div>
        <div class="stat">
          <span class="stat-label">Next Kickoff</span>
          <span class="stat-value">{{ stats.nextKickoff }}</span>
        </div>
        <div class="stat">
          <span class="stat-label">This Week</span>
          <span class="stat-value">{{ stats.thisWeek }}</span>
        </div>
        <div class="stat">
          <span class="stat-label">Total Fixtures</span>
          <span class="stat-value">{{ stats.totalFixtures }}</span>
        </div>
      </div>
    </div>
  </section>
</template>

<script>
import gameService from "@/api/gameService";

export default {
  name: "QuickStatsBar",
  data() {
    return {
      stats: {
        venue: "Santiago",
        nextKickoff: "--.--.-- --:--",
        thisWeek: 0,
        totalFixtures: 0,
      },
    };
  },
  async mounted() {
    await this.fetchStats();
  },
  methods: {
    async fetchStats() {
      try {
        const response = await gameService.getAll();
        const games = Array.isArray(response?.data) ? response.data : [];
        let totalFixtures = games.length;
        let thisWeek = 0;
        const now = new Date();
        let nextMatch = null;
        let earliestMatch = null;
        const startOfWeek = this.getISOWeekStartDate(now);
        const endOfWeek = new Date(startOfWeek);
        endOfWeek.setDate(endOfWeek.getDate() + 7);

        for (const game of games) {
          const dateValue = game?.game_date;
          if (dateValue) {
            const parsed = new Date(String(dateValue).replace(" ", "T"));
            if (Number.isNaN(parsed.getTime())) continue;
            if (parsed >= startOfWeek && parsed < endOfWeek) {
              thisWeek += 1;
            }
            if (parsed >= now && (!nextMatch || parsed < nextMatch)) {
              nextMatch = parsed;
            }
            if (!earliestMatch || parsed < earliestMatch) {
              earliestMatch = parsed;
            }
          }
        }

        const kickoffSource = nextMatch || earliestMatch;
        this.stats = {
          venue: "Santiago",
          nextKickoff: kickoffSource
            ? this.formatDateTime(kickoffSource)
            : "--.--.-- --:--",
          thisWeek,
          totalFixtures,
        };
      } catch (error) {
        this.stats = {
          venue: "Santiago",
          nextKickoff: "--.--.-- --:--",
          thisWeek: 0,
          totalFixtures: 0,
        };
      }
    },
    getISOWeekStartDate(date) {
      const d = new Date(date);
      const day = (d.getDay() + 6) % 7;
      d.setDate(d.getDate() - day);
      d.setHours(0, 0, 0, 0);
      return d;
    },
    formatDateTime(date) {
      return date.toLocaleString("en-GB", {
        year: "numeric",
        month: "2-digit",
        day: "2-digit",
        hour: "2-digit",
        minute: "2-digit",
        hour12: false,
      });
    },
  },
};
</script>

<style scoped>
@import url("https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700&display=swap");

.quick-stats {
  padding: 1.5rem 0;
}

.quick-stats-card {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 1.5rem;
  background: rgba(255, 255, 255, 0.08);
  border-radius: 16px;
  padding: 1.2rem 1.6rem;
  color: #f3f6ff;
  box-shadow: 0 12px 28px rgba(6, 12, 32, 0.35);
  border: 1px solid rgba(255, 255, 255, 0.08);
  font-family: "Sora", "Segoe UI", sans-serif;
}

.stat {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.35rem;
  text-align: center;
}

.stat-label {
  font-size: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 0.12em;
  color: rgba(255, 255, 255, 0.65);
  font-weight: 600;
}

.stat-value {
  font-size: 1.6rem;
  font-weight: 700;
}

@media (max-width: 991px) {
  .quick-stats-card {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

@media (max-width: 575px) {
  .quick-stats-card {
    grid-template-columns: 1fr;
  }
}
</style>
