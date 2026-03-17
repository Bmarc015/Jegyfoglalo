<template>
  <article class="card match-card h-100">
    <div class="card-body text-center">
      <div class="match-time">{{ match.time }}</div>
      <div class="match-teams">
        <div class="team-side">
          <img
            v-if="match.homeLogo"
            class="team-logo"
            :src="match.homeLogo"
            :alt="`${match.homeTeam} logo`"
            @error="$emit('logo-error', $event)"
          />
          <span class="team-name">{{ match.homeTeam }}</span>
        </div>
        <span class="vs">vs</span>
        <div class="team-side">
          <img
            v-if="match.awayLogo"
            class="team-logo"
            :src="match.awayLogo"
            :alt="`${match.awayTeam} logo`"
            @error="$emit('logo-error', $event)"
          />
          <span class="team-name">{{ match.awayTeam }}</span>
        </div>
      </div>
      <div v-if="match.venue" class="match-venue">{{ match.venue }}</div>
      <RouterLink class="btn btn-sm btn-outline-primary mt-3" to="/adatok/buytickets">
        Buy Tickets
      </RouterLink>
    </div>
  </article>
</template>

<script setup>
defineProps({
  match: Object
})

defineEmits(['logo-error'])
</script>

<style scoped>
.match-card {
  border: 1px solid #dee2e6;
  border-radius: 10px;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.match-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.match-time {
  font-size: 0.9rem;
  color: #6c757d;
  font-weight: bold;
}

.match-teams {
  font-size: 1rem;
  font-weight: bold;
  margin: 10px 0;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.65rem;
  flex-wrap: wrap;
}

.team-side {
  display: inline-flex;
  align-items: center;
  gap: 0.45rem;
}

.team-logo {
  width: 36px;
  height: 36px;
  object-fit: contain;
  border-radius: 50%;
  background: #ffffff;
  border: 1px solid #dbe3ef;
  padding: 2px;
}

.team-name {
  color: #212529;
}

.vs {
  color: #6c757d;
  margin: 0 8px;
  font-size: 0.85rem;
}

.match-venue {
  font-size: 0.85rem;
  color: #6c757d;
}
</style>

