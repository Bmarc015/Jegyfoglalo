import { defineStore } from "pinia";
// import { useToastStore } from "@/stores/toastStore";
import { useSearchStore } from "./searchStore";
import service from "@/api/teamService";

// const toast = useToastStore();

//változtatás
class Item {
  constructor(id = 0, team_name = "", team_city = "") {
    this.id = id;
    this.team_name = team_name;
    this.team_city = team_city;
  }
}

class Pagination {
  constructor(current_page = 1, last_page = 1, total = 10) {
    this.current_page = current_page;
    this.last_page = last_page;
    this.total = total;
  }
}

export const useTeamStore = defineStore("teams", {
  state: () => ({
    item: new Item(),
    items: [new Item()],
    pagination: new Pagination(),
    selectedPerPage: 10,
    selectedPerPageList: [10, 30, 50],
    loading: false,
    error: null,
    sortColumn: "id",
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
    // READ - Összes adat lekérése
    async getAllAbc() {
      //   const toast = useToastStore();
      this.loading = true;
      this.error = null;
      try {
        const response = await service.getAllAbc();
        this.items = response.data;
      } catch (err) {
        this.error = err;
        throw err;
      } finally {
        this.loading = false;
      }
    },
    //Ha a direction meg van aadva, akkor ez lesz a sorrend
    //Ha nincs megadva, akkor ellentettjére vált
    async getAllSortSearch(column = "id", direction = null) {
      this.sortColumn = column;
      if (direction) {
        this.sortDirection = direction;
      }
      return await this.getPaging(1);
    },
    async getAll() {
      //   const toast = useToastStore();
      this.loading = true;
      this.error = null;
      try {
        const response = await service.getAll();
        // this.searchStore.reset();
        this.items = response.data;
      } catch (err) {
        this.error = err;
        throw err;
      } finally {
        this.loading = false;
      }
    },

    // teamStore.js

    async getPaging(page = null) {
      this.loading = true;
      this.error = null;

      // UI oldalszám (amit a felhasználó lát és kattint)
      const logicalPage = page || this.pagination?.current_page || 1;
      let apiPage = logicalPage;
      const lastPage = this.pagination?.last_page || 1;

      // Speciális szabály: ID DESC esetén az oldalszám maradjon "logikai",
      // de a háttérben tükrözött oldalt kérünk le, így pl. az 1. oldal 10..1 lesz.
      if (this.sortColumn === "id" && this.sortDirection === "desc") {
        apiPage = Math.max(1, lastPage - logicalPage + 1);
      }

      try {
        const response = await service.getPaging(
          apiPage,
          this.selectedPerPage,
          this.sortColumn,
          this.sortDirection,
          this.searchStore.searchWord || "",
        );

        // Az axios interceptor miatt itt már a szerver JSON-ja van:
        // { message: 'OK', data: { data: [...], meta: {...} } }
        const payload = response?.data ?? {};
        this.items = Array.isArray(payload.data) ? payload.data : [];
        this.pagination = payload.meta || this.pagination;

        // A lapozóban a logikai oldalszám maradjon meg.
        if (this.sortColumn === "id" && this.sortDirection === "desc") {
          this.pagination = {
            ...this.pagination,
            current_page: logicalPage,
          };
        }

        return true;
      } catch (err) {
        this.error = err;
        console.error("Hiba a getPaging-ben:", err);
        return false;
      } finally {
        this.loading = false;
      }
    },

    // READ - Egy adat lekérése
    async getById(id) {
      this.loading = true;
      this.error = null;
      //   const toast = useToastStore();
      try {
        const response = await service.getById(id);
        this.item = response.data;
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
        // toast.messages.push("Sikeresen létrehozva!");
        // toast.show("Success");
        return true;
      } catch (err) {
        this.error = err;
        throw err;
        return false;
      } finally {
        this.loading = false;
      }
    },

    // 3. UPDATE - Módosítás (Helyi frissítéssel, újraolvasás nélkül)
    async update(id, updateData) {
      this.loading = true;
      this.error = null;
      try {
        await service.update(id, updateData);
        await this.getPaging(this.pagination?.current_page || 1);
        // toast.messages.push(`Sikeresen módosítva`);
        // toast.show("Success");
        return true;
      } catch (err) {
        this.error = err;
        throw err;
        return false;
      } finally {
        this.loading = false;
      }
    },

    // 4. DELETE - Törlés
    async delete(id) {
      this.loading = true;
      this.error = null;
      try {
        await service.delete(id);
        await this.getPaging(this.pagination?.current_page || 1);
        // toast.messages.push(`Sikeresen törölve`);
        // toast.show("Success");
        return true;
      } catch (err) {
        this.error = err;
        throw err;
        return false;
      } finally {
        this.loading = false;
      }
    },
  },
});
