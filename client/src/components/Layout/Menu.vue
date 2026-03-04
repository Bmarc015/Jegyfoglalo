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
            <li class="nav-item" v-if="hasMenuAccess('/adatok/games')">
              <RouterLink class="nav-link" to="/adatok/games">Games</RouterLink>
            </li>
            <li class="nav-item" v-if="hasMenuAccess('/adatok/leagues')">
              <RouterLink class="nav-link" to="/adatok/leagues">Leagues</RouterLink>
            </li>
            <li class="nav-item" v-if="hasMenuAccess('/adatok/teams')">
              <RouterLink class="nav-link" to="/adatok/teams">Teams</RouterLink>
            </li>
            <li class="nav-item">
              <RouterLink class="nav-link" to="/about">About</RouterLink>
            </li>
            <li class="nav-item" v-if="hasMenuAccess('/adatok/users')">
              <RouterLink class="nav-link" to="/adatok/users">Users</RouterLink>
            </li>
          </ul>

          <ul class="navbar-nav mb-2 mb-lg-0">
            <li class="nav-item" v-if="!isLoggedIn">
              <RouterLink class="nav-link" to="/login">
                <i class="bi bi-person"></i> Sign In
              </RouterLink>
            </li>
            <li class="nav-item" v-if="isLoggedIn">
              <div class="d-flex align-items-center">
                <RouterLink class="nav-link" to="/userprofil">
                  <i class="bi bi-person"></i>
                  {{ userNameWithRole }}
                </RouterLink>
                <i
                  class="bi bi-box-arrow-right ms-2 my-pointer tight-icon"
                  style="font-size: 1.5rem"
                  title="Kijelentkezes"
                  @click="onClickLogut()"
                ></i>
              </div>
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
  },
  mounted() {
    this.$nextTick(() => {
      this.updateMenuHeightVar();
    });
    window.addEventListener("resize", this.updateMenuHeightVar);
  },
  beforeUnmount() {
    window.removeEventListener("resize", this.updateMenuHeightVar);
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

.tight-icon {
  line-height: 1 !important;
  display: inline-flex;
  vertical-align: middle;
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
</style>
