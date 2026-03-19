<template>
  <div>
    <Modal ref="modal" :title="title" yes="Save" no="Cancel" @yesEvent="yesEventHandler">
      <div class="mb-4 row pt-2 form-row">
        <label for="teamName" class="col-form-label col-auto pt-1 pe-0 form-label">
          Team Name:
        </label>
        <div class="col form-field">
          <input
            id="teamName"
            type="text"
            class="form-control form-input"
            v-model="formItem.team_name"
            @input="clearError('team_name')"
            required
          />
          <div v-if="!serverErrors.team_name" class="invalid-feedback position-absolute">
            Team name is required
          </div>
          <div
            v-if="serverErrors.team_name"
            class="invalid-feedback position-absolute d-block"
          >
            {{ serverErrors.team_name[0] }}
          </div>
        </div>
      </div>

      <div class="mb-4 row pt-2 form-row">
        <label for="teamCity" class="col-form-label col-auto pt-1 pe-0 form-label">
          Team City:
        </label>
        <div class="col form-field">
          <input
            id="teamCity"
            type="text"
            class="form-control form-input"
            v-model="formItem.team_city"
            @input="clearError('team_city')"
            required
          />
          <div v-if="!serverErrors.team_city" class="invalid-feedback position-absolute">
            Team city is required
          </div>
          <div
            v-if="serverErrors.team_city"
            class="invalid-feedback position-absolute d-block"
          >
            {{ serverErrors.team_city[0] }}
          </div>
        </div>
      </div>
    </Modal>
  </div>
</template>

<script>
import Modal from "@/components/Modal/Modal.vue";

export default {
  emits: ["yesEventForm"],
  name: "FormTeam",
  components: {
    Modal,
  },
  props: {
    title: { type: String, default: "New Team" },
    item: { type: Object },
  },
  data() {
    return {
      formItem: { ...this.item },
      serverErrors: {},
    };
  },
  watch: {
    item(value) {
      this.formItem = { ...value };
      this.serverErrors = {};
    },
  },
  methods: {
    show() {
      this.serverErrors = {};
      this.$refs.modal.show();
    },
    hide() {
      this.$refs.modal.hide();
    },
    setServerErrors(errors) {
      this.serverErrors = errors;
    },
    clearError(field) {
      if (this.serverErrors[field]) {
        delete this.serverErrors[field];
      }
    },
    yesEventHandler(done) {
      this.$emit("yesEventForm", { item: this.formItem, done });
    },
  },
};
</script>

<style scoped>
.form-row {
  align-items: flex-start;
  gap: 0.75rem;
}

.form-label {
  font-weight: 600;
  color: #1f3a67;
}

.form-field {
  position: relative;
}

.form-input {
  border-radius: 10px;
  border: 1px solid #d8e2f0;
  box-shadow: 0 6px 14px rgba(15, 23, 42, 0.06);
  padding: 0.55rem 0.75rem;
}

.form-input:focus {
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
}

.invalid-feedback {
  font-weight: 600;
}
</style>
