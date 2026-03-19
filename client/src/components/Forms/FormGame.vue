<template>
  <div>
    <Modal ref="modal" :title="title" yes="Save" no="Cancel" @yesEvent="yesEventHandler">
      <div class="mb-4 row pt-2 form-row">
        <label for="teamHome" class="col-form-label col-auto pt-1 pe-0 form-label">
          Home Team:
        </label>
        <div class="col form-field">
          <select
            id="teamHome"
            class="form-select form-input"
            v-model="formItem.team_home_id"
            @change="clearError('team_home_id')"
            required
          >
            <option value="">Select team</option>
            <option v-for="team in teams" :key="team.id" :value="team.id">
              {{ team.team_name }}
            </option>
          </select>
          <div v-if="!serverErrors.team_home_id" class="invalid-feedback position-absolute">
            Home team is required
          </div>
          <div
            v-if="serverErrors.team_home_id"
            class="invalid-feedback position-absolute d-block"
          >
            {{ serverErrors.team_home_id[0] }}
          </div>
        </div>
      </div>

      <div class="mb-4 row pt-2 form-row">
        <label for="teamAway" class="col-form-label col-auto pt-1 pe-0 form-label">
          Away Team:
        </label>
        <div class="col form-field">
          <select
            id="teamAway"
            class="form-select form-input"
            v-model="formItem.team_away_id"
            @change="clearError('team_away_id')"
            required
          >
            <option value="">Select team</option>
            <option v-for="team in teams" :key="team.id" :value="team.id">
              {{ team.team_name }}
            </option>
          </select>
          <div v-if="!serverErrors.team_away_id" class="invalid-feedback position-absolute">
            Away team is required
          </div>
          <div
            v-if="serverErrors.team_away_id"
            class="invalid-feedback position-absolute d-block"
          >
            {{ serverErrors.team_away_id[0] }}
          </div>
        </div>
      </div>

      <div class="mb-4 row pt-2 form-row">
        <label for="gameDate" class="col-form-label col-auto pt-1 pe-0 form-label">
          Game Date:
        </label>
        <div class="col form-field">
          <input
            id="gameDate"
            type="datetime-local"
            class="form-control form-input"
            v-model="gameDateLocal"
            @input="clearError('game_date')"
            required
          />
          <div v-if="!serverErrors.game_date" class="invalid-feedback position-absolute">
            Game date is required
          </div>
          <div
            v-if="serverErrors.game_date"
            class="invalid-feedback position-absolute d-block"
          >
            {{ serverErrors.game_date[0] }}
          </div>
        </div>
      </div>
    </Modal>
  </div>
</template>

<script>
import Modal from "@/components/Modal/Modal.vue";
import teamService from "@/api/teamService";

export default {
  emits: ["yesEventForm"],
  name: "FormGame",
  components: {
    Modal,
  },
  props: {
    title: { type: String, default: "New game" },
    item: { type: Object },
  },
  data() {
    return {
      formItem: { ...this.item },
      teams: [],
      gameDateLocal: "",
      serverErrors: {},
    };
  },
  watch: {
    item(value) {
      this.formItem = { ...value };
      this.gameDateLocal = this.toLocalDateTime(value?.game_date);
      this.serverErrors = {};
    },
  },
  async mounted() {
    await this.loadTeams();
    this.gameDateLocal = this.toLocalDateTime(this.formItem?.game_date);
  },
  methods: {
    async loadTeams() {
      try {
        const response = await teamService.getAll();
        this.teams = Array.isArray(response?.data) ? response.data : [];
      } catch (error) {
        this.teams = [];
      }
    },
    show() {
      this.serverErrors = {};
      if (!this.teams.length) {
        this.loadTeams();
      }
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
      const payload = {
        ...this.formItem,
        team_home_id: this.formItem.team_home_id ? Number(this.formItem.team_home_id) : null,
        team_away_id: this.formItem.team_away_id ? Number(this.formItem.team_away_id) : null,
        game_date: this.toServerDateTime(this.gameDateLocal),
      };
      this.$emit("yesEventForm", { item: payload, done });
    },
    toLocalDateTime(value) {
      if (!value) return "";
      const raw = String(value);
      if (raw.includes("T")) return raw.slice(0, 16);
      if (raw.includes(" ")) return raw.replace(" ", "T").slice(0, 16);
      return "";
    },
    toServerDateTime(value) {
      if (!value) return null;
      return String(value).replace("T", " ") + ":00";
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
