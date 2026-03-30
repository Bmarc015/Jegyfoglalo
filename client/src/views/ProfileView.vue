<template>
  <div class="profile-page">
    <div class="profile-card">
      <div class="profile-head mb-3">
        <h2 class="m-0">My Profile</h2>
        <span class="profile-subtitle">Account and billing data</span>
      </div>

      <div class="profile-grid">
        <div class="mb-3">
          <label class="form-label" for="profile-name">Name</label>
          <input
            id="profile-name"
            v-model.trim="form.name"
            type="text"
            class="form-control"
            :disabled="loading"
          />
        </div>

        <div class="mb-3">
          <label class="form-label" for="profile-email">Email</label>
          <input
            id="profile-email"
            v-model.trim="form.email"
            type="email"
            class="form-control"
            :disabled="loading"
          />
        </div>

        <div class="mb-3">
          <label class="form-label" for="profile-city">City</label>
          <input
            id="profile-city"
            v-model.trim="form.billing_city"
            type="text"
            class="form-control"
            :disabled="loading"
          />
        </div>

        <div class="mb-3">
          <label class="form-label" for="profile-zip">Zip</label>
          <input
            id="profile-zip"
            v-model.trim="form.billing_zip"
            type="text"
            inputmode="numeric"
            pattern="[0-9]*"
            class="form-control"
            :class="{ 'is-invalid': zipTouched && !isZipValid }"
            :disabled="loading"
            @blur="zipTouched = true"
          />
          <div v-if="zipTouched && !isZipValid" class="invalid-feedback d-block">
            Zip code must contain only numbers.
          </div>
        </div>

        <div class="mb-3 profile-grid-span-2">
          <label class="form-label" for="profile-address">Address</label>
          <input
            id="profile-address"
            v-model.trim="form.billing_address"
            type="text"
            class="form-control"
            :disabled="loading"
          />
        </div>
      </div>

      <div class="d-flex justify-content-end gap-2">
        <button class="btn btn-outline-secondary" :disabled="loading" @click="resetForm">
          Reset
        </button>
        <button class="btn btn-primary" :disabled="loading || !isZipValid" @click="saveProfile">
          {{ loading ? "Saving..." : "Save" }}
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import { mapActions, mapState } from "pinia";
import { useUserLoginLogoutStore } from "@/stores/userLoginLogoutStore";

export default {
  name: "ProfileView",
  data() {
    return {
      form: {
        name: "",
        email: "",
        billing_city: "",
        billing_zip: "",
        billing_address: "",
      },
      zipTouched: false,
    };
  },
  computed: {
    ...mapState(useUserLoginLogoutStore, ["item", "loading"]),
    isZipValid() {
      return this.form.billing_zip === "" || /^[0-9]+$/.test(this.form.billing_zip);
    },
  },
  methods: {
    ...mapActions(useUserLoginLogoutStore, ["getMeRefresh", "updateMe"]),
    fillFromStore() {
      this.form = {
        name: this.item?.name || "",
        email: this.item?.email || "",
        billing_city: this.item?.billing_city || "",
        billing_zip: this.item?.billing_zip || "",
        billing_address: this.item?.billing_address || "",
      };
      this.zipTouched = false;
    },
    resetForm() {
      this.fillFromStore();
    },
    async saveProfile() {
      await this.updateMe({
        name: this.form.name,
        email: this.form.email,
        billing_city: this.form.billing_city,
        billing_zip: this.form.billing_zip,
        billing_address: this.form.billing_address,
      });
      await this.getMeRefresh();
      this.fillFromStore();
    },
  },
  async mounted() {
    await this.getMeRefresh();
    this.fillFromStore();
  },
};
</script>

<style scoped>
.profile-page {
  display: flex;
  justify-content: center;
  padding: 1rem;
}

.profile-card {
  width: 100%;
  max-width: 860px;
  background: linear-gradient(180deg, #ffffff 0%, #f7faff 100%);
  border: 1px solid #d8dee7;
  border-radius: 14px;
  padding: 1.1rem;
  box-shadow: 0 10px 24px rgba(15, 23, 42, 0.08);
}

.profile-head {
  display: flex;
  align-items: baseline;
  justify-content: space-between;
  gap: 0.75rem;
}

.profile-subtitle {
  color: #5b6f8e;
  font-size: 0.9rem;
}

.profile-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 0.8rem;
}

.profile-grid-span-2 {
  grid-column: span 2;
}

@media (max-width: 768px) {
  .profile-grid {
    grid-template-columns: 1fr;
  }

  .profile-grid-span-2 {
    grid-column: span 1;
  }
}
</style>
