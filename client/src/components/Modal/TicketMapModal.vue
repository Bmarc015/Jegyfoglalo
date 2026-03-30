<template>
  <div
    v-if="modelValue"
    class="ticket-modal"
    ref="modalContent"
    @click.self="close"
  >
    <div
      class="ticket-modal-panel"
      role="dialog"
      aria-modal="true"
      aria-label="Ticket purchase"
    >
      <header class="ticket-modal-header">
        <div>
          <h2 class="ticket-modal-title">Choose Your Seat</h2>
          <p class="ticket-modal-subtitle">{{ selectedMatchText }}</p>
        </div>
        <button
          class="ticket-modal-close"
          type="button"
          @click="close"
          aria-label="Close"
        >
          <i class="bi bi-x-lg"></i>
        </button>
      </header>

      <div class="ticket-modal-body">
        <section class="ticket-map-col">
          <div class="ticket-map-toolbar">
            <span class="ticket-map-label">Sector Map</span>
            <div class="ticket-zoom-controls">
              <button
                type="button"
                class="zoom-btn"
                @click="zoomOut"
                :disabled="zoomLevel <= minZoom"
              >
                <i class="bi bi-dash-lg"></i>
              </button>
              <button type="button" class="zoom-btn" @click="resetZoom">
                Reset
              </button>
              <button
                type="button"
                class="zoom-btn"
                @click="zoomIn"
                :disabled="zoomLevel >= maxZoom"
              >
                <i class="bi bi-plus-lg"></i>
              </button>
            </div>
          </div>
          <div class="ticket-map-legend">
            <span class="legend-title">Legend:</span>
            <span class="legend-item">
              <span class="legend-swatch swatch-available"></span>
              Available
            </span>
            <span class="legend-item">
              <span class="legend-swatch swatch-selected"></span>
              Selected
            </span>
            <span class="legend-item">
              <span class="legend-swatch swatch-sold"></span>
              Sold
            </span>
          </div>

          <div
            ref="mapStage"
            class="ticket-map-stage"
            :class="{ 'is-editing': isEditMode }"
            @mousedown="startPan"
            @mousemove="onPanMove"
            @mouseup="endPan"
            @mouseleave="endPan"
          >
            <div
              class="ticket-map-scale"
              :class="{ 'is-editing': isEditMode }"
              :style="{
                transform: isEditMode
                  ? 'translate(0px, 0px) scale(1)'
                  : `translate(${panX}px, ${panY}px) scale(${zoomLevel})`,
              }"
            >
              <div
                v-if="!isEditMode"
                class="ticket-map-svg"
                v-html="stadiumMapRaw"
                @click="onSvgClick($event)"
              ></div>

              <div
                v-else
                class="admin-grid-container"
                @mousedown.stop
                @mousemove.stop
              >
                <div class="admin-grid-header">
                  <h4>
                    {{ isAdmin ? "Editing Layout:" : "Selected sector:" }}
                    {{ selectedSector }}
                  </h4>
                  <button class="btn-back" @click="isEditMode = false">
                    ← Back to Map
                  </button>
                </div>

                <div
                  class="seat-grid"
                  @mousedown="isMouseDown = true"
                  @mouseup="isMouseDown = false"
                  @mouseleave="isMouseDown = false"
                >
                  <div
                    v-for="(seat, index) in seats"
                    :key="index"
                    class="seat-dot"
                    :style="{
                      backgroundColor: getSeatColor(seat),
                      display: !isAdmin && !seat.active ? 'none' : 'block',
                    }"
                    :class="{
                      'is-sold': seat.status === 2,
                      'is-selected': selectedSeatsForBooking.includes(seat.id),
                    }"
                    @mousedown="handleMouseDown(index)"
                    @mouseenter="handleMouseEnter(index)"
                    @click="!isAdmin && handleSeatClick(seat)"
                  >
                    <span class="tooltip">
                      Row {{ seat.row }}, Col {{ seat.col }}
                      {{ seat.status === 2 ? "(Sold)" : "" }}
                    </span>
                  </div>
                </div>

                <div v-if="isAdmin" class="admin-grid-footer">
                  <p class="admin-hint">
                    Tip: Click and drag to mass select/deselect seats.
                  </p>
                  <button class="btn-save" @click="saveLayout">
                    Save Sector Layout
                  </button>
                </div>
              </div>
            </div>
          </div>
        </section>

        <aside class="ticket-info-col">
          <div class="ticket-info-card">
            <h3>Match Details</h3>
            <p class="ticket-info-teams">{{ selectedMatchTeams }}</p>
            <p class="ticket-info-meta">{{ selectedMatchMeta }}</p>

            <div v-if="selectedSector" class="booking-summary">
              <hr />

              <div v-if="isAdmin" class="admin-price-editor">
                <div class="admin-header">
                  <span class="admin-badge">Admin tools</span>
                  <span class="admin-sector">Sector: {{ selectedSector }}</span>
                </div>

                <div v-if="sectorLoading" class="admin-loading">
                  Loading sector data...
                </div>
                <div v-else class="admin-form">
                  <label class="small">Sector Name:</label>
                  <input
                    v-model="sectorName"
                    class="form-control mb-2 form-control-sm"
                  />

                  <label class="small">Price (€):</label>
                  <div class="input-group input-group-sm">
                    <input
                      type="number"
                      step="0.01"
                      v-model="sectorPrice"
                      class="form-control"
                    />
                    <span class="input-group-text">EUR</span>
                    <button class="btn btn-warning" @click="saveSectorPrice">
                      Save
                    </button>
                  </div>
                </div>
                <p v-if="sectorError" class="admin-error">
                  {{ sectorError }}
                </p>
              </div>

              <div
                v-if="!isAdmin && selectedSeatsForBooking.length > 0"
                class="cart-container mt-3"
              >
                <div class="cart-header">
                  <i class="bi bi-cart-fill"></i> Your Selection
                </div>
                <div class="cart-body p-3 bg-light">
                  <div class="d-flex justify-content-between mb-1">
                    <span>Sector:</span>
                    <strong>{{ sectorName }}</strong>
                  </div>
                  <div class="d-flex justify-content-between mb-1">
                    <span>Seats:</span>
                    <span class="badge bg-primary"
                      >{{ selectedSeatsForBooking.length }}x</span
                    >
                  </div>
                  <div class="cart-seats">
                    <span class="cart-seats-label">Selected seats:</span>
                    <div class="cart-seats-list">
                      <span
                        v-for="seat in selectedSeatDetails"
                        :key="seat.id"
                        class="cart-seat-pill"
                      >
                        Row {{ seat.row }}, Col {{ seat.col }}
                      </span>
                    </div>
                  </div>
                  <div class="d-flex justify-content-between mb-2">
                    <span>Price/seat:</span>
                    <span>{{ parseFloat(sectorPrice).toFixed(2) }} €</span>
                  </div>
                  <hr />
                  <div
                    class="d-flex justify-content-between align-items-center"
                  >
                    <span class="fw-bold">Total:</span>
                    <span class="h4 m-0 text-success fw-bold">{{
                      formattedTotalPrice
                    }}</span>
                  </div>
                  <button class="btn-book-action mt-3" @click="bookTickets">
                    Complete Purchase
                  </button>
                </div>
              </div>

              <p
                v-if="!isAdmin && selectedSeatsForBooking.length === 0"
                class="ticket-info-hint mt-3"
              >
                <i class="bi bi-info-circle"></i> Click on the seats to add them
                to your cart.
              </p>
            </div>
            <p v-if="!selectedSector" class="ticket-info-hint">
              Select a sector on the map to continue. Zoom in for a closer look.
            </p>
          </div>
        </aside>
      </div>
    </div>
  </div>
