<template>
  <div v-if="modelValue" class="ticket-modal" @click.self="close">
    <div class="ticket-modal-panel" role="dialog" aria-modal="true" aria-label="Ticket purchase">
      <header class="ticket-modal-header">
        <div>
          <h2 class="ticket-modal-title">Choose Your Seat</h2>
          <p class="ticket-modal-subtitle">{{ selectedMatchText }}</p>
        </div>
        <button class="ticket-modal-close" type="button" @click="close" aria-label="Close">
          <i class="bi bi-x-lg"></i>
        </button>
      </header>

      <div class="ticket-modal-body">
        <section class="ticket-map-col">
          <div class="ticket-map-toolbar">
            <span class="ticket-map-label">Sector Map</span>
            <div class="ticket-zoom-controls">
              <button type="button" class="zoom-btn" @click="zoomOut" :disabled="zoomLevel <= minZoom">
                <i class="bi bi-dash-lg"></i>
              </button>
              <button type="button" class="zoom-btn" @click="resetZoom">
                Reset
              </button>
              <button type="button" class="zoom-btn" @click="zoomIn" :disabled="zoomLevel >= maxZoom">
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
              :style="{ transform: `translate(${panX}px, ${panY}px) scale(${zoomLevel})` }"
            >
              <div class="ticket-map-svg" v-html="stadiumMapRaw"></div>
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
      this.zoomLevel = Math.min(this.maxZoom, +(this.zoomLevel + 0.2).toFixed(2));
      this.$nextTick(() => this.clampPan());
    },
    zoomOut() {
      this.zoomLevel = Math.max(this.minZoom, +(this.zoomLevel - 0.2).toFixed(2));
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
  transition: fill 0.15s ease, stroke 0.15s ease;
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

@media (max-width: 992px) {
  .ticket-modal-body {
    grid-template-columns: 1fr;
  }
}
</style>
