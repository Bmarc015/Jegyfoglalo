<template>
  <div>
    <!-- oldal fejléc -->
    <div class="team-header mb-3">
      <div class="team-header-center">
        <Pagination
          :useCollectionStore="useCollectionStore"
          firstTitle="First page"
          lastTitle="Last page"
        />
        <div class="team-search">
          <div class="input-group input-group-sm">
            <span class="input-group-text">
              <i class="bi bi-search"></i>
            </span>
            <input
              v-model="searchWordInput"
              type="text"
              class="form-control"
              placeholder="Search games..."
              @keyup.enter="onClickSearchButton"
            />
            <button class="btn btn-primary" type="button" @click="onClickSearchButton">
              Search
            </button>
          </div>
        </div>
      </div>

      <div class="team-header-right d-flex align-items-center">
        <!-- új rekord ikon -->
        <ButtonsCrudCreate v-if="!loading && crudButtonsVisible" @create="createHandler" />
        <p class="m-0 ms-2 records-pill">{{ getItemsLength }} rekord</p>

        <!-- sor/oldal -->
        <SetSelectedPerPage :useCollectionStore="useCollectionStore" label="Rows per page:" />
      </div>
    </div>

    <div v-if="loading" class="games-loading-screen">
      <div class="loading-card">
        <div class="loading-spinner"></div>
        <p>Loading games...</p>
      </div>
    </div>

    <!-- kártyák -->
    <div class="game-grid" v-else-if="items.length > 0">
      <article v-for="game in items" :key="game.id" class="game-card">
        <div class="game-card-tools">
          <ButtonsCrud
            v-if="crudButtonsVisible"
            :id="game.id"
            :cButtonVisible="false"
            :uButtonVisible="false"
            :dButtonVisible="crudButtonsVisible"
            @delete="deleteHandler"
            @update="updateHandler"
          />
        </div>

        <div class="game-card-teams">
          <div class="game-team">
            <div class="game-team-logo" v-if="getGameLogo(game.team_home_name, game.team_home_logo)">
              <img
                :src="getGameLogo(game.team_home_name, game.team_home_logo)"
                :alt="game.team_home_name"
              />
            </div>
            <span class="game-team-name">{{ game.team_home_name }}</span>
          </div>

          <span class="game-vs">vs</span>

          <div class="game-team">
            <div class="game-team-logo" v-if="getGameLogo(game.team_away_name, game.team_away_logo)">
              <img
                :src="getGameLogo(game.team_away_name, game.team_away_logo)"
                :alt="game.team_away_name"
              />
            </div>
            <span class="game-team-name">{{ game.team_away_name }}</span>
          </div>
        </div>

        <div class="game-card-footer">
          <span class="game-date">
            <i class="bi bi-calendar3"></i>
            {{ game.game_date || "Date TBD" }}
          </span>
          <div class="game-card-actions">
            <button
              type="button"
              class="game-card-action secondary"
              @click="openMapModal(game)"
              :disabled="checkingTicketsId === game.id"
            >
              <span v-if="checkingTicketsId === game.id" class="btn-spinner"></span>
              {{ checkingTicketsId === game.id ? "Loading..." : "Check tickets" }}
            </button>
            <button
              type="button"
              class="game-card-action"
              @click="updateHandler(game.id)"
              v-if="crudButtonsVisible"
            >
              Edit game
            </button>
          </div>
        </div>
      </article>
    </div>
    <div v-else style="width: 100px" class="m-auto">Nincs találat</div>

    <!-- Form -->
    <FormGame
      ref="form"
      :title="title"
      :item="item"
      @yesEventForm="yesEventFormHandler"
    />

    <!-- Confirm modal -->
    <ConfirmModal
      :isOpenConfirmModal="isOpenConfirmModal"
      title="Confirm delete"
      message="Are you sure you want to delete this game?"
      cancel="Cancel"
      confirm="Delete"
      @cancel="cancelHandler"
      @confirm="confirmHandler"
    />
    <TicketMapModal v-model="showMapModal" :match="selectedMatch" />
  </div>
