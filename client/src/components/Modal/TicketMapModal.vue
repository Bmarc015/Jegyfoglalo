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
                @click="onSvgClick"
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
              <p><strong>Sector:</strong> {{ selectedSector }}</p>

              <div v-if="!isAdmin">
                <div
                  v-if="selectedSeatsForBooking.length > 0"
                  class="selection-details"
                >
                  <p>
                    Selected seats:
                    <strong>{{ selectedSeatsForBooking.length }}</strong>
                  </p>
                  <button class="btn-book-action" @click="bookTickets">
                    Book Tickets Now
                  </button>
                </div>
                <p v-else class="hint-text">
                  Click on the green dots to select your seats.
                </p>
              </div>

              <div v-else class="admin-summary">
                <p>Status: <strong>Admin Editing Mode</strong></p>
              </div>
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
import axios from "axios";

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
      stadiumMapRaw,
      selectedSector: null,
      isEditMode: false,
      seats: [],
      selectedSeatsForBooking: [],
      loading: false,
      isMouseDown: false,
      dragAction: true,
    };
  },
  computed: {
    ...mapState(useUserLoginLogoutStore, ["item", "isLoggedIn"]),

    // Automatikusan eldönti a store-ból, hogy admin-e
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
  },
  watch: {
    modelValue(value) {
      if (value) {
        // Megvárjuk, amíg a Vue kirakja a HTML-t a képernyőre
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
      this.$nextTick(() => this.clampPan());
    },
    zoomOut() {
      this.zoomLevel = Math.max(
        this.minZoom,
        +(this.zoomLevel - 0.2).toFixed(2),
      );
      this.$nextTick(() => this.clampPan());
    },
    resetZoom() {
      this.zoomLevel = 1;
      this.$nextTick(() => this.clampPan());
    },
    startPan(event) {
      if (this.isEditMode) return;
      const pt = this.getEventPoint(event);
      this.isPanning = true;
      this.panStartX = pt.x;
      this.panStartY = pt.y;
      this.panOriginX = this.panX;
      this.panOriginY = this.panY;
    },
    onPanMove(event) {
      if (this.isEditMode) return;
      if (!this.isPanning) return;
      const pt = this.getEventPoint(event);
      this.panX = this.panOriginX + (pt.x - this.panStartX);
      this.panY = this.panOriginY + (pt.y - this.panStartY);
      this.clampPan();
    },
    endPan() {
      if (this.isEditMode) return;
      this.isPanning = false;
    },
    clampPan() {
      /* ... a regid ... */
    },
    getEventPoint(e) {
      if (e?.touches?.[0])
        return { x: e.touches[0].clientX, y: e.touches[0].clientY };
      return { x: e.clientX, y: e.clientY };
    },

    // --- SVG KATTINTAS ---
    onSvgClick(event) {
      const gElement = event.target.closest('g[id^="sector-"]');
      if (gElement) {
        const finalId = gElement.id.replace("sector-", "").split("-")[0];
        this.handleSectorClick(finalId);
      }
    },

    handleSectorClick(id) {
      this.selectedSector = id;
      this.highlightSelectedSector(id);
      this.panX = 0;
      this.panY = 0;
      this.zoomLevel = 1;
      const game_id = this.match?.id || this.match?.game_id;

      if (!game_id) {
        alert("Hiba: Meccs ID nem talalhato!");
        return;
      }

      this.isEditMode = true;
      this.fetchSectorSeats(game_id, id);
    },
    highlightSelectedSector(id) {
      this.$nextTick(() => {
        const root = this.$el?.querySelector(".ticket-map-svg");
        if (!root) return;
        root
          .querySelectorAll('g[id^="sector-"]')
          .forEach((node) => node.classList.remove("is-selected"));
        const selected = root.querySelector(`g[id^="sector-${id}"]`);
        if (selected) selected.classList.add("is-selected");
      });
    },
    clearSectorHighlight() {
      const root = this.$el?.querySelector(".ticket-map-svg");
      if (!root) return;
      root
        .querySelectorAll('g[id^="sector-"]')
        .forEach((node) => node.classList.remove("is-selected"));
    },

    async fetchSectorSeats(game_id, sector_id) {
      this.loading = true;
      this.seats = [];
      try {
        const response = await axios.get("/api/get-seats", {
          params: {
            game_id: game_id,
            sector_id: sector_id,
          },
        });

        const dbSeats = Array.isArray(response.data)
          ? response.data
          : response.data.data || [];

        this.seats = Array.from({ length: 1000 }, (_, i) => {
          const row = Math.floor(i / 50) + 1;
          const col = (i % 50) + 1;

          const savedSeat = dbSeats.find(
            (s) => Number(s.row) === row && Number(s.col) === col,
          );

          return {
            id: savedSeat ? savedSeat.id : i,
            row: row,
            col: col,
            active: !!savedSeat,
            status: savedSeat ? savedSeat.status : 0,
          };
        });
      } catch (error) {
        console.error("Hiba a betolteskor:", error);
      } finally {
        this.loading = false;
      }
    },

    // --- KATTINTAS A POTTYRE (Egyesitett logika) ---
    handleSeatToggle(index) {
      const seat = this.seats[index];

      if (this.isAdmin) {
        seat.active = !seat.active;
      } else {
        if (!seat.active || seat.status === 2) return;

        const pos = this.selectedSeatsForBooking.indexOf(seat.id);
        if (pos > -1) {
          this.selectedSeatsForBooking.splice(pos, 1);
        } else {
          this.selectedSeatsForBooking.push(seat.id);
        }
      }
    },

    getSeatColor(seat) {
      if (this.isAdmin) {
        return seat.active ? "#4CAF50" : "#e0e0e0";
      } else {
        if (!seat.active) return "transparent";
        if (seat.status === 2) return "#F44336";

        return this.selectedSeatsForBooking.includes(seat.id)
          ? "#2196F3"
          : "#4CAF50";
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
        await axios.post("http://localhost:8000/api/seats-save", {
          game_id: this.match.id,
          sector_id: this.selectedSector,
          seats: selectedSeats,
        });

        alert("Layout elmentve!");
        this.isEditMode = false;
      } catch (error) {
        console.error("Szerver hiba:", error.response?.data);
        alert("Hiba tortent a mentes soran! Nezd meg a konzolt.");
      }
    },

    async bookTickets() {
      if (!this.isLoggedIn) {
        alert("Kerlek, jelentkezz be a vasarlashoz!");
        return;
      }

      try {
        await axios.post("/api/tickets/book", {
          game_id: this.match.id,
          seat_ids: this.selectedSeatsForBooking,
          user_id: this.item.id,
        });

        alert("Sikeres foglalas!");
        this.selectedSeatsForBooking = [];
        this.fetchSectorSeats(this.match.id, this.selectedSector);
      } catch (error) {
        alert(
          "Hiba: " + (error.response?.data?.error || "Sikertelen foglalas"),
        );
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
      if (this.isAdmin) return;
      if (!seat.active || seat.status === 2) return;

      const pos = this.selectedSeatsForBooking.indexOf(seat.id);
      if (pos > -1) {
        this.selectedSeatsForBooking.splice(pos, 1);
      } else {
        this.selectedSeatsForBooking.push(seat.id);
      }
    },
  },
};
</script>

<style scoped src="./TicketMapModal.css"></style>




