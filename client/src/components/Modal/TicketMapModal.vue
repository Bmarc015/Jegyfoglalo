<template>
  <div v-if="modelValue" class="ticket-modal" @click.self="close">
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

          <div
            ref="mapStage"
            class="ticket-map-stage"
            @mousedown="startPan"
            @mousemove="onPanMove"
            @mouseup="endPan"
            @mouseleave="endPan"
            @touchstart.passive="startPan"
            @touchmove="onPanMove"
            @touchend="endPan"
          >
            <div
              class="ticket-map-scale"
              :style="{
                transform: `translate(${panX}px, ${panY}px) scale(${zoomLevel})`,
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
                  <h4>Editing Sector: {{ selectedSector }}</h4>
                  <button class="btn-back" @click="isEditMode = false">
                    ← Back to Map
                  </button>
                </div>

                <div class="seat-grid">
                  <div
                    v-for="(seat, index) in seats"
                    :key="index"
                    class="seat-dot"
                    :class="{ 'is-active': seat.active }"
                    @click="toggleSeat(index)"
                  >
                    <span class="tooltip">Seat {{ index + 1 }}</span>
                  </div>
                </div>

                <div class="admin-grid-footer">
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
            <p class="ticket-info-hint">
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
      isMouseDown: false,
    };
  },
  computed: {
    selectedMatchText() {
      if (!this.match) return "Select your sector to continue.";
      const teams = `${this.match.homeTeam} vs ${this.match.awayTeam}`;
      const time = this.match.time ? ` · ${this.match.time}` : "";
      return `${teams}${time}`;
    },
    selectedMatchTeams() {
      if (!this.match) return "--";
      return `${this.match.homeTeam} vs ${this.match.awayTeam}`;
    },
    selectedMatchMeta() {
      if (!this.match) return "--";
      const time = this.match.time ? this.match.time : "--:--";
      const venue = this.match.venue ? this.match.venue : "Venue TBD";
      return `${time} · ${venue}`;
    },
    ...mapState(useUserLoginLogoutStore, ["item", "isLoggedIn"]),
    isAdmin() {
      // Feltételezve, hogy a store-ban a user objektumban benne van a role
      return this.item && Number(this.item.role) === 1;
    },
  },
  watch: {
    modelValue(value) {
      if (value) {
        this.resetView();
      }
      document.body.style.overflow = value ? "hidden" : "";
    },
  },
  beforeUnmount() {
    document.body.style.overflow = "";
  },
  methods: {
    close() {
      this.$emit("update:modelValue", false);
    },
    resetView() {
      this.zoomLevel = 1;
      this.panX = 0;
      this.panY = 0;
    },
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
      const point = this.getEventPoint(event);
      this.isPanning = true;
      this.panStartX = point.x;
      this.panStartY = point.y;
      this.panOriginX = this.panX;
      this.panOriginY = this.panY;
    },
    onPanMove(event) {
      if (!this.isPanning) return;
      const point = this.getEventPoint(event);
      const dx = point.x - this.panStartX;
      const dy = point.y - this.panStartY;
      this.panX = this.panOriginX + dx;
      this.panY = this.panOriginY + dy;
      this.clampPan();
    },
    endPan() {
      this.isPanning = false;
    },
    clampPan() {
      const stage = this.$refs.mapStage;
      if (!stage) return;
      const rect = stage.getBoundingClientRect();
      const maxPanX = Math.max(0, (this.zoomLevel - 1) * rect.width * 0.6);
      const maxPanY = Math.max(0, (this.zoomLevel - 1) * rect.height * 0.6);
      this.panX = Math.max(-maxPanX, Math.min(maxPanX, this.panX));
      this.panY = Math.max(-maxPanY, Math.min(maxPanY, this.panY));
    },
    getEventPoint(event) {
      if (event?.touches?.[0]) {
        return { x: event.touches[0].clientX, y: event.touches[0].clientY };
      }
      return { x: event.clientX, y: event.clientY };
    },
    onSvgClick(event) {
      // 1. Megkeressük a legközelebbi olyan <g> vagy <path> elemet, aminek az ID-ja "sector-"-ral kezdődik
      const target = event.target.closest('[id^="sector-"]');

      if (target) {
        // 2. Kinyerjük a számot az ID-ból (pl. "sector-121" -> "121")
        const sectorId = target.id.replace("sector-", "");
        console.log("Sikeres kattintás! Szektor:", sectorId);

        // 3. Meghívjuk a már megírt handleSectorClick-et
        this.handleSectorClick(sectorId);
      } else {
        console.log("Nem szektorra kattintottál.");
      }
    },
    handleSectorClick(id) {
      this.selectedSector = id;

      // DEBUG: Nézzük meg, mi van a match-ben
      console.log("Aktuális match objektum:", this.match);

      if (!this.match) {
        alert("Hiba: Nincs meccs adat átadva a modálnak!");
        return;
      }

      // Ha a Laravel 'game'-ként küldte, próbáljuk meg azt is
      const game_id = this.match.id || this.match.game_id;

      if (!game_id) {
        alert("Hiba: A meccs objektumnak nincs ID-ja!");
        return;
      }

      const sector_id = id;
      if (this.isAdmin) {
        this.isEditMode = true;
        this.fetchSectorSeats(game_id, sector_id);
      }
    },
    async fetchSectorSeats(game_id, sector_id) {
      this.loading = true;
      try {
        const response = await axios.get("/api/seats-by-sector", {
          params: { game_id, sector_id },
        });

        // Itt a trükk: ha a response.data egy objektum, amiben van egy 'data' vagy 'item' kulcs,
        // akkor azt kell használni. Ha sima tömb, akkor marad az.
        const dbSeats = Array.isArray(response.data)
          ? response.data
          : response.data.data || [];

        this.seats = Array.from({ length: 750 }, (_, i) => {
          const row = Math.floor(i / 50) + 1;
          const col = (i % 50) + 1;

          // Most már a dbSeats biztosan tömb, így lefut a .some()
          const isSaved = dbSeats.some(
            (s) => Number(s.row) === row && Number(s.col) === col,
          );

          return {
            id: i,
            row: row,
            col: col,
            active: isSaved,
          };
        });
      } catch (error) {
        console.error("Hiba a betöltéskor:", error);
        this.generateGrid();
      } finally {
        this.loading = false;
      }
    },
    generateGrid() {
      // 15x50-es üres mátrix generálása
      this.seats = Array.from({ length: 750 }, (_, i) => ({
        id: i,
        active: false,
      }));
    },
    toggleSeat(index) {
      this.seats[index].active = !this.seats[index].active;
    },
    async saveLayout() {
      // 1. ELŐBB definiáljuk a változót (itt gyűjtjük össze a zöld pöttyöket)
      const selectedSeats = this.seats
        .filter((s) => s.active)
        .map((s) => ({
          row: s.row,
          col: s.col,
        }));

      // 2. CSAK UTÁNA használjuk az axios-ban
      console.log("Küldés folyamatban:", selectedSeats);

      try {
        const response = await axios.post(
          "http://localhost:8000/api/seats-save",
          {
            game_id: this.match.id,
            sector_id: this.selectedSector,
            seats: selectedSeats, // Itt már létezik a változó!
          },
        );

        alert(response.data.message || "Sikeres mentés!");
        this.isEditMode = false;
      } catch (error) {
        console.error("Mentési hiba:", error);
        // Ha a Laravel küldött hibaüzenetet, azt is írjuk ki:
        const errorMsg = error.response?.data?.error || "Hiba történt!";
        alert("Hiba: " + errorMsg);
      }
    },
  },
};
</script>