</template>

<script>
import { mapActions, mapState } from "pinia";
import { useGamesStore } from "@/stores/gamesStore";
import { useSearchStore } from "@/stores/searchStore";
import { useUserLoginLogoutStore } from "@/stores/userLoginLogoutStore";
import ButtonsCrud from "@/components/Table/ButtonsCrud.vue";
import ConfirmModal from "@/components/Confirm/ConfirmModal.vue";
import ButtonsCrudCreate from "@/components/Table/ButtonsCrudCreate.vue";
import FormGame from "@/components/Forms/FormGame.vue";
import Pagination from "@/components/Pagination/Pagination.vue";
import SetSelectedPerPage from "@/components/Pagination/SetSelectedPerPage.vue";
import { resolveTeamLogo } from "@/constants/teamLogos";
import TicketMapModal from "@/components/Modal/TicketMapModal.vue";

export default {
  name: "GamesView",
  components: {
    ButtonsCrud,
    ConfirmModal,
    ButtonsCrudCreate,
    FormGame,
    Pagination,
    SetSelectedPerPage,
    TicketMapModal,
  },
  watch: {
    searchWord() {
      this.searchWordInput = this.searchWord;
      this.getPaging();
    },
    searchWordInput(value) {
      if (!value) {
        this.resetSearchWord();
      }
    },
    showMapModal(value) {
      if (!value) this.checkingTicketsId = null;
    },
  },
  data() {
    return {
      pageTitle: "Games",
      searchWordInput: "",
      useCollectionStore: useGamesStore,
      isOpenConfirmModal: false,
      toDeleteId: null,
      state: "r",
      title: "",
      showMapModal: false,
      selectedMatch: null,
      checkingTicketsId: null,
    };
  },
  computed: {
    ...mapState(useGamesStore, [
      "item",
      "items",
      "loading",
      "sortColumn",
      "sortDirection",
      "getItemsLength",
      "pagination",
    ]),
    ...mapState(useSearchStore, ["searchWord"]),
    ...mapState(useUserLoginLogoutStore, ["role"]),
    isAdmin() {
      return this.role === 1;
    },
    crudButtonsVisible() {
      return this.isAdmin;
    },
  },
  methods: {
    getGameLogo(teamName, logoValue) {
      if (logoValue) {
        const raw = String(logoValue);
        if (raw.startsWith("http")) return raw;
        return `/csapat%20kepek/${encodeURIComponent(raw)}`;
      }
      return resolveTeamLogo(teamName);
    },
    openMapModal(game) {
      this.checkingTicketsId = game?.id ?? null;
      this.selectedMatch = {
        id: game?.id ?? game?.game_id ?? null,
        homeTeam: game?.team_home_name ?? "",
        awayTeam: game?.team_away_name ?? "",
        time: game?.game_time ?? game?.time ?? "",
        matchDate: game?.game_date ?? game?.match_date ?? "",
        venue: game?.venue ?? "",
      };
      this.showMapModal = true;
    },
    ...mapActions(useGamesStore, [
      "getAll",
      "getAllSortSearch",
      "getPaging",
      "setColumn",
      "getById",
      "create",
      "update",
      "delete",
      "clearItem",
    ]),
    ...mapActions(useSearchStore, ["resetSearchWord", "setSearchWord"]),
    deleteHandler(id) {
      this.state = "d";
      this.isOpenConfirmModal = true;
      this.toDeleteId = id;
    },
    updateHandler(id) {
      this.state = "u";
      this.title = "Edit Game";
      this.getById(id);
      this.$refs.form.show();
      console.log("update:", id);
    },
    createHandler() {
      this.state = "c";
      this.title = "New Game";
      this.clearItem();
      this.$refs.form.show();
      console.log("Create:");
    },
    sortHandler(column) {
      console.log(column);
      this.setColumn(column);
    },
    onClickSearchButton() {
      this.setSearchWord(this.searchWordInput);
      this.getPaging(1);
    },
    cancelHandler() {
      console.log("mégsem törlök");
      this.isOpenConfirmModal = false;
      this.state = "r";
    },
    async confirmHandler() {
      try {
        await this.delete(this.toDeleteId);
      } catch (error) {}
      this.isOpenConfirmModal = false;
      this.state = "r";
    },
    async yesEventFormHandler({ item, done }) {
      try {
        if (this.state == "c") {
          await this.create(item);
        } else {
          await this.update(item.id, item);
        }
        this.state = "r";
        done(true);
      } catch (err) {
        if (err.response && err.response.status === 422) {
          this.$refs.form.setServerErrors(err.response.data.errors);
          done(false);
        } else {
          done(false);
        }
      }
    },
  },
  async mounted() {
    this.resetSearchWord();
    await this.getPaging(1);
  },
};
</script>

