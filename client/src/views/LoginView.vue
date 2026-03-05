<template>
  <div>
    <UserLogin @logIn="loginHandler" />
  </div>
</template>

<script>
import { mapActions } from "pinia";
import { useUserLoginLogoutStore } from "@/stores/userLoginLogoutStore";
import UserLogin from "@/components/User/UserLogin.vue";

export default {
  name: "LoginView",
  components: {
    UserLogin,
  },
  methods: {
    ...mapActions(useUserLoginLogoutStore, ["login"]),
    async loginHandler(user) {
      try {
        await this.login(user);
        const redirectTarget = this.$route.query?.redirect;
        const safeRedirect =
          typeof redirectTarget === "string" && redirectTarget.startsWith("/")
            ? redirectTarget
            : "/";
        this.$router.push(safeRedirect);
      } catch (error) {
        console.log("Login error!");
      }
    },
  },
};
</script>

<style scoped>
</style>
