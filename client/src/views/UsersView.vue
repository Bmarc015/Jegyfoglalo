<template>
  <div>
    <div class="team-header mb-3">
      <div class="team-header-left d-flex align-items-center">
      </div>

      <div class="team-header-right d-flex align-items-center">
        <div class="user-filter">
          <select v-model="selectedRole" class="form-select form-select-sm">
            <option value="all">All roles</option>
            <option value="1">Admin</option>
            <option value="2">Customer</option>
          </select>
        </div>
        <div class="user-search">
          <div class="input-group input-group-sm">
            <span class="input-group-text">
              <i class="bi bi-search"></i>
            </span>
            <input
              v-model="searchWordInput"
              type="text"
              class="form-control"
              placeholder="Search users..."
              @keyup.enter="onClickSearchButton"
            />
            <button class="btn btn-primary" type="button" @click="onClickSearchButton">
              Search
            </button>
          </div>
        </div>
        <p class="m-0 ms-2 records-pill">{{ visibleCount }} records</p>
      </div>
    </div>

    <div v-if="loading" class="users-loading-screen">
      <div class="loading-card">
        <div class="loading-spinner"></div>
        <p>Loading users...</p>
      </div>
    </div>

    <div class="user-grid" v-else-if="filteredItems.length > 0">
      <article v-for="user in filteredItems" :key="user.id" class="user-card" :class="getCardClass(user.role)">
        <div class="user-card-tools">
          <ButtonsCrud
            :id="user.id"
            :cButtonVisible="false"
            :uButtonVisible="false"
            :dButtonVisible="true"
            @delete="deleteHandler"
            @update="updateHandler"
          />
        </div>

        <div class="user-avatar">
          <span>{{ getUserInitials(user.name) }}</span>
        </div>

        <h3 class="user-name">{{ user.name }}</h3>
        <p class="user-email">
          <i class="bi bi-envelope"></i>
          <span>{{ user.email }}</span>
        </p>

        <div class="user-card-footer">
          <span class="user-role" :class="getRoleClass(user.role)">
            {{ formatRole(user.role) }}
          </span>
          <button
            type="button"
            class="user-card-action"
            @click="updateHandler(user.id)"
          >
            Edit user
          </button>
        </div>
      </article>
    </div>
    <div v-else style="width: 100px" class="m-auto">No results</div>

    <FormUser
      ref="form"
      :title="title"
      :item="item"
      @yesEventForm="yesEventFormHandler"
    />

    <ConfirmModal
      :isOpenConfirmModal="isOpenConfirmModal"
      @cancel="cancelHandler"
      @confirm="confirmHandler"
    />
  </div>
</template>

<script>
import { mapActions, mapState } from "pinia";
import { useUserStore } from "@/stores/userStore";
import { useSearchStore } from "@/stores/searchStore";
import { useToastStore } from "@/stores/toastStore";
import ConfirmModal from "@/components/Confirm/ConfirmModal.vue";
import ButtonsCrud from "@/components/Table/ButtonsCrud.vue";
import FormUser from "@/components/Forms/FormUser.vue";