<style scoped>
.team-header {
  display: flex;
  align-items: center;
  gap: 0.85rem;
  padding: 0.65rem 0.9rem;
  border: 1px solid #d8dee7;
  border-radius: 12px;
  background: linear-gradient(180deg, #ffffff 0%, #f3f6fb 100%);
  box-shadow: 0 6px 18px rgba(15, 23, 42, 0.06);
  justify-content: space-between;
}

.team-header-center {
  flex: 1 1 auto;
  display: flex;
  justify-content: flex-start;
  align-items: center;
  gap: 0.75rem;
}

.team-header-center :deep(nav) {
  margin-left: 0 !important;
}

.team-header-center :deep(.pagination) {
  background: #eef3fb;
  border: 1px solid #d8e2f0;
  border-radius: 10px;
  padding: 0.2rem;
}

.team-search {
  min-width: 260px;
  margin-left: auto;
}

.team-search :deep(.input-group) {
  border-radius: 10px;
  overflow: hidden;
  box-shadow: 0 6px 16px rgba(15, 23, 42, 0.08);
}

.team-search :deep(.input-group-text) {
  background: #ffffff;
  border: 1px solid #d8e2f0;
  border-right: 0;
  color: #0b57d0;
}

.team-search :deep(.form-control) {
  border-color: #d8e2f0;
}

.team-search :deep(.btn) {
  border-radius: 0;
}

.team-header-center :deep(.page-link) {
  border: 0;
  margin: 0 1px;
  border-radius: 8px;
  color: #1f3a67;
  font-weight: 600;
  background: transparent;
}

.team-header-center :deep(.page-item.active .page-link) {
  background: linear-gradient(135deg, #0d6efd 0%, #0b57d0 100%);
  color: #fff;
}

.team-header-center :deep(.page-link:hover) {
  background: #dbe8ff;
}

.team-header-right {
  flex: 0 0 auto;
  gap: 0.5rem;
  background: #ffffff;
  border: 1px solid #e0e7f1;
  border-radius: 10px;
  padding: 0.35rem 0.45rem;
  margin-left: auto;
}

.records-pill {
  padding: 0.2rem 0.55rem;
  border-radius: 999px;
  background: #e8f0ff;
  color: #0b57d0;
  font-weight: 600;
  font-size: 0.9rem;
}

@media (max-width: 992px) {
  .team-header {
    flex-wrap: wrap;
    padding: 0.6rem;
  }

  .team-header-center {
    order: 3;
    width: 100%;
    justify-content: center;
    flex-wrap: wrap;
  }

  .team-header-right {
    width: 100%;
    justify-content: space-between;
    flex-wrap: wrap;
  }

  .team-search {
    width: 100%;
    margin-left: 0;
  }
}

@media (max-width: 600px) {
  .team-header-right {
    gap: 0.5rem;
  }

  .team-search :deep(.input-group) {
    width: 100%;
  }

  .game-card-footer {
    flex-direction: column;
    align-items: flex-start;
  }

  .game-card-actions {
    width: 100%;
    justify-content: flex-start;
  }
}

.game-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(260px, 320px));
  gap: 1.2rem;
  justify-content: center;
}

.games-loading-screen {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 220px;
  margin-bottom: 1rem;
}

