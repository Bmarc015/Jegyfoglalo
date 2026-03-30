<template>
  <transition name="toast-pop">
    <div
      v-if="messages.length"
      class="app-toast"
      :class="{
        'app-toast-success': type === 'Success',
        'app-toast-error': type === 'Error',
      }"
      role="status"
      aria-live="polite"
    >
      <div class="toast-accent" aria-hidden="true"></div>

      <div class="toast-icon" aria-hidden="true">
        <i :class="toastIconClass"></i>
      </div>

      <div class="toast-body">
        <h6 class="toast-title m-0">{{ toastTitle }}</h6>
        <p v-for="message in messages" :key="message" class="toast-message m-0">
          {{ message }}
        </p>
      </div>

      <button type="button" class="toast-close" @click="close" aria-label="Close">
        <i class="bi bi-x-lg"></i>
      </button>
    </div>
  </transition>
</template>

<script>
import { mapState, mapActions } from "pinia";
import { useToastStore } from "@/stores/toastStore";

export default {
  computed: {
    ...mapState(useToastStore, ["messages", "type"]),
    toastTitle() {
      if (this.type === "Error") return "Error";
      return "Success";
    },
    toastIconClass() {
      if (this.type === "Error") return "bi bi-exclamation-octagon";
      return "bi bi-check-circle";
    },
  },
  methods: {
    ...mapActions(useToastStore, ["close"]),
  },
};
</script>

<style scoped>
.app-toast {
  position: fixed;
  top: calc(var(--app-menu-height, 92px) + 14px);
  right: calc(var(--app-gutter, 18px) + 6px);
  z-index: 3000;
  min-width: 380px;
  max-width: min(94vw, 520px);
  display: grid;
  grid-template-columns: 4px 34px 1fr auto;
  align-items: start;
  gap: 0.65rem;
  padding: 0.9rem 1rem;
  border-radius: 12px;
  border: 1px solid #d8e2f0;
  background: linear-gradient(180deg, #ffffff 0%, #f7fbff 100%);
  box-shadow: 0 14px 28px rgba(16, 48, 86, 0.14);
  backdrop-filter: blur(4px);
}

.toast-accent {
  width: 4px;
  border-radius: 999px;
  background: #0d6efd;
  min-height: 40px;
}

.toast-icon {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 1.2rem;
  background: #e9f1ff;
  color: #0b57d0;
}

.toast-title {
  font-weight: 700;
  color: #163a6b;
  margin-bottom: 0.15rem;
  font-size: 1.05rem;
}

.toast-message {
  color: #2b466b;
  font-size: 1rem;
  line-height: 1.4;
}

.toast-close {
  border: 0;
  background: transparent;
  color: #4b678f;
  width: 28px;
  height: 28px;
  border-radius: 8px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  transition: background-color 0.2s ease, color 0.2s ease;
}

.toast-close:hover {
  background: #eaf2ff;
  color: #163a6b;
}

.app-toast-success .toast-accent {
  background: #1f9d62;
}

.app-toast-success .toast-icon {
  background: #e9f9f1;
  color: #1f9d62;
}

.app-toast-error .toast-accent {
  background: #d14343;
}

.app-toast-error .toast-icon {
  background: #feecec;
  color: #d14343;
}

.toast-pop-enter-active,
.toast-pop-leave-active {
  transition: all 0.2s ease;
}

.toast-pop-enter-from,
.toast-pop-leave-to {
  opacity: 0;
  transform: translateY(-8px) scale(0.98);
}

@media (max-width: 768px) {
  .app-toast {
    left: calc(var(--app-gutter, 10px) + 2px);
    right: calc(var(--app-gutter, 10px) + 2px);
    max-width: none;
    min-width: 0;
  }
}
</style>