export default {
  name: "UsersView",
  components: {
    ConfirmModal,
    ButtonsCrud,
    FormUser,
  },
  watch: {
    searchWord() {
      this.searchWordInput = this.searchWord;
      this.getAllSortSearch(this.sortColumn, this.sortDirection);
    },
    searchWordInput(value) {
      if (!value) {
        this.resetSearchWord();
      }
    },
  },
  data() {
    return {
      pageTitle: "Users",
      searchWordInput: "",
      selectedRole: "all",
      roleNames: {
        1: "Admin",
        2: "Customer",
        3: "User",
      },
      useCollectionStore: useUserStore,
      isOpenConfirmModal: false,
      toDeleteId: null,
      state: "r",
      title: "",
    };
  },
  computed: {
    ...mapState(useUserStore, [
      "item",
      "items",
      "loading",
      "sortColumn",
      "sortDirection",
    ]),
    ...mapState(useSearchStore, ["searchWord"]),
    filteredItems() {
      if (this.selectedRole === "all") return this.items || [];
      const roleValue = Number(this.selectedRole);
      return (this.items || []).filter((user) => Number(user.role) === roleValue);
    },
    visibleCount() {
      return this.filteredItems.length;
    },
  },
  methods: {
    ...mapActions(useUserStore, [
      "getAll",
      "getAllSortSearch",
      "getById",
      "create",
      "update",
      "delete",
      "clearItem",
    ]),
    ...mapActions(useSearchStore, ["resetSearchWord", "setSearchWord"]),
    formatRole(role) {
      return this.roleNames[role] || `Role ${role}`;
    },
    getRoleClass(role) {
      if (role === 1) return "role-admin";
      if (role === 2) return "role-customer";
      return "role-guest";
    },
    getCardClass(role) {
      if (role === 1) return "card-admin";
      if (role === 2) return "card-customer";
      return "card-guest";
    },
    getUserInitials(name) {
      if (!name) return "U";
      const parts = String(name).trim().split(/\s+/).filter(Boolean);
      const first = parts[0]?.[0] ?? "U";
      const second = parts.length > 1 ? parts[parts.length - 1][0] : "";
      return `${first}${second}`.toUpperCase();
    },
    onClickSearchButton() {
      this.setSearchWord(this.searchWordInput);
      this.getAllSortSearch(this.sortColumn, this.sortDirection);
    },
    deleteHandler(id) {
      this.state = "d";
      this.isOpenConfirmModal = true;
      this.toDeleteId = id;
    },
    updateHandler(id) {
      this.state = "u";
      this.title = "Edit User";
      this.getById(id);
      this.$refs.form.show();
      console.log("update:", id);
    },
    createHandler() {
      useToastStore().messages.push("Users cannot be created here.");
      useToastStore().show("Error");
      return;
    },
    cancelHandler() {
      console.log("cancel delete");
      this.isOpenConfirmModal = false;
      this.state = "r";
    },
    async confirmHandler() {
      try {
        await this.delete(this.toDeleteId);
      } catch (error) {}
      this.isOpenConfirmModal = false;
      this.state = "r";
    },
    async yesEventFormHandler({ item, done }) {
      try {
        if (this.state == "c") {
          await this.create(item);
        } else {
          await this.update(item.id, item);
        }
        this.state = "r";
        done(true);
      } catch (err) {
        if (err.response && err.response.status === 422) {
          this.$refs.form.setServerErrors(err.response.data.errors);
          done(false);
        } else {
          done(false);
        }
      }
    },
  },
  async mounted() {
    this.resetSearchWord();
    await this.getAll();
  },
};
</script>

