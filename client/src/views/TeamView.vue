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
      </div>

      <div class="team-header-right d-flex align-items-center">
        <div class="team-search">
          <div class="input-group input-group-sm">
            <span class="input-group-text">
              <i class="bi bi-search"></i>
            </span>
            <input
              v-model="searchWordInput"
              type="text"
              class="form-control"
              placeholder="Search teams..."
              @keyup.enter="onClickSearchButton"
            />
            <button class="btn btn-primary" type="button" @click="onClickSearchButton">
              Search
            </button>
          </div>
        </div>
        <!-- új rekord ikon -->
        <ButtonsCrudCreate v-if="!loading && crudButtonsVisible" @create="createHandler" />
        <p class="m-0 ms-2 records-pill">{{ getItemsLength }} Record</p>

        <!-- sor/oldal -->
        <SetSelectedPerPage :useCollectionStore="useCollectionStore" label="Rows per page:" />
      </div>
    </div>

    <div v-if="loading" class="teams-loading-screen">
      <div class="loading-card">
        <div class="loading-spinner"></div>
        <p>Loading teams...</p>
      </div>
    </div>

    <!-- kártyák -->
    <div class="team-grid" v-else-if="items.length > 0">
      <article v-for="team in items" :key="team.id" class="team-card">
        <div class="team-card-top">
          <ButtonsCrud
            v-if="crudButtonsVisible"
            class="team-card-tools"
            :id="team.id"
            :cButtonVisible="false"
            :uButtonVisible="false"
            :dButtonVisible="crudButtonsVisible"
            @delete="deleteHandler"
            @update="updateHandler"
          />
        </div>
        <div class="team-card-logo" v-if="getTeamLogo(team)">
          <img
            :src="getTeamLogo(team)"
            :alt="team.team_name"
          />
        </div>
        <h3 class="team-card-title">{{ team.team_name }}</h3>
        <p class="team-card-meta">
          <i class="bi bi-geo-alt"></i>
          <span>{{ team.team_city }}</span>
        </p>
        <div class="team-card-footer">
          <span class="team-card-id" v-if="isAdmin">#{{ team.id }}</span>
          <button
            type="button"
            class="team-card-action"
            @click="updateHandler(team.id)"
            v-if="crudButtonsVisible"
          >
            Edit team
          </button>
        </div>
      </article>
    </div>
    <div v-else style="width: 100px" class="m-auto">Nincs találat</div>

    <!-- Form -->
    <FormTeam
      ref="form"
      :title="title"
      :item="item"
      @yesEventForm="yesEventFormHandler"
    />

    <!-- Confirm modal -->
    <ConfirmModal
      :isOpenConfirmModal="isOpenConfirmModal"
      title="Confirm delete"
      message="Are you sure you want to delete this team?"
      cancel="Cancel"
      confirm="Delete"
      @cancel="cancelHandler"
      @confirm="confirmHandler"
    />
  </div>
</template>

