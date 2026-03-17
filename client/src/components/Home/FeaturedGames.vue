<template>
  <section id="featured-games" class="featured-games-section">
    <div class="container position-relative">
      <div class="featured-header">
        <div>
          <span class="featured-eyebrow">Featured Games</span>
          <h2 class="featured-title">Your next three must-watch clashes</h2>
          <p class="featured-subtitle">
            Fresh picks from the schedule, updated every time you visit.
          </p>
        </div>
        <RouterLink class="btn btn-outline-light featured-cta" to="/adatok/buytickets">
          All Tickets
        </RouterLink>
      </div>

      <div class="row">
        <div
          v-for="match in featuredGames"
          :key="match.id"
          class="col-12 col-lg-4 mb-4"
        >
          <article class="featured-card h-100">
            <div class="featured-card-top">
              <div class="match-time">
                <span class="match-date">{{ match.date }}</span>
                <span class="match-time-sep">•</span>
                <span class="match-clock">{{ match.time }}</span>
              </div>
              <div v-if="match.venue" class="match-venue">{{ match.venue }}</div>
            </div>

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

            <RouterLink class="btn btn-primary featured-buy-btn" to="/adatok/buytickets">
              Buy Tickets
            </RouterLink>
          </article>
        </div>
      </div>

      <div v-if="loadingMatches" class="featured-empty">
        Loading featured matches...
      </div>
      <div v-else-if="featuredGames.length === 0" class="featured-empty">
        No matches available right now.
      </div>
    </div>
  </section>
</template>

<script>
import gameService from "@/api/gameService";
import { resolveTeamLogo } from "@/constants/teamLogos";

export default {
  name: "FeaturedGames",
  data() {
    return {
      featuredGames: [],
      loadingMatches: false,
    };
  },
  async mounted() {
    await this.fetchGames();
  },
  methods: {
    async fetchGames() {
      this.loadingMatches = true;
      try {
        const response = await gameService.getAll();
        const allGames = Array.isArray(response?.data) ? response.data : [];
        this.featuredGames = this.pickFeaturedGames(allGames, 3).map((game) =>
          this.mapGame(game),
        );
      } catch (error) {
        this.featuredGames = [];
      } finally {
        this.loadingMatches = false;
      }
    },
    pickFeaturedGames(games, count) {
      if (!Array.isArray(games)) return [];
      const copy = games.slice();
      for (let i = copy.length - 1; i > 0; i -= 1) {
        const j = Math.floor(Math.random() * (i + 1));
        [copy[i], copy[j]] = [copy[j], copy[i]];
      }
      return copy.slice(0, count);
    },
    mapGame(game) {
      return {
        id: game?.id ?? `${game?.home_team?.team_name}-${game?.away_team?.team_name}`,
        time: this.formatTime(game?.game_date),
        date: this.formatDate(game?.game_date),
        homeTeam: game?.home_team?.team_name || "Unknown Home Team",
        awayTeam: game?.away_team?.team_name || "Unknown Away Team",
        homeLogo: resolveTeamLogo(game?.home_team?.team_name),
        awayLogo: resolveTeamLogo(game?.away_team?.team_name),
        venue: game?.venue || game?.stadium || "",
      };
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
      return parsedDate.toLocaleTimeString("en-GB", {
        hour: "2-digit",
        minute: "2-digit",
        hour12: false,
      });
    },
    formatDate(dateTimeValue) {
      if (!dateTimeValue) return "--.--.--";
      const source = String(dateTimeValue).replace(" ", "T");
      const parsedDate = new Date(source);
      if (Number.isNaN(parsedDate.getTime())) {
        return String(dateTimeValue).split(" ")[0] || "--.--.--";
      }
      return parsedDate.toLocaleDateString("en-GB", {
        year: "numeric",
        month: "short",
        day: "2-digit",
      });
    },
  },
};
</script>

<style scoped>
@import url("https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700&family=Space+Grotesk:wght@500;700&display=swap");

.featured-games-section {
  position: relative;
  padding: 4.5rem 0 5rem;
  color: #f8f9ff;
  overflow: hidden;
  font-family: "Sora", "Space Grotesk", "Segoe UI", sans-serif;
  background: transparent;
}

.featured-header {
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  gap: 2rem;
  flex-wrap: wrap;
  margin-bottom: 2.5rem;
}

.featured-eyebrow {
  text-transform: uppercase;
  letter-spacing: 0.18em;
  font-size: 0.75rem;
  color: rgba(255, 255, 255, 0.7);
  font-weight: 600;
}

.featured-title {
  font-family: "Space Grotesk", "Sora", "Segoe UI", sans-serif;
  font-size: clamp(1.8rem, 2.5vw, 2.6rem);
  font-weight: 700;
  margin: 0.35rem 0 0.6rem;
}

.featured-subtitle {
  font-size: 1.05rem;
  color: rgba(255, 255, 255, 0.72);
  max-width: 520px;
  margin: 0;
}

.featured-cta {
  border-radius: 999px;
  border-width: 2px;
  padding: 0.6rem 1.6rem;
  font-weight: 600;
}

.featured-card {
  position: relative;
  background: rgba(255, 255, 255, 0.08);
  border: 1px solid rgba(255, 255, 255, 0.15);
  border-radius: 18px;
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  gap: 1.5rem;
  backdrop-filter: blur(10px);
  box-shadow: 0 16px 30px rgba(7, 12, 34, 0.35);
  transition: transform 0.25s ease, box-shadow 0.25s ease;
  overflow: hidden;
}

.featured-card::before {
  content: "";
  position: absolute;
  inset: -40% -10% auto -10%;
  height: 180px;
  background: radial-gradient(circle, rgba(255, 168, 76, 0.45), transparent 70%);
  filter: blur(6px);
  opacity: 0.9;
  z-index: 0;
  pointer-events: none;
}

.featured-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 20px 38px rgba(7, 12, 34, 0.5);
}

.featured-card-top {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
  font-weight: 600;
  position: relative;
  z-index: 1;
}

.match-time {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 0.45rem;
  font-size: 0.95rem;
}

.match-time-sep {
  opacity: 0.6;
}

.match-venue {
  font-size: 0.85rem;
  color: rgba(255, 255, 255, 0.65);
}

.match-teams {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.6rem;
  flex-wrap: wrap;
  text-align: center;
  position: relative;
  z-index: 1;
}

.team-side {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  font-weight: 600;
}

.team-logo {
  width: 40px;
  height: 40px;
  object-fit: contain;
  border-radius: 50%;
  background: #ffffff;
  border: 1px solid rgba(255, 255, 255, 0.35);
  padding: 4px;
}

.team-name {
  font-size: 1rem;
}

.vs {
  font-size: 0.85rem;
  letter-spacing: 0.1em;
  text-transform: uppercase;
  color: rgba(255, 255, 255, 0.6);
}

.featured-buy-btn {
  align-self: center;
  border-radius: 999px;
  padding: 0.55rem 1.8rem;
  font-weight: 600;
  box-shadow: 0 8px 16px rgba(13, 110, 253, 0.35);
  position: relative;
  z-index: 1;
}

.featured-empty {
  text-align: center;
  margin-top: 2rem;
  color: rgba(255, 255, 255, 0.7);
}

@media (max-width: 767px) {
  .featured-games-section {
    padding: 3.5rem 0 4rem;
  }

  .featured-header {
    align-items: flex-start;
  }

  .featured-card {
    padding: 1.25rem;
  }
}
</style>
