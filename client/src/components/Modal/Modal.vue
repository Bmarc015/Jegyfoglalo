<template>
  <!-- Modal -->
  <div
    class="modal fade app-modal"
    id="modal"
    ref="modal"
    tabindex="-1"
    aria-labelledby="exampleModalLabel"
    aria-hidden="true"
  >
    <div class="modal-dialog modal-dialog-centered" :class="modalSizeClass">
      <div class="modal-content app-modal-content">
        <form
          @submit.prevent="onClickYes"
          :class="{ 'was-validated': validated }"
          novalidate
        >
          <!-- header -->
          <div class="modal-header app-modal-header">
            <h1 class="modal-title fs-5" id="exampleModalLabel">{{ title }}</h1>
            <button
              type="button"
              class="btn-close"
              @click="
                hide();
                $event.target.blur();
              "
            ></button>
          </div>
          <!-- body -->
          <div class="modal-body app-modal-body">
            <!-- Itt vannak a form elemek -->
            <slot></slot>
          </div>
          <!-- footer -->
          <div class="modal-footer app-modal-footer">
            <!-- cancel -->
            <button
              type="button"
              class="btn btn-primary app-modal-cancel"
              v-if="no"
              @click="
                hide();
                $event.target.blur();
              "
            >
              {{ no }}
            </button>
            <!-- save -->
            <button
              type="submit"
              class="btn btn-danger app-modal-confirm"
              @click="$event.target.blur()"
            >
              {{ yes }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import { Modal } from "bootstrap";
export default {
  emits: ["yesEvent"],
  props: {
    title: { type: String, default: "Modális ablak" },
    yes: { type: String, default: "Mentés" },
    no: { type: String, default: "Mégsem" },
    modalSize: { type: String, default: "" },
  },
  data() {
    return {
      modal: null,
      validated: false,
    };
  },
  mounted() {
    this.modal = new Modal(this.$refs.modal);
  },
  computed: {
    modalSizeClass() {
      return {
        "modal-sm": this.modalSize == "sm",
        "modal-lg": this.modalSize == "lg",
        "modal-xl": this.modalSize == "xl",
      };
    },
  },
  methods: {
    //Modal yes gombjának kezelése
    onClickYes(event) {
      const form = event.target;
      this.validated = true;
      // Van-e űrlap kitöltési hiba
      if (form.checkValidity() === false) {
        //hiba van az űrlapon
        console.log("Kliens oldali hiba az űrlapon");
      } else {
        //Nincs hiba az űrlapon
        // Átadunk egy függvényt (callback), amit a szülő hív meg, ha végzett
        this.$emit("yesEvent", (success) => {
          if (success) {
            this.hide();
          } else {
            // Ha success === false, nem hívunk hide()-ot, a modal nyitva marad a hibákkal
            console.log("Szerveroldali hiba, a modal marad");
          }
        });
      }
    },
    show() {
      this.modal.show();
      this.validated = false;
    },
    hide() {
      this.modal.hide();
      this.validated = false;
    },
  },
};
</script>

<style scoped>
.app-modal .modal-dialog {
  max-width: 560px;
}

.app-modal-content {
  border-radius: 16px;
  border: 1px solid #dbe3ef;
  box-shadow: 0 20px 50px rgba(15, 23, 42, 0.2);
  overflow: hidden;
}

.app-modal-header {
  background: linear-gradient(135deg, #f8fbff 0%, #e6f0ff 100%);
  border-bottom: 1px solid #dbe3ef;
  padding: 1.1rem 1.4rem;
}

.app-modal-body {
  padding: 1.35rem 1.4rem 1.45rem;
  background: #ffffff;
}

.app-modal-footer {
  background: #f5f8ff;
  border-top: 1px solid #dbe3ef;
  padding: 0.95rem 1.4rem;
  gap: 0.6rem;
}

.app-modal-confirm,
.app-modal-cancel {
  border-radius: 10px;
  min-width: 110px;
  font-weight: 600;
  box-shadow: 0 6px 14px rgba(15, 23, 42, 0.12);
}

.app-modal-confirm {
  background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
  border-color: rgba(15, 23, 42, 0.08);
}

.app-modal-cancel {
  background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
  border-color: rgba(15, 23, 42, 0.08);
}

.app-modal .modal-title {
  color: #1a2d4d;
  font-weight: 700;
}

.app-modal .btn-close {
  filter: saturate(0.6);
}

@media (max-width: 576px) {
  .app-modal .modal-dialog {
    margin: 0.75rem;
  }
}
</style>
