import { defineStore } from "pinia";
// import { useToastStore } from "@/stores/toastStore";
import { useSearchStore } from "./searchStore";
import service from "@/api/gameService";

// const toast = useToastStore();

//változtatás
class Item {
  constructor(id = 0, osztalyNev = "") {
    this.id = id;
    this.osztalyNev = osztalyNev;
  }
}

class Pagination {
  constructor(current_page = 1, last_page = 1, total = 10) {
    this.current_page = current_page;
    this.last_page = last_page;
    this.total = total;
  }
}

export const useGamesStore = defineStore("games", {
  state: () => ({
    item: new Item(),
    items: [new Item()],
    pagination: new Pagination(),
    selectedPerPage: 10,
    selectedPerPageList: [10, 30, 50],
    loading: false,
    error: null,
    sortColumn: "game_date",
    sortDirection: "asc",
    searchStore: useSearchStore(),
  }),
  getters: {
    getItemsLength() {
      return this.items.length;
    },
  },
  actions: {
    async setSelectedPerPage(value) {
      this.selectedPerPage = value;
      this.loading = true;
      await this.getPaging();
      this.loading = false;
    },
    setColumn(column) {
      if (this.sortColumn === column) {
        this.sortDirection = this.sortDirection === "asc" ? "desc" : "asc";
      } else {
        this.sortColumn = column;
        this.sortDirection = "asc";
      }

      this.getPaging(this.pagination?.current_page || 1);
    },

    clearItem() {
      this.item = new Item();
    },

    // Paging - Lapozás
    async getPaging(page = null) {
      this.loading = true;
      this.error = null;

      const logicalPage = page || this.pagination?.current_page || 1;

      try {
        const response = await service.getPaging(
          logicalPage,
          this.selectedPerPage,
          this.sortColumn,
          this.sortDirection,
          this.searchStore.searchWord || "",
        );

        const payload =
          response && typeof response === "object" && "data" in response && "meta" in response
            ? response
            : response?.data ?? response ?? {};

        // Átalakítjuk az adatokat, hogy a táblázat könnyen kiolvashassa
        this.items =
          payload.data?.map((game) => ({
            ...game,
            team_home_name: game.home_team ? game.home_team.team_name : "N/A",
            team_away_name: game.away_team ? game.away_team.team_name : "N/A",
            team_home_logo: game.home_team ? game.home_team.team_logo : null,
            team_away_logo: game.away_team ? game.away_team.team_logo : null,
          })) || [];

        this.pagination = payload.meta || this.pagination;

        return true;
      } catch (err) {
        this.error = err;
        console.error("Hiba a getPaging-ben:", err);
        return false;
      } finally {
        this.loading = false;
      }
    },

    // READ - Összes adat lekérése
    async getAllAbc() {
      this.loading = true;
      this.error = null;
      try {
        const response = await service.getAllAbc();
        this.items = response?.data ?? response ?? [];
      } catch (err) {
        this.error = err;
        throw err;
      } finally {
        this.loading = false;
      }
    },

    // Rendezés és keresés
    async getAllSortSearch(column = "game_date", direction = null) {
      this.sortColumn = column;
      if (direction) {
        this.sortDirection = direction;
      }
      return await this.getPaging(1);
    },

    // READ - Összes adat (régi, nem használt)
    async getAll() {
      return await this.getPaging(1);
    },

    // READ - Egy adat lekérése
    async getById(id) {
      this.loading = true;
      this.error = null;
      try {
        const response = await service.getById(id);
        this.item = response?.data ?? response ?? null;
      } catch (err) {
        this.error = err;
        throw err;
      } finally {
        this.loading = false;
      }
    },

    // CREATE - Új elem hozzáadása
    async create(data) {
      this.loading = true;
      this.error = null;
      try {
        await service.create(data);
        await this.getPaging(this.pagination?.current_page || 1);
        return true;
      } catch (err) {
        this.error = err.response?.data?.errors?.osztalyNev?.[0] || err.message;
        throw err;
      } finally {
        this.loading = false;
      }
    },

    // UPDATE - Módosítás
    async update(id, updateData) {
      this.loading = true;
      this.error = null;
      try {
        await service.update(id, updateData);
        await this.getPaging(this.pagination?.current_page || 1);
        return true;
      } catch (err) {
        this.error = err;
        throw err;
      } finally {
        this.loading = false;
      }
    },

    // DELETE - Törlés
    async delete(id) {
      this.loading = true;
      this.error = null;
      try {
        await service.delete(id);
        await this.getPaging(this.pagination?.current_page || 1);
        return true;
      } catch (err) {
        this.error = err;
        throw err;
      } finally {
        this.loading = false;
      }
    },
  },
});
