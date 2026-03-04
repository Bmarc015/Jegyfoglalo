import { defineStore } from "pinia";
// import { useToastStore } from "@/stores/toastStore";
import service from "@/api/userLoginLogoutService";
import { useToastStore } from "./toastStore";

export const useUserLoginLogoutStore = defineStore("userLoginLogout", {
  //Ezek a változók
  state: () => ({
    item: JSON.parse(localStorage.getItem("user_data")) || null,
    loading: false,
    error: null,
    rolNames: ["Admin", "Customer", "Guest"],
  }),
  //valamilyen formában visszaadja
  getters: {
    token() {
      if (!this.item) {
        return null;
      }
      return this.item.token;
    },
    role() {
      if (!this.item) {
        return null;
      }
      return this.item.role;
    },
    userName() {
      if (!this.item) {
        return null;
      }
      return this.item.name;
    },
    userNameWithRole() {
      if (!this.item) {
        return "";
      }
      const name = this.item.name || "";
      if (this.item.role === 1) {
        return `${name}: ${this.rolNames[this.item.role - 1]}`;
      }
      return name;
    },
    isLoggedIn() {
      return this.item != null ? true : false;
    },
  },
  //csinál vele valamit
  actions: {
    canAccess(requiredRoles) {
      // Itt a 'this' kulcsszóval éred el a state-et
      if (!requiredRoles || requiredRoles.length === 0) return true;
      if (!this.isLoggedIn) return false;
      return requiredRoles.includes(this.role);
    },
    async login(data) {
      try {
        this.loading = true;
        this.error = null;
        const response = await service.login(data);
        this.item = response.data;
        localStorage.setItem("user_data", JSON.stringify(response.data));
        // const toastStore = useToastStore();
        // toastStore.messages.push("Sikeres bejelentkezés");
        // toastStore.show("Success");
        return true;
      } catch (err) {
        this.error = err;
        throw err;
        return false;
      } finally {
        this.loading = false;
      }
    },
    async logout() {
      try {
        this.error = null;
        this.loading = true;
        const response = await service.logout();
        this.item = null;
        // Törlés localStorage-ból
        localStorage.removeItem("user_data");
        const toastStore = useToastStore();
        toastStore.messages.push("Sikeres kijelenkezés");
        toastStore.show("Success");

        return true;
      } catch (err) {
        this.error = err;
        this.item = null;
        throw err;
        return false;
      } finally {
        this.loading = false;
      }
    },
    async getMeRefresh() {
      try {
        this.error = null;
        this.loading = true;
        const response = await service.getMeRefresh();
        const me = response?.data?.data || {};
        if (!this.item) {
          this.item = {};
        }
        this.item = {
          ...this.item,
          name: me.name ?? this.item.name,
          email: me.email ?? this.item.email,
          billing_city: me.billing_city ?? this.item.billing_city,
          billing_zip: me.billing_zip ?? this.item.billing_zip,
          billing_address: me.billing_address ?? this.item.billing_address,
          role: me.role ?? this.item.role,
        };
        localStorage.setItem("user_data", JSON.stringify(this.item));
        return true;
      } catch (err) {
        this.error = err;
        throw err;
        return false;
      } finally {
        this.loading = false;
      }
    },
    async updateMe(data) {
      try {
        this.error = null;
        this.loading = true;
        const response = await service.updateMe(data);
        const updated = response?.data?.data || {};

        if (this.item) {
          this.item = {
            ...this.item,
            name: updated.name ?? this.item.name,
            email: updated.email ?? this.item.email,
            billing_city: updated.billing_city ?? this.item.billing_city,
            billing_zip: updated.billing_zip ?? this.item.billing_zip,
            billing_address:
              updated.billing_address ?? this.item.billing_address,
          };
          localStorage.setItem("user_data", JSON.stringify(this.item));
        }
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