</template>

<script>
import stadiumMapRaw from "@/assets/stadium-map.svg?raw";
import { mapState } from "pinia";
import { useUserLoginLogoutStore } from "@/stores/userLoginLogoutStore";
import { useToastStore } from "@/stores/toastStore";
import apiClient from "@/api/axiosClient";

export default {
  name: "TicketMapModal",
  props: {
    modelValue: { type: Boolean, default: false },
    match: { type: Object, default: null },
  },
  emits: ["update:modelValue"],
  data() {
    return {
      zoomLevel: 1,
      minZoom: 0.6,
      maxZoom: 2.5,
      panX: 0,
      panY: 0,
      isPanning: false,
      panStartX: 0,
      panStartY: 0,
      panOriginX: 0,
      panOriginY: 0,
      panRafId: null,
      panQueuedX: 0,
      panQueuedY: 0,
      inertiaRafId: null,
      velocityX: 0,
      velocityY: 0,
      lastPanX: 0,
      lastPanY: 0,
      lastPanTime: 0,
      stadiumMapRaw,
      selectedSector: null,
      isEditMode: false,
      seats: [],
      selectedSeatsForBooking: [],
      loading: false,
      sectorLoading: false,
      sectorError: "",
      isMouseDown: false,
      dragAction: true,
      sectorPrice: 0,
      sectorName: "",
    };
  },
  computed: {
    ...mapState(useUserLoginLogoutStore, ["item", "isLoggedIn"]),

    isAdmin() {
      return this.item && Number(this.item.role) === 1;
    },

    selectedMatchText() {
      if (!this.match) return "Select your sector to continue.";
      return `${this.match.homeTeam} vs ${this.match.awayTeam} · ${this.match.time || ""}`;
    },
    selectedMatchTeams() {
      return this.match
        ? `${this.match.homeTeam} vs ${this.match.awayTeam}`
        : "--";
    },
    selectedMatchMeta() {
      if (!this.match) return "--";
      return `${this.match.time || "--:--"} · ${this.match.venue || "Venue TBD"}`;
    },
    totalPrice() {
      return (
        this.selectedSeatsForBooking.length *
        (parseFloat(this.sectorPrice) || 0)
      );
    },
    formattedTotalPrice() {
      return new Intl.NumberFormat("de-DE", {
        style: "currency",
        currency: "EUR",
      }).format(this.totalPrice);
    },
    selectedSeatDetails() {
      return this.seats
        .filter((seat) => this.selectedSeatsForBooking.includes(seat.id))
        .sort((a, b) => (a.row - b.row) || (a.col - b.col));
    },
  },
  watch: {
    modelValue(value) {
      if (value) {
        this.$nextTick(() => {
          this.resetView();
        });
      }
      document.body.style.overflow = value ? "hidden" : "";
    },
  },
  methods: {
    close() {
      this.$emit("update:modelValue", false);
    },
    resetView() {
      this.zoomLevel = 1;
      this.panX = 0;
      this.panY = 0;
      this.velocityX = 0;
      this.velocityY = 0;
      if (this.inertiaRafId) {
        cancelAnimationFrame(this.inertiaRafId);
        this.inertiaRafId = null;
      }
      this.isEditMode = false;
      this.selectedSeatsForBooking = [];
      this.clearSectorHighlight();
    },

    // --- ZOOM & PAN ---
    zoomIn() {
      this.zoomLevel = Math.min(
        this.maxZoom,
        +(this.zoomLevel + 0.2).toFixed(2),
      );
    },
    zoomOut() {
      this.zoomLevel = Math.max(
        this.minZoom,
        +(this.zoomLevel - 0.2).toFixed(2),
      );
    },
    resetZoom() {
      this.zoomLevel = 1;
    },
    startPan(event) {
      if (this.isEditMode) return;
      const pt = this.getEventPoint(event);
      this.isPanning = true;
      this.panStartX = pt.x;
      this.panStartY = pt.y;
      this.panOriginX = this.panX;
      this.panOriginY = this.panY;
      this.lastPanX = this.panX;
      this.lastPanY = this.panY;
      this.lastPanTime = performance.now();
      this.velocityX = 0;
      this.velocityY = 0;
      if (this.inertiaRafId) {
        cancelAnimationFrame(this.inertiaRafId);
        this.inertiaRafId = null;
      }
    },
    onPanMove(event) {
      if (this.isEditMode || !this.isPanning) return;
      const pt = this.getEventPoint(event);
      this.panQueuedX = this.panOriginX + (pt.x - this.panStartX);
      this.panQueuedY = this.panOriginY + (pt.y - this.panStartY);
      if (this.panRafId) return;
      this.panRafId = requestAnimationFrame(() => {
        this.panX = this.panQueuedX;
        this.panY = this.panQueuedY;
        const now = performance.now();
        const dt = Math.max(16, now - this.lastPanTime);
        this.velocityX = (this.panX - this.lastPanX) / dt;
        this.velocityY = (this.panY - this.lastPanY) / dt;
        this.lastPanX = this.panX;
        this.lastPanY = this.panY;
        this.lastPanTime = now;
        this.panRafId = null;
      });
    },
    endPan() {
      this.isPanning = false;
      if (this.panRafId) {
        cancelAnimationFrame(this.panRafId);
        this.panRafId = null;
      }
      this.startInertia();
    },
    startInertia() {
      const friction = 0.92;
      const minVelocity = 0.02;
      const step = () => {
        this.velocityX *= friction;
        this.velocityY *= friction;
        this.panX += this.velocityX * 16;
        this.panY += this.velocityY * 16;
        if (
          Math.abs(this.velocityX) < minVelocity &&
          Math.abs(this.velocityY) < minVelocity
        ) {
          this.inertiaRafId = null;
          return;
        }
        this.inertiaRafId = requestAnimationFrame(step);
      };
      if (
        Math.abs(this.velocityX) < minVelocity &&
        Math.abs(this.velocityY) < minVelocity
      )
        return;
      this.inertiaRafId = requestAnimationFrame(step);
    },
    getEventPoint(e) {
      if (e?.touches?.[0])
        return { x: e.touches[0].clientX, y: e.touches[0].clientY };
      return { x: e.clientX, y: e.clientY };
    },

    // --- SZERKESZTETT onSvgClick: Most már definiálva van a target! ---
    async onSvgClick(event) {
      console.log("Kattintás történt!", event);

      // Fontos: itt definiáljuk a 'target'-et
      const targetElement = event.target.closest("path, polygon, rect, g");
      const sectorGroup =
        event.target.closest('g[id^="sector-"]') || targetElement;

      if (!targetElement) {
        console.warn("Nem szektorra kattintottál.");
        return;
      }

      // Kinyerjük az ID-t (először data-name, majd a szektor csoport ID-ja)
      let sectorId =
        targetElement.getAttribute("data-name") ||
        sectorGroup?.getAttribute?.("id") ||
        targetElement.getAttribute("id");

      if (!sectorId) {
        console.warn("Az elemnek nincs ID-ja.");
        return;
      }

      // Tisztítás: sector-132-2 -> 132
      sectorId = sectorId.replace("sector-", "").split("-")[0];
      this.selectedSector = sectorId;

      // Vizuális visszajelzés
      this.clearSectorHighlight();
      if (sectorGroup?.classList) {
        sectorGroup.classList.add("is-selected");
      } else {
        targetElement.classList.add("is-selected");
      }

      // Adatok lekérése a szerverről
      await this.fetchSectorData(sectorId);

      // Székek betöltése, ha van meccs ID
      const game_id = this.match?.id || this.match?.game_id;
      if (game_id) {
        this.isEditMode = true;
        this.fetchSectorSeats(game_id, sectorId);
      }
    },

    async fetchSectorData(sectorId) {
      this.sectorLoading = true;
      this.sectorError = "";
      try {
        const res = await apiClient.get(`/sectors/${sectorId}`);
        const sector = res?.data ?? res ?? {};
        this.sectorPrice = sector?.sector_price ?? 0;
        this.sectorName = sector?.sector_name ?? sectorId;
      } catch (e) {
        console.error("Hiba az ár lekérésekor:", e);
        this.sectorPrice = 0;
        this.sectorName = sectorId;
        this.sectorError = "Nem sikerült betölteni a szektor adatait.";
      } finally {
        this.sectorLoading = false;
      }
    },
    async saveSectorPrice() {
      try {
        if (!this.isLoggedIn) {
          const toast = useToastStore();
          toast.messages.push(
            "Bejelentkezési adat nem található a böngészőben!",
          );
          toast.show("Error");
          return;
        }

        await apiClient.patch(`/sectors/${this.selectedSector}`, {
          sector_name: this.sectorName,
          sector_price: this.sectorPrice,
        });

        {
          const toast = useToastStore();
          toast.messages.push("Sikeres mentés!");
          toast.show("Success");
        }
      } catch (error) {
        console.error("Részletes hiba:", error.response);
        if (error.response?.status === 401) {
          const toast = useToastStore();
          toast.messages.push(
            "A szerver elutasította a tokent (401 Unauthorized). Próbálj meg újra belépni!",
          );
          toast.show("Error");
        } else {
          const toast = useToastStore();
          toast.messages.push("Hiba történt a mentés során!");
          toast.show("Error");
        }
      }
    },

    highlightSelectedSector(id) {
      this.$nextTick(() => {
        const root = this.$el?.querySelector(".ticket-map-svg");
        if (!root) return;
        root
          .querySelectorAll('[id^="sector-"]')
          .forEach((node) => node.classList.remove("is-selected"));
        const selected = root.querySelector(`[id$="${id}"]`);
        if (selected) selected.classList.add("is-selected");
      });
    },

    clearSectorHighlight() {
      const root = this.$el?.querySelector(".ticket-map-svg");
      if (!root) return;
      root
        .querySelectorAll('[id*="sector-"]')
        .forEach((node) => node.classList.remove("is-selected"));
    },

    async fetchSectorSeats(game_id, sector_id) {
      this.loading = true;
      try {
        const response = await apiClient.get("/get-seats", {
          params: { game_id, sector_id },
        });
        const dbSeats = Array.isArray(response)
          ? response
          : response?.data || [];
        this.seats = Array.from({ length: 1000 }, (_, i) => {
          const row = Math.floor(i / 50) + 1;
          const col = (i % 50) + 1;
          const savedSeat = dbSeats.find(
            (s) => Number(s.row) === row && Number(s.col) === col,
          );
          return {
            id: savedSeat ? savedSeat.id : i,
            row,
            col,
            active: !!savedSeat,
            status: savedSeat ? savedSeat.status : 0,
          };
        });
      } catch (error) {
        console.error("Hiba a székek betöltésekor:", error);
      } finally {
        this.loading = false;
      }
    },

    // --- EZ HIÁNYZOTT A KÉPED ALAPJÁN ---
    getSeatColor(seat) {
      if (this.isAdmin) {
        return seat.active ? "#4CAF50" : "#e0e0e0";
      } else {
        if (!seat.active) return "transparent";
        if (seat.status === 2) return "#F44336"; // Eladott: Piros
        return this.selectedSeatsForBooking.includes(seat.id)
          ? "#2196F3"
          : "#4CAF50"; // Kijelölt: Kék, Szabad: Zöld
      }
    },

    async saveLayout() {
      const selectedSeats = this.seats
        .filter((s) => s.active)
        .map((s) => ({
          row: s.row,
          col: s.col,
        }));
      try {
        await apiClient.post("/seats-save", {
          game_id: this.match.id,
          sector_id: this.selectedSector,
          seats: selectedSeats,
        });
        {
          const toast = useToastStore();
          toast.messages.push("Layout elmentve!");
          toast.show("Success");
        }
        this.isEditMode = false;
      } catch (error) {
        const toast = useToastStore();
        toast.messages.push("Szerver hiba mentéskor!");
        toast.show("Error");
      }
    },

    handleMouseDown(index) {
      if (!this.isAdmin) return;
      this.isMouseDown = true;
      this.dragAction = !this.seats[index].active;
      this.seats[index].active = this.dragAction;
    },
    handleMouseEnter(index) {
      if (this.isAdmin && this.isMouseDown) {
        this.seats[index].active = this.dragAction;
      }
    },
    handleSeatClick(seat) {
      if (this.isAdmin || !seat.active || seat.status === 2) return;
      const pos = this.selectedSeatsForBooking.indexOf(seat.id);
      if (pos > -1) {
        this.selectedSeatsForBooking.splice(pos, 1);
      } else {
        this.selectedSeatsForBooking.push(seat.id);
      }
    },
    async bookTickets() {
      if (!this.isLoggedIn) {
        alert("Jelentkezz be!");
        return;
      }
      try {
        await apiClient.post("/tickets/book", {
          game_id: this.match.id,
          seat_ids: this.selectedSeatsForBooking,
          user_id: this.item.id,
        });
        alert("Sikeres foglalás!");
        this.selectedSeatsForBooking = [];
        this.fetchSectorSeats(this.match.id, this.selectedSector);
      } catch (error) {
        alert("Hiba történt a foglalás során.");
      }
    },
  }, // methods vége
};
</script>

<style scoped src="../../assets/TicketMapModal.css"></style>