<script>
import { mapActions, mapState } from "pinia";
//módosít
import { useTeamStore } from "@/stores/teamStore";
import { useSearchStore } from "@/stores/searchStore";
import { useUserLoginLogoutStore } from "@/stores/userLoginLogoutStore";
import ButtonsCrud from "@/components/Table/ButtonsCrud.vue";
import ConfirmModal from "@/components/Confirm/ConfirmModal.vue";
import ButtonsCrudCreate from "@/components/Table/ButtonsCrudCreate.vue";
import FormTeam from "@/components/Forms/FormTeam.vue";
import Pagination from "@/components/Pagination/Pagination.vue";
import SetSelectedPerPage from "@/components/Pagination/SetSelectedPerPage.vue";
import { resolveTeamLogo } from "@/constants/teamLogos";
export default {
  //módosít
  name: "SportView",
  components: {
    ButtonsCrud,
    ConfirmModal,
    ButtonsCrudCreate,
    FormTeam,
    Pagination,
    SetSelectedPerPage,
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
  },
  data() {
    return {
      //módosít
      pageTitle: "Teams",
      searchWordInput: "",
      //módosít
      tableColumns: [
        // { key: "id", label: "ID", debug: import.meta.env.VITE_DEBUG_MODE },
        { key: "id", label: "ID", debug: 2 },
        { key: "team_name", label: "Team_Name", debug: 2 },
        { key: "team_city", label: "Team_City", debug: 2 },
      ],
      //módosít
      useCollectionStore: useTeamStore,
      isOpenConfirmModal: false,
      toDeleteId: null,
      state: "r", //crud
      title: "",
    };
  },
  computed: {
    //módosít
    ...mapState(useTeamStore, [
      "item",
      "items",
      "loading",
      "sortColumn",
      "sortDirection",
      "getItemsLength",
    ]),
    ...mapState(useSearchStore, ["searchWord"]),
    ...mapState(useUserLoginLogoutStore, ["role"]),
    visibleTableColumns() {
      if (this.role === 1) return this.tableColumns;
      return this.tableColumns.filter((column) => column.key !== "id");
    },
    isAdmin() {
      return this.role === 1;
    },
    crudButtonsVisible() {
      return this.isAdmin;
    },
  },
  methods: {
    getTeamLogo(team) {
      return team?.team_logo || resolveTeamLogo(team?.team_name);
    },
    //módosít
    ...mapActions(useTeamStore, [
      "getAll",
      "getAllSortSearch",
      "getPaging",
      "setColumn",
      "getById",
      "create",
      "update",
      "delete",
      "clearItem"
    ]),
    ...mapActions(useSearchStore, ["resetSearchWord", "setSearchWord"]),
    deleteHandler(id) {
      this.state = "d";
      this.isOpenConfirmModal = true;
      this.toDeleteId = id;
    },
    updateHandler(id) {
      this.state = "u";
      this.title = "Edit Team";
      this.getById(id);
      this.$refs.form.show();
      console.log("update:", id);
    },
    createHandler() {
      this.state = "c";
      this.title = "New Team";
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
      //vagy create, vagy update
      try {
        if (this.state == "c") {
          //create
          await this.create(item);
        } else {
          //update
          await this.update(item.id, item);
        }
        //nem volt hiba
        this.state = "r";
        done(true);
      } catch (err) {
        //hiba volt
        //nem csukódik le az ablak
        if (err.response && err.response.status === 422) {
          // Átadjuk a formnak a konkrét hibaüzeneteket (pl. "min 2 karakter")
          this.$refs.form.setServerErrors(err.response.data.errors);
          done(false); // Nyitva tartja a modalt
        } else {
          // Minden más hiba (500, 401) esetén is értesítjük a modalt, hogy ne záródjon be
          done(false);
        }
        //átadom a hibát
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

.teams-loading-screen {
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
  }

  .team-header-right {
    width: 100%;
    justify-content: space-between;
    flex-wrap: wrap;
  }

  .team-search {
    width: 100%;
  }
}

@media (max-width: 600px) {
  .team-header-right {
    gap: 0.5rem;
  }

  .team-search :deep(.input-group) {
    width: 100%;
  }

  .team-card-footer {
    flex-direction: column;
    align-items: flex-start;
  }
}

.team-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 280px));
  gap: 1.2rem;
  justify-content: center;
}

.team-card {
  width: 100%;
  max-width: 320px;
  background: linear-gradient(135deg, #ffffff 0%, #f6f8fb 100%);
  border: 1px solid #e3e9f1;
  border-radius: 18px;
  padding: 1rem 1.1rem;
  box-shadow: 0 10px 22px rgba(15, 23, 42, 0.08);
  display: flex;
  flex-direction: column;
  gap: 0.55rem;
  position: relative;
  overflow: hidden;
}

.team-card::after {
  content: "";
  position: absolute;
  inset: 0;
  background: radial-gradient(circle at top right, rgba(59, 130, 246, 0.12), transparent 55%);
  pointer-events: none;
}

.team-card-top {
  position: absolute;
  top: 0.9rem;
  right: 0.9rem;
  z-index: 2;
}

.team-card-logo {
  width: 68px;
  height: 68px;
  border-radius: 16px;
  background: #ffffff;
  border: 1px solid #e2e8f0;
  box-shadow: 0 8px 16px rgba(15, 23, 42, 0.08);
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0.4rem;
  position: relative;
  z-index: 1;
}

.team-card-logo img {
  width: 100%;
  height: 100%;
  object-fit: contain;
  display: block;
}

.team-card-title {
  font-size: 1.15rem;
  font-weight: 700;
  color: #0f172a;
  margin: 0;
  position: relative;
  z-index: 1;
}

.team-card-meta {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  color: #475569;
  margin: 0;
  position: relative;
  z-index: 1;
}

.team-card-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.6rem;
  margin-top: auto;
  position: relative;
  z-index: 1;
}

.team-card-id {
  font-weight: 600;
  color: #64748b;
}

.team-card-action {
  border: 0;
  background: linear-gradient(135deg, #0d6efd 0%, #0b57d0 100%);
  color: #fff;
  padding: 0.45rem 0.85rem;
  border-radius: 10px;
  font-weight: 600;
  box-shadow: 0 6px 14px rgba(13, 110, 253, 0.25);
}

.team-card-tools :deep(.crud-action-btn) {
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

.team-card-tools :deep(.crud-action-btn:hover) {
  transform: translateY(-1px);
  box-shadow: 0 10px 18px rgba(15, 23, 42, 0.18);
}

.team-card-tools :deep(.crud-delete) {
  background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
  color: #ffffff;
}

.team-card-tools :deep(.crud-update) {
  background: linear-gradient(135deg, #38bdf8 0%, #0ea5e9 100%);
  color: #ffffff;
}
</style>










