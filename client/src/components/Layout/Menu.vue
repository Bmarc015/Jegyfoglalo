<template>
  <div>
    <nav
      ref="mainNav"
      class="navbar navbar-expand-md fixed-top bg-white border-bottom border-dark-subtle shadow-sm"
      data-bs-theme="light"
    >
      <div class="container-fluid text-center">
        <Header id="home-link" />

        <button
          class="navbar-toggler"
          type="button"
          data-bs-toggle="collapse"
          data-bs-target="#navbarSupportedContent"
          aria-controls="navbarSupportedContent"
          aria-expanded="false"
          aria-label="Toggle navigation"
        >
          <span class="navbar-toggler-icon"></span>
        </button>

        <div id="navbarSupportedContent" class="collapse navbar-collapse">
          <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-2">
            <li class="nav-item">
              <RouterLink class="nav-link" to="/about">About</RouterLink>
            </li>
            <li class="nav-item" v-if="hasMenuAccess('/adatok/games')">
              <RouterLink class="nav-link" to="/adatok/games">Games</RouterLink>
            </li>
            <li class="nav-item">
              <RouterLink class="nav-link" to="/adatok/teams">Teams</RouterLink>
            </li>
            <li class="nav-item">
              <RouterLink class="nav-link" to="/adatok/buytickets">Game Calendar</RouterLink>
            </li>
            <li class="nav-item" v-if="hasMenuAccess('/adatok/users')">
              <RouterLink class="nav-link" to="/adatok/users">Users</RouterLink>
            </li>
            <li class="nav-item">
              <RouterLink class="btn btn-outline-primary ms-2" :to="buyTicketsMenuLink">Buy Tickets</RouterLink>
            </li>
          </ul>

          <ul class="navbar-nav mb-2 mb-lg-0">
            <li class="nav-item" v-if="!isLoggedIn">
              <RouterLink class="nav-link" to="/login">
                <i class="bi bi-person"></i> Sign In
              </RouterLink>
            </li>
            <li class="nav-item" v-if="!isLoggedIn">
              <RouterLink class="nav-link" to="/registration">
                <i class="bi bi-person-plus"></i> Registration
              </RouterLink>
            </li>
            <li class="nav-item" v-if="isLoggedIn">
              <RouterLink class="nav-link cart-link" :to="cartMenuLink">
                <span class="cart-icon">
                  <i class="bi bi-cart3"></i>
                  <span v-if="cartCount > 0" class="cart-badge">{{ cartCount }}</span>
                </span>
              </RouterLink>
            </li>
            <li class="nav-item dropdown" v-if="isLoggedIn">
              <a
                class="nav-link dropdown-toggle d-flex align-items-center"
                href="#"
                role="button"
                data-bs-toggle="dropdown"
                aria-expanded="false"
              >
                <i class="bi bi-person me-1"></i>
                {{ userNameWithRole }}
              </a>
              <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                <li>
                  <RouterLink class="dropdown-item" to="/profile">
                    <i class="bi bi-person-badge me-2"></i>
                    Profile
                  </RouterLink>
                </li>
                <li><hr class="dropdown-divider" /></li>
                <li>
                  <button class="dropdown-item text-danger" type="button" @click="onClickLogut()">
                    <i class="bi bi-box-arrow-right me-2"></i>
                    Sign out
                  </button>
                </li>
              </ul>
            </li>
          </ul>
        </div>
      </div>
    </nav>
  </div>
</template>

<script>
import { mapActions, mapState } from "pinia";
import { useSearchStore } from "@/stores/searchStore";
import { useUserLoginLogoutStore } from "@/stores/userLoginLogoutStore";
import Header from "./Header.vue";