.loading-card {
  display: inline-flex;
  align-items: center;
  gap: 0.65rem;
  padding: 0.75rem 1rem;
  border-radius: 999px;
  border: 1px solid #d8e2f0;
  background: #ffffff;
  box-shadow: 0 10px 22px rgba(15, 23, 42, 0.08);
  color: #0f172a;
  font-weight: 600;
}

.loading-spinner {
  width: 18px;
  height: 18px;
  border-radius: 50%;
  border: 2px solid #c7d7f5;
  border-top-color: #0b57d0;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.game-card {
  background: linear-gradient(135deg, #ffffff 0%, #f6f8fb 100%);
  border: 1px solid #e3e9f1;
  border-radius: 18px;
  padding: 1rem 1.1rem 0.9rem;
  box-shadow: 0 10px 22px rgba(15, 23, 42, 0.08);
  position: relative;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  gap: 0.9rem;
}

.game-card::after {
  content: "";
  position: absolute;
  inset: 0;
  background: radial-gradient(circle at top right, rgba(59, 130, 246, 0.12), transparent 55%);
  pointer-events: none;
}

.game-card-tools {
  position: absolute;
  top: 0.9rem;
  right: 0.6rem;
  z-index: 2;
}

.game-card-tools :deep(.crud-action-btn) {
  width: 34px;
  height: 34px;
  border-radius: 10px;
  border: 1px solid rgba(15, 23, 42, 0.08);
  box-shadow: 0 6px 14px rgba(15, 23, 42, 0.12);
  display: inline-flex;
  align-items: center;
  justify-content: center;
  transition: transform 120ms ease, box-shadow 120ms ease, filter 120ms ease;
}

.game-card-tools :deep(.crud-action-btn:hover) {
  transform: translateY(-1px);
  box-shadow: 0 10px 18px rgba(15, 23, 42, 0.18);
}

.game-card-tools :deep(.crud-delete) {
  background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
  color: #ffffff;
}

.game-card-tools :deep(.crud-update) {
  background: linear-gradient(135deg, #38bdf8 0%, #0ea5e9 100%);
  color: #ffffff;
}

.game-card-teams {
  display: grid;
  grid-template-columns: 1fr auto 1fr;
  align-items: center;
  gap: 0.75rem;
  position: relative;
  z-index: 1;
}

.game-team {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.45rem;
  text-align: center;
}

.game-team-logo {
  width: 64px;
  height: 64px;
  border-radius: 16px;
  background: #ffffff;
  border: 1px solid #e2e8f0;
  box-shadow: 0 8px 16px rgba(15, 23, 42, 0.08);
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0.35rem;
}

.game-team-logo img {
  width: 100%;
  height: 100%;
  object-fit: contain;
  display: block;
}

.game-team-name {
  font-weight: 700;
  color: #0f172a;
  font-size: 0.98rem;
}

.game-vs {
  font-weight: 700;
  color: #0b57d0;
  text-transform: uppercase;
  letter-spacing: 0.08em;
}

.game-card-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.8rem;
  position: relative;
  z-index: 1;
}

.game-card-actions {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  flex-wrap: wrap;
  justify-content: flex-end;
}

.game-date {
  color: #475569;
  font-weight: 600;
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
}

.game-card-action {
  border: 0;
  background: linear-gradient(135deg, #0d6efd 0%, #0b57d0 100%);
  color: #fff;
  padding: 0.45rem 0.85rem;
  border-radius: 10px;
  font-weight: 600;
  box-shadow: 0 6px 14px rgba(13, 110, 253, 0.25);
}

.game-card-action.secondary {
  background: #ffffff;
  color: #0b57d0;
  border: 1px solid #c7d7f5;
  box-shadow: none;
}

.game-card-action:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.btn-spinner {
  width: 14px;
  height: 14px;
  border-radius: 50%;
  border: 2px solid #c7d7f5;
  border-top-color: #0b57d0;
  display: inline-block;
  margin-right: 0.45rem;
  animation: spin 0.8s linear infinite;
}
</style>
