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
        <!-- homokóra -->
        <i
          v-if="loading"
          class="bi bi-hourglass-split fs-3 col-auto p-0 pe-1"
        ></i>
        <!-- új rekord ikon -->
        <ButtonsCrudCreate v-if="!loading && crudButtonsVisible" @create="createHandler" />
        <p class="m-0 ms-2 records-pill">{{ getItemsLength }} rekord</p>

        <!-- sor/oldal -->
        <SetSelectedPerPage :useCollectionStore="useCollectionStore" label="Rows per page:" />
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
import GenericTable from "@/components/Table/GenericTable.vue";
import ConfirmModal from "@/components/Confirm/ConfirmModal.vue";
import ButtonsCrudCreate from "@/components/Table/ButtonsCrudCreate.vue";
import FormTeam from "@/components/Forms/FormTeam.vue";
import Pagination from "@/components/Pagination/Pagination.vue";
import SetSelectedPerPage from "@/components/Pagination/SetSelectedPerPage.vue";
export default {
  //módosít
  name: "SportView",
  components: {
    GenericTable,
    ConfirmModal,
    ButtonsCrudCreate,
    FormTeam,
    Pagination,
    SetSelectedPerPage,
  },
  watch: {
    searchWord() {
      this.getPaging();
    },
  },
  data() {
    return {
      //módosít
      pageTitle: "Teams",
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
    ...mapActions(useSearchStore, ["resetSearchWord"]),
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
