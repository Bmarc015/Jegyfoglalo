<template>
  <section class="auth-shell">
    <div class="auth-card">
      <div class="auth-head">
        <p class="auth-kicker mb-1">New account</p>
        <h1 class="auth-title m-0">Create Account</h1>
        <p class="auth-subtitle mb-0">Set up your profile to start booking tickets quickly.</p>
      </div>

      <form @submit.prevent="handleSubmit" :class="{ 'was-validated': validated }" novalidate>
        <div class="row g-3">
          <div class="col-12">
            <label for="userName" class="form-label">Full name <span class="required-mark">*</span></label>
            <input
              id="userName"
              v-model.trim="userName"
              type="text"
              class="form-control"
              @input="clearError('name')"
              required
            />
            <div v-if="!serverErrors.name" class="invalid-feedback">
              Please enter your name (at least 2 characters).
            </div>
            <div v-if="serverErrors.name" class="invalid-feedback d-block">
              {{ serverErrors.name[0] }}
            </div>
          </div>

          <div class="col-12">
            <label for="email" class="form-label">Email address <span class="required-mark">*</span></label>
            <input
              id="email"
              v-model.trim="email"
              type="email"
              class="form-control"
              @input="clearError('email')"
              required
            />
            <div v-if="!serverErrors.email" class="invalid-feedback">
              Please enter a valid email address.
            </div>
            <div v-if="serverErrors.email" class="invalid-feedback d-block">
              {{ serverErrors.email[0] }}
            </div>
          </div>

          <div class="col-12 col-md-6">
            <label for="billingCity" class="form-label">City</label>
            <input
              id="billingCity"
              v-model.trim="billingCity"
              type="text"
              class="form-control"
              @input="clearError('billing_city')"
            />
            <div v-if="serverErrors.billing_city" class="invalid-feedback d-block">
              {{ serverErrors.billing_city[0] }}
            </div>
          </div>

          <div class="col-12 col-md-6">
            <label for="billingZip" class="form-label">ZIP code</label>
            <input
              id="billingZip"
              v-model.trim="billingZip"
              type="text"
              class="form-control"
              inputmode="numeric"
              pattern="[0-9]*"
              @input="clearError('billing_zip')"
            />
            <div v-if="serverErrors.billing_zip" class="invalid-feedback d-block">
              {{ serverErrors.billing_zip[0] }}
            </div>
          </div>

          <div class="col-12">
            <label for="billingAddress" class="form-label">Address</label>
            <input
              id="billingAddress"
              v-model.trim="billingAddress"
              type="text"
              class="form-control"
              @input="clearError('billing_address')"
            />
            <div v-if="serverErrors.billing_address" class="invalid-feedback d-block">
              {{ serverErrors.billing_address[0] }}
            </div>
          </div>

          <div class="col-12">
            <PasswordField
              ref="pass1Comp"
              v-model="password"
              :label="'Password'"
              :required="true"
              :input-ref="'firstInput'"
              :label-id="'password'"
              :server-errors="serverErrors"
            />
          </div>

          <div class="col-12">
            <PasswordField
              ref="pass2Comp"
              v-model="confirmPassword"
              :label="'Confirm password'"
              :required="true"
              :input-ref="'confirmInput'"
              :label-id="'confirmPassword'"
              :password-error-message="passwordErrorMessage"
              :server-errors="serverErrors"
            />
          </div>
        </div>

        <p class="required-note mt-2 mb-0">Fields marked with <span class="required-mark">*</span> are required.</p>

        <div class="auth-actions mt-3">
          <button type="submit" class="btn btn-primary">Create account</button>
          <button type="button" class="btn btn-outline-secondary" @click="$router.push('/login')">
            Back to sign in
          </button>
        </div>
      </form>
    </div>
  </section>
</template>

<script>
import PasswordField from "./PasswordField.vue";

export default {
  name: "UserRegistration",
  components: {
    PasswordField,
  },
  data() {
    return {
      userName: "",
      email: "",
      billingCity: "",
      billingZip: "",
      billingAddress: "",
      password: "",
      confirmPassword: "",
      validated: false,
      passwordErrorMessage: "",
      serverErrors: {},
    };
  },
  methods: {
    validatePasswords() {
      const comp2 = this.$refs.pass2Comp;
      const input2 = comp2?.$refs[comp2.inputRef];
      if (!input2) return;

      if (this.password !== this.confirmPassword) {
        input2.setCustomValidity("Passwords do not match.");
        this.passwordErrorMessage = "Passwords do not match.";
      } else {
        input2.setCustomValidity("");
        this.passwordErrorMessage = "";
      }
    },
    handleSubmit(event) {
      this.validatePasswords();
      const form = event.target;
      this.validated = true;

      if (form.checkValidity() === false) {
        return;
      }

      const data = {
        name: this.userName,
        email: this.email,
        billing_city: this.billingCity,
        billing_zip: this.billingZip,
        billing_address: this.billingAddress,
        password: this.password,
      };

      this.$emit("createUser", {
        data,
        done: (success) => {
          if (success) {
            this.$router.push("/login");
          }
        },
      });
    },
    setServerErrors(errors) {
      this.serverErrors = errors;
    },
    clearError(field) {
      if (this.serverErrors[field]) {
        delete this.serverErrors[field];
      }
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
  max-width: 720px;
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
  min-width: 150px;
}

.required-mark {
  color: #d14343;
  font-weight: 700;
}

.required-note {
  font-size: 0.8rem;
  color: #5f7698;
}
</style>