<style scoped>
.ticket-modal {
  position: fixed;
  inset: 0;
  background: rgba(6, 16, 31, 0.6);
  backdrop-filter: blur(6px);
  z-index: 2000;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0;
}

.ticket-modal-panel {
  width: 100vw;
  height: 100vh;
  background: #ffffff;
  border-radius: 0;
  box-shadow: 0 28px 60px rgba(10, 20, 40, 0.25);
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.ticket-modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1.2rem 1.5rem;
  border-bottom: 1px solid #e7edf6;
  background: #f7f9fc;
}

.ticket-modal-title {
  margin: 0;
  font-size: 1.4rem;
  color: #1a2d4d;
}

.ticket-modal-subtitle {
  margin: 0.2rem 0 0;
  color: #5a6b82;
  font-weight: 600;
}

.ticket-modal-close {
  width: 42px;
  height: 42px;
  border-radius: 12px;
  border: none;
  background: #ffffff;
  color: #1a2d4d;
  box-shadow: 0 6px 14px rgba(10, 20, 40, 0.12);
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.ticket-modal-body {
  display: grid;
  grid-template-columns: minmax(0, 1.3fr) minmax(0, 0.7fr);
  gap: 1.5rem;
  padding: 1.5rem;
  overflow: hidden;
}

.ticket-map-col {
  display: flex;
  flex-direction: column;
  gap: 0.8rem;
  min-height: 0;
}

.ticket-map-toolbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
}