export default {
  components: {
    Header,
  },
  data() {
    return {
      searchWordInput: "",
      timeout: null,
      cartCount: 0,
    };
  },
  watch: {
    searchWordInput(value) {
      if (!value) {
        this.resetSearchWord();
      }
    },
    searchWord(value) {
      this.searchWordInput = value;
    },
  },
  computed: {
    ...mapState(useSearchStore, ["searchWord"]),
    ...mapState(useUserLoginLogoutStore, ["isLoggedIn", "userNameWithRole"]),
    buyTicketsMenuLink() {
      if (this.isLoggedIn) return "/adatok/buytickets";
      return { path: "/login", query: { redirect: "/adatok/buytickets" } };
    },
    cartMenuLink() {
      if (this.isLoggedIn) return "/checkout";
      return { path: "/login", query: { redirect: "/checkout" } };
    },
  },
  methods: {
    ...mapActions(useSearchStore, ["resetSearchWord", "setSearchWord"]),
    ...mapActions(useUserLoginLogoutStore, ["logout"]),
    onClickSearchButton() {
      this.setSearchWord(this.searchWordInput);
    },
    hasMenuAccess(targetPath) {
      const userStore = useUserLoginLogoutStore();
      const resolved = this.$router.resolve(targetPath);
      if (!resolved || !resolved.matched.length) return false;

      return resolved.matched.every((route) => {
        const requiredRoles = route.meta?.roles;
        return userStore.canAccess(requiredRoles);
      });
    },
    async onClickLogut() {
      try {
        await this.logout();
        this.$router.push("/");
      } catch (error) {
        console.log("Kijelentkezesi hiba!");
      }
    },
    updateMenuHeightVar() {
      const nav = this.$refs.mainNav;
      if (!nav) return;
      const height = Math.ceil(nav.getBoundingClientRect().height);
      document.documentElement.style.setProperty("--app-menu-height", `${height + 8}px`);
    },
    refreshCartCount() {
      try {
        const raw = sessionStorage.getItem("checkoutCart");
        const parsed = raw ? JSON.parse(raw) : null;
        const items = Array.isArray(parsed) ? parsed : parsed ? [parsed] : [];
        const count = items.reduce((sum, item) => {
          if (Array.isArray(item?.seatIds)) return sum + item.seatIds.length;
          if (Array.isArray(item?.seats)) return sum + item.seats.length;
          return sum;
        }, 0);
        this.cartCount = count;
      } catch (error) {
        this.cartCount = 0;
      }
    },
  },
  mounted() {
    this.$nextTick(() => {
      this.updateMenuHeightVar();
    });
    window.addEventListener("resize", this.updateMenuHeightVar);
    window.addEventListener("cart-updated", this.refreshCartCount);
    this.refreshCartCount();
  },
  beforeUnmount() {
    window.removeEventListener("resize", this.updateMenuHeightVar);
    window.removeEventListener("cart-updated", this.refreshCartCount);
  },
};
</script>

<style scoped>
.nav-link.active,
.nav-link.router-link-exact-active {
  color: #0800ff !important;
  font-weight: bold;
  border-bottom: 2px solid rgb(0, 17, 255);
}

.navbar-nav.me-auto .nav-item + .nav-item {
  position: relative;
  padding-left: 0.75rem;
  margin-left: 0.35rem;
}

.navbar-nav.me-auto .nav-item + .nav-item::before {
  content: "";
  position: absolute;
  left: 0;
  top: 50%;
  width: 1px;
  height: 22px;
  background-color: #d0d7e2;
  transform: translateY(-50%);
}

.nav-item:has(.dropdown-item.router-link-active) .nav-link.dropdown-toggle {
  color: #0800ff !important;
  font-weight: bold;
  border-bottom: 2px solid rgb(0, 21, 255);
}

.dropdown-item.router-link-active {
  background-color: transparent !important;
  color: #0800ff !important;
  font-weight: bold;
}

.navbar {
  position: fixed;
  top: 0;
  left: var(--app-gutter, 18px);
  right: var(--app-gutter, 18px);
  z-index: 1060 !important;
  border-radius: 0 0 10px 10px;
}

.dropdown-menu {
  z-index: 1060 !important;
}

.home-link {
  color: black !important;
}

.cart-link {
  position: relative;
  padding-top: 0.35rem;
  padding-bottom: 0.35rem;
}

.cart-icon {
  position: relative;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 38px;
  height: 38px;
  border-radius: 999px;
  border: 1px solid #d0d7e2;
  color: #17365f;
  background: #ffffff;
}

.cart-badge {
  position: absolute;
  top: -7px;
  right: -7px;
  min-width: 20px;
  height: 20px;
  padding: 0 6px;
  border-radius: 999px;
  background: #dc3545;
  color: #fff;
  font-size: 12px;
  line-height: 20px;
  text-align: center;
  font-weight: 700;
}
</style>