<style scoped>
.team-header {
  display: flex;
  align-items: center;
  gap: 0.85rem;
  padding: 0.65rem 0.9rem;
  border: 1px solid #d8dee7;
  border-radius: 12px;
  background: linear-gradient(180deg, #ffffff 0%, #f3f6fb 100%);
  box-shadow: 0 6px 18px rgba(15, 23, 42, 0.06);
  justify-content: space-between;
}

.team-header-left {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.team-header-right {
  display: flex;
  align-items: center;
  gap: 0.6rem;
}

.user-filter .form-select {
  border-radius: 10px;
  border: 1px solid #d8e2f0;
  box-shadow: 0 6px 16px rgba(15, 23, 42, 0.08);
  font-weight: 600;
  color: #1f3a67;
}

.records-pill {
  padding: 0.2rem 0.55rem;
  border-radius: 999px;
  background: #e8f0ff;
  color: #0b57d0;
  font-weight: 600;
  font-size: 0.9rem;
}

.user-search {
  min-width: 260px;
}

.user-search :deep(.input-group) {
  border-radius: 10px;
  overflow: hidden;
  box-shadow: 0 6px 16px rgba(15, 23, 42, 0.08);
}

.user-search :deep(.input-group-text) {
  background: #ffffff;
  border: 1px solid #d8e2f0;
  border-right: 0;
  color: #0b57d0;
}

.user-search :deep(.form-control) {
  border-color: #d8e2f0;
}

.user-search :deep(.btn) {
  border-radius: 0;
}

.users-loading-screen {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 220px;
  margin-bottom: 1rem;
}

.loading-card {
  display: inline-flex;
  align-items: center;
  gap: 0.65rem;
  padding: 0.75rem 1rem;
  border-radius: 999px;
  border: 1px solid #d8e2f0;
  background: #ffffff;
  box-shadow: 0 10px 22px rgba(15, 23, 42, 0.08);
  color: #0f172a;
  font-weight: 600;
}

.loading-spinner {
  width: 18px;
  height: 18px;
  border-radius: 50%;
  border: 2px solid #c7d7f5;
  border-top-color: #0b57d0;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.user-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 300px));
  gap: 1.2rem;
  justify-content: center;
}

.user-card {
  background: linear-gradient(135deg, #ffffff 0%, #f6f8fb 100%);
  border: 1px solid #e3e9f1;
  border-radius: 18px;
  padding: 1rem 1.1rem 0.9rem;
  box-shadow: 0 10px 22px rgba(15, 23, 42, 0.08);
  position: relative;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  gap: 0.7rem;
}

.user-card::after {
  content: "";
  position: absolute;
  inset: 0;
  background: radial-gradient(circle at top right, rgba(59, 130, 246, 0.12), transparent 55%);
  pointer-events: none;
}

.user-card.card-admin {
  border-color: rgba(248, 113, 113, 0.35);
  background: linear-gradient(135deg, #ffffff 0%, #fff5f5 100%);
}

.user-card.card-admin::after {
  background: radial-gradient(circle at top right, rgba(239, 68, 68, 0.18), transparent 55%);
}

.user-card.card-customer {
  border-color: rgba(59, 130, 246, 0.35);
  background: linear-gradient(135deg, #ffffff 0%, #f2f7ff 100%);
}

.user-card.card-customer::after {
  background: radial-gradient(circle at top right, rgba(59, 130, 246, 0.18), transparent 55%);
}

.user-card.card-guest {
  border-color: rgba(100, 116, 139, 0.35);
  background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
}

.user-card.card-guest::after {
  background: radial-gradient(circle at top right, rgba(148, 163, 184, 0.2), transparent 55%);
}

.user-card-tools {
  position: absolute;
  top: 0.9rem;
  right: 0.6rem;
  z-index: 2;
}

.user-card-tools :deep(.crud-action-btn) {
  width: 34px;
  height: 34px;
  border-radius: 10px;
  border: 1px solid rgba(15, 23, 42, 0.08);
  box-shadow: 0 6px 14px rgba(15, 23, 42, 0.12);
  display: inline-flex;
  align-items: center;
  justify-content: center;
  transition: transform 120ms ease, box-shadow 120ms ease, filter 120ms ease;
}

.user-card-tools :deep(.crud-action-btn:hover) {
  transform: translateY(-1px);
  box-shadow: 0 10px 18px rgba(15, 23, 42, 0.18);
}

.user-card-tools :deep(.crud-delete) {
  background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
  color: #ffffff;
}

.user-card-tools :deep(.crud-update) {
  background: linear-gradient(135deg, #38bdf8 0%, #0ea5e9 100%);
  color: #ffffff;
}

.user-avatar {
  width: 60px;
  height: 60px;
  border-radius: 16px;
  background: #ffffff;
  border: 1px solid #e2e8f0;
  box-shadow: 0 8px 16px rgba(15, 23, 42, 0.08);
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  color: #0b57d0;
  text-transform: uppercase;
  position: relative;
  z-index: 1;
}

.card-admin .user-avatar {
  color: #b91c1c;
  border-color: rgba(248, 113, 113, 0.45);
}

.card-customer .user-avatar {
  color: #1d4ed8;
  border-color: rgba(59, 130, 246, 0.4);
}

.card-guest .user-avatar {
  color: #334155;
  border-color: rgba(148, 163, 184, 0.45);
}

.user-name {
  margin: 0;
  font-size: 1.1rem;
  font-weight: 700;
  color: #0f172a;
  position: relative;
  z-index: 1;
}

.user-email {
  display: flex;
  align-items: center;
  gap: 0.35rem;
  color: #475569;
  margin: 0;
  position: relative;
  z-index: 1;
}

.user-card-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.6rem;
  margin-top: auto;
  position: relative;
  z-index: 1;
}

.user-role {
  padding: 0.25rem 0.6rem;
  border-radius: 999px;
  font-size: 0.85rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.03em;
}

.role-admin {
  background: #fee2e2;
  color: #b91c1c;
}

.role-customer {
  background: #dbeafe;
  color: #1d4ed8;
}

.role-guest {
  background: #e2e8f0;
  color: #334155;
}

.user-card-action {
  border: 0;
  background: linear-gradient(135deg, #0d6efd 0%, #0b57d0 100%);
  color: #fff;
  padding: 0.45rem 0.85rem;
  border-radius: 10px;
  font-weight: 600;
  box-shadow: 0 6px 14px rgba(13, 110, 253, 0.25);
}

@media (max-width: 900px) {
  .team-header {
    flex-wrap: wrap;
  }

  .team-header-left,
  .team-header-right {
    width: 100%;
    justify-content: space-between;
  }

  .user-filter {
    width: 100%;
  }

  .user-search {
    width: 100%;
  }
}

@media (max-width: 600px) {
  .user-card-footer {
    flex-direction: column;
    align-items: flex-start;
  }

  .user-card-action {
    width: 100%;
    text-align: center;
  }
}
</style>
