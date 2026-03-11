import { createRouter, createWebHistory } from "vue-router";
import HomeView from "@/views/HomeView.vue";
import { useUserLoginLogoutStore } from "@/stores/userLoginLogoutStore";
import { useToastStore } from "@/stores/toastStore";

//Azt nézi meg, hogy be van-e valaki jelentkezve
function checkIfNotLogged(to) {
  const storeAuth = useUserLoginLogoutStore();
  if (!storeAuth.isLoggedIn) {
    return { path: "/login", query: { redirect: to.fullPath } };
  }
}

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: "/",
      name: "home",
      component: HomeView,
      meta: {
        title: (route) => "Home Page",
      },
    },
    {
      path: "/about",
      name: "about",
      component: () => import("@/views/AboutView.vue"),
      meta: {
        title: (route) => "About Us",
      },
    },
    {
      path: "/adatok",
      name: "adatok",
      component: () => import("@/views/EmptyWrapperView.vue"),
      meta: {
        disabled: true,
        roles: [1, 2],
      },
      children: [
        {
          path: "teams",
          name: "teams",
          component: () => import("@/views/TeamView.vue"),
          beforeEnter: [checkIfNotLogged],
          meta: {
            title: (route) => "Teams",
            roles: [1,2],
          },
        },
        {
          path: "buytickets",
          name: "buytickets",
          component: () => import("@/views/BuyTicketsView.vue"),
          beforeEnter: [checkIfNotLogged],
          meta: {
            title: (route) => "Buy Tickets",
            roles: [1,2],
          },
        },
        {
          path: "games",
          name: "games",
          component: () => import("@/views/GamesView.vue"),
          beforeEnter: [checkIfNotLogged],
          meta: {
            title: (route) => "Games",
            roles: [1,2],
          },
        },
        {
          path: "student",
          name: "student",
          component: () => import("@/views/StudentView.vue"),
          beforeEnter: [checkIfNotLogged],
          meta: {
            title: (route) => "Tanuló",
            roles: [1, 2],
          },
        },
        {
          path: "users",
          name: "users",
          component: () => import("@/views/UsersView.vue"),
          beforeEnter: [checkIfNotLogged],
          meta: {
            title: (route) => "Users",
            roles: [1],
          },
        },
      ],
    },
    {
      path: "/login",
      name: "login",
      component: () => import("@/views/LoginView.vue"),
      meta: {
        title: (route) => "Login",
      },
    },
    {
      path: "/registration",
      name: "registration",
      component: () => import("@/views/RegistrationView.vue"),
      meta: {
        title: (route) => "Regisztráció",
      },
    },
    {
      path: "/profile",
      name: "profile",
      component: () => import("@/views/ProfileView.vue"),
      beforeEnter: [checkIfNotLogged],
      meta: {
        title: (route) => "Profil",
        roles: [1, 2],
      },
    },
    {
      path: "/:pathMatch(.*)*",
      name: "NotFound",
      component: () => import("@/views/404.vue"),
      meta: {
        title: (route) => "404",
      },
    },
  ],
});

router.beforeEach((to, from, next) => {
 
  document.title = "Iskola - " + to.meta.title(to);
  //mehetsz tovább az oldalra

  // Megkeressük az összes meta.roles beállítást az útvonal láncban
  // (A to.matched azért jó, mert ha a szülő védett, az egész ág védett lesz)
  const requiredRoles = to.meta.roles;
  
  const userStore = useUserLoginLogoutStore();
  // Használjuk a már megismert logikát
  if (userStore.canAccess(requiredRoles)) {
    // 1. eset: Van joga (vagy publikus), mehet tovább
    next();
  } else {
    // 2. eset: Nincs joga
    if (!userStore.isLoggedIn) {
      // Ha nincs belépve, küldjük a loginra
      next({ path: "/login", query: { redirect: to.fullPath } });
    } else {
      // Ha be van lépve, de ehhez nincs joga (pl. diák admin oldalra téved)
      // Küldjük a főoldalra vagy egy "Nincs jogosultság" oldalra
      //alert("Nincs jogosultságod az oldal megtekintéséhez!");
      useToastStore().messages.push("Ehhez az oldalhoz nincs jogod!");
      useToastStore().show("Error");
      next("/");
    }
  }

  // next();
});

export default router;

