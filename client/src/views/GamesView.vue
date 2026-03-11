<template>
  <div>
    <!-- oldal fejléc -->
    <div class="team-header mb-3">
      <div class="team-header-center">
        <div class="team-search">
          <div class="input-group input-group-sm">
            <span class="input-group-text">
              <i class="bi bi-search"></i>
            </span>
            <input
              v-model="searchWordInput"
              type="text"
              class="form-control"
              placeholder="Keresés meccsek között..."
              @keyup.enter="onClickSearchButton"
            />
            <button class="btn btn-primary" type="button" @click="onClickSearchButton">
              Keresés
            </button>
          </div>
        </div>
        <Pagination :useCollectionStore="useCollectionStore" />
      </div>

      <div class="team-header-right d-flex align-items-center">
        <!-- homokóra -->
        <i
          v-if="loading"
          class="bi bi-hourglass-split fs-3 col-auto p-0 pe-1"
        ></i>
        <!-- új rekord ikon -->
        <ButtonsCrudCreate v-if="!loading && crudButtonsVisible" @create="createHandler" />
        <p class="m-0 ms-2 records-pill">{{ getItemsLength }} rekord</p>

        <!-- sor/oldal -->
        <SetSelectedPerPage
         :useCollectionStore="useCollectionStore" 
        />
      </div>
    </div>

    <!-- táblázat -->
    <div class="table-full-bleed" v-if="items.length > 0">
      <GenericTable
        :items="items"
        :columns="visibleTableColumns"
        :useCollectionStore="useCollectionStore"
        :cButtonVisible="crudButtonsVisible"
        :uButtonVisible="crudButtonsVisible"
        :dButtonVisible="crudButtonsVisible"
        :toolsColumnVisible="crudButtonsVisible"
        @delete="deleteHandler"
        @update="updateHandler"
        @create="createHandler"
        @sort="sortHandler"
      />
    </div>
    <div v-else style="width: 100px" class="m-auto">Nincs találat</div>

    <!-- Form -->
    <FormSchoolClass
      ref="form"
      :title="title"
      :item="item"
      @yesEventForm="yesEventFormHandler"
    />

    <!-- Confirm modal -->
    <ConfirmModal
      :isOpenConfirmModal="isOpenConfirmModal"
      @cancel="cancelHandler"
      @confirm="confirmHandler"
    />
  </div>
</template>

<script>
import { mapActions, mapState } from "pinia";
import { useGamesStore } from "@/stores/gamesStore";
import { useSearchStore } from "@/stores/searchStore";
import { useUserLoginLogoutStore } from "@/stores/userLoginLogoutStore";
import GenericTable from "@/components/Table/GenericTable.vue";
import ConfirmModal from "@/components/Confirm/ConfirmModal.vue";
import ButtonsCrudCreate from "@/components/Table/ButtonsCrudCreate.vue";
import FormSchoolClass from "@/components/Forms/FormSchoolClass.vue";
import Pagination from "@/components/Pagination/Pagination.vue";
import SetSelectedPerPage from "@/components/Pagination/SetSelectedPerPage.vue";

export default {
  name: "GamesView",
  components: {
    GenericTable,
    ConfirmModal,
    ButtonsCrudCreate,
    FormSchoolClass,
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
      pageTitle: "Games",
      searchWordInput: "",
      tableColumns: [
        { key: "id", label: "ID", debug: import.meta.env.VITE_DEBUG_MODE },
        { key: "team_home_name", label: "Home Team", debug: 2 },
        { key: "team_away_name", label: "Away Team", debug: 2 },
        { key: "game_date", label: "Game_Date", debug: 2 },
      ],
      useCollectionStore: useGamesStore,
      isOpenConfirmModal: false,
      toDeleteId: null,
      state: "r",
      title: "",
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
      this.title = "Adatmódosítás";
      this.getById(id);
      this.$refs.form.show();
      console.log("update:", id);
    },
    createHandler() {
      this.state = "c";
      this.title = "Új adatbevitel";
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
}

.table-full-bleed {
  width: 100%;
  max-width: 100%;
  margin-left: 0;
  margin-right: 0;
  padding-left: 0;
  padding-right: 0;
  overflow-x: auto;
}
</style>
