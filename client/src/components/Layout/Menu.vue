<template>
  <div>
    <nav class="navbar navbar-expand-md bg-white border-bottom border-dark-subtle shadow-sm " data-bs-theme="light">
      <div class="container-fluid text-center">
        <Header id="home-link" />
        <!-- <a class="navbar-brand" href="#">Navbar</a> -->
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
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
          <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-2">
            
            <li class="nav-item" v-if="hasMenuAccess('/adatok/matches')">
              <RouterLink class="nav-link" to="/adatok/matches">Matches</RouterLink>
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
                  @click="onClickLogut()"
                  title="Kijelentkezés"
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
import userLoginLogoutService from "@/api/userLoginLogoutService";
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
    ...mapState(useUserLoginLogoutStore, ['isLoggedIn','userNameWithRole'])
  },
  methods: {
    ...mapActions(useSearchStore, ["resetSearchWord", "setSearchWord"]),
    onClickSearchButton() {
      this.setSearchWord(this.searchWordInput);
    },
    ...mapActions(useUserLoginLogoutStore, ['logout']),
    hasMenuAccess(targetPath) {
      //A jogosultsági szintnek megfelelően engedélyezi, vagy tiltja a menüt
      const userStore = useUserLoginLogoutStore();
      const resolved = this.$router.resolve(targetPath);

      if (!resolved || !resolved.matched.length) return false;

      // Végigmeneteltetjük a szabályt az összes szülőn keresztül (adatok -> sports)
      // Az 'every' akkor igaz, ha minden egyes elemre igaz a feltétel
      return resolved.matched.every((route) => {
        const requiredRoles = route.meta?.roles;

        // A már meglévő Pinia getterünket hívjuk meg minden szinten
        return userStore.canAccess(requiredRoles);
      });
    },
    async onClickLogut(){
      try {
        await this.logout();
        this.$router.push('/');
      } catch (error) {
        console.log('Kijelentkezési hiba!');
      }

    },
  },
};
</script>

<style scoped>
/* 1. A sima .active ÉS a router által adott osztály is legyen sárga */
.nav-link.active,
.nav-link.router-link-exact-active {
  color: #0800ff !important;
  font-weight: bold;
  border-bottom: 2px solid rgb(0, 17, 255);
}


/* 2. Az "Adatok" gomb sárgítása, ha az alatta lévő listában van aktív elem */
/* Azt mondjuk: "Színezd a .nav-item-et, ha van benne aktív router-link" */
.nav-item:has(.dropdown-item.router-link-active) .nav-link.dropdown-toggle {
  color: #0800ff !important;
  font-weight: bold;
  border-bottom: 2px solid rgb(0, 21, 255);
}

/* 3. A lenyíló menüben a konkrét aktív elem (pl. Sportok) kijelölése */
.dropdown-item.router-link-active {
  /* background-color: #ffff00 !important; */
  /* color: #000 !important; */
  background-color: transparent !important; /* Levesszük a teli hátteret */
  color: #0800ff !important; /* Csak a szöveg lesz sárga */
  font-weight: bold;
}

.tight-icon {
  line-height: 1 !important;
  display: inline-flex;
  vertical-align: middle;
}

.navbar {
  position: relative;
  z-index: 1060 !important; /* A Bootstrap modalok 1050-nél kezdődnek */
}

.dropdown-menu {
  z-index: 1060 !important;
}

.home-link {
  color: black !important;
}
</style>
