<template>
  <section class="auth-shell">
    <div class="auth-card">
      <div class="auth-head">
        <p class="auth-kicker mb-1">Welcome back</p>
        <h1 class="auth-title m-0">Sign In</h1>
        <p class="auth-subtitle mb-0">Access your account to buy tickets and manage your profile.</p>
      </div>

      <form @submit.prevent="handleSubmit" :class="{ 'was-validated': validated }" novalidate>
        <div class="mb-3">
          <label for="email" class="form-label">Email address</label>
          <input
            id="email"
            v-model.trim="user.email"
            type="email"
            class="form-control"
            required
          />
          <div class="invalid-feedback">Please enter a valid email address.</div>
        </div>

        <PasswordField
          class="mb-3"
          v-model="user.password"
          :label="'Password'"
          :label-id="'password'"
          :input-ref="'passwordInput'"
        />

        <div class="auth-actions">
          <button type="submit" class="btn btn-primary">Sign In</button>
          <RouterLink to="/registration" class="btn btn-outline-primary">Create account</RouterLink>
        </div>
      </form>
    </div>
  </section>
</template>

<script>
import PasswordField from "./PasswordField.vue";

class User {
  constructor(email = "", password = "") {
    this.email = email;
    this.password = password;
  }
}

export default {
  name: "UserLogin",
  components: {
    PasswordField,
  },
  data() {
    return {
      validated: false,
      user: new User(),
    };
  },
  methods: {
    handleSubmit(event) {
      const form = event.target;
      this.validated = true;

      if (form.checkValidity() === false) {
        return;
      }

      this.$emit("logIn", this.user);
    },
  },
};
</script>

<style scoped>
.auth-shell {
  display: flex;
  justify-content: center;
  padding: 1.2rem 0.8rem;
}

.auth-card {
  width: 100%;
  max-width: 500px;
  border: 1px solid #d8e2f0;
  border-radius: 14px;
  background: linear-gradient(180deg, #ffffff 0%, #f7fbff 100%);
  box-shadow: 0 14px 30px rgba(16, 48, 86, 0.12);
  padding: 1.2rem;
}

.auth-head {
  margin-bottom: 0.9rem;
}

.auth-kicker {
  font-size: 0.78rem;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: #0b57d0;
  font-weight: 700;
}

.auth-title {
  font-size: 1.7rem;
  font-weight: 800;
  color: #153a69;
}

.auth-subtitle {
  color: #516a8f;
  margin-top: 0.3rem;
}

.auth-actions {
  display: flex;
  gap: 0.6rem;
  flex-wrap: wrap;
}

.auth-actions .btn {
  min-width: 140px;
}
</style>