.ticket-map-label {
  font-weight: 700;
  color: #1a2d4d;
}

.ticket-zoom-controls {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
}

.zoom-btn {
  border: 1px solid #c9d6ea;
  background: #ffffff;
  color: #163a6b;
  padding: 0.35rem 0.7rem;
  border-radius: 8px;
  font-weight: 600;
  min-width: 48px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.35rem;
  box-shadow: 0 4px 10px rgba(22, 58, 107, 0.08);
}

.zoom-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.ticket-map-stage {
  flex: 1;
  background: #f3f6fb;
  border-radius: 16px;
  border: 1px solid #dfe6ef;
  overflow: auto;
  position: relative;
  padding: 1rem;
  cursor: grab;
  touch-action: none;
  user-select: none;
}

.ticket-map-stage:active {
  cursor: grabbing;
}

.ticket-map-scale {
  transform-origin: center center;
  transition: transform 0.15s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 100%;
}

.ticket-map-svg {
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  user-select: none;
}

.ticket-map-svg :deep(svg) {
  width: min(900px, 100%);
  height: auto;
  display: block;
  user-select: none;
}

.ticket-map-svg :deep(text) {
  user-select: none;
}

.ticket-map-svg :deep([id^="sector-"] polygon),
.ticket-map-svg :deep([id^="sector-"] path) {
  transition:
    fill 0.15s ease,
    stroke 0.15s ease;
}

.ticket-map-svg :deep([id^="sector-"]:hover polygon),
.ticket-map-svg :deep([id^="sector-"]:hover path) {
  fill: #2f6fed;
  stroke: #ffffff;
}

.ticket-info-col {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.ticket-info-card {
  background: #ffffff;
  border-radius: 16px;
  border: 1px solid #e1e7f0;
  padding: 1.2rem;
  box-shadow: 0 10px 20px rgba(10, 20, 40, 0.08);
}

.ticket-info-card h3 {
  margin-top: 0;
  color: #1a2d4d;
}

.ticket-info-teams {
  font-weight: 700;
  font-size: 1.05rem;
  color: #163a6b;
}

.ticket-info-meta {
  color: #5a6b82;
  font-weight: 600;
}

.ticket-info-hint {
  margin-top: 1rem;
  color: #5a6b82;
}

/* Admin Rács Stílusok */
.admin-grid-container {
  background: white;
  padding: 2rem;
  border-radius: 12px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
  max-width: 95%;
}

.admin-grid-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.seat-grid {
  display: grid;
  /* 50 oszlop, 12px-es körök */
  grid-template-columns: repeat(50, 12px);
  gap: 5px;
  justify-content: center;
  background: #f0f3f7;
  padding: 20px;
  border-radius: 8px;
  border: 1px solid #d1d9e6;
}

.seat-dot {
  width: 12px;
  height: 12px;
  background-color: #cbd5e0;
  border-radius: 50%;
  cursor: pointer;
  position: relative;
  transition: all 0.2s;
}

.seat-dot:hover {
  background-color: #4a5568;
  transform: scale(1.3);
}

.seat-dot.is-active {
  background-color: #28a745; /* Zöld = kijelölt */
  box-shadow: 0 0 8px rgba(40, 167, 69, 0.6);
}

/* Tooltip az ülésszámhoz hover esetén */
.seat-dot .tooltip {
  visibility: hidden;
  position: absolute;
  bottom: 150%;
  left: 50%;
  transform: translateX(-50%);
  background: black;
  color: white;
  padding: 2px 6px;
  border-radius: 4px;
  font-size: 10px;
  white-space: nowrap;
}

.seat-dot:hover .tooltip {
  visibility: visible;
}

.btn-back {
  background: #edf2f7;
  border: 1px solid #cbd5e0;
  padding: 5px 15px;
  border-radius: 6px;
  cursor: pointer;
}

.btn-save {
  margin-top: 1.5rem;
  width: 100%;
  background: #1a2d4d;
  color: white;
  border: none;
  padding: 12px;
  border-radius: 8px;
  font-weight: bold;
  cursor: pointer;
}

.btn-save:hover {
  background: #2a3d5d;
}

@media (max-width: 992px) {
  .ticket-modal-body {
    grid-template-columns: 1fr;
  }
}
</style>
