<template>
  <div class="week-nav mb-4">
    <button type="button" class="today-btn" @click="$emit('go-today')">
      Today
    </button>

    <button type="button" class="week-arrow-btn" @click="$emit('prev-week')" aria-label="Previous week">
      <i class="bi bi-chevron-left"></i>
    </button>

    <div class="week-days d-flex justify-content-center flex-wrap gap-2">
      <div v-for="(day, index) in weekDays" :key="index">
        <div
          class="calendar-day text-center"
          :class="{ active: selectedDay === index }"
          @click="$emit('day-select', index)"
        >
          <div class="day-name">{{ day.name }}</div>
          <div class="day-date">{{ day.date }}</div>
        </div>
      </div>
    </div>

    <button type="button" class="week-arrow-btn" @click="$emit('next-week')" aria-label="Next week">
      <i class="bi bi-chevron-right"></i>
    </button>
  </div>
</template>

<script setup>
defineProps({
  weekDays: Array,
  selectedDay: Number
})

defineEmits(['day-select', 'prev-week', 'next-week', 'go-today'])
</script>

<style scoped>
.week-arrow-btn {
  width: 38px;
  height: 38px;
  border-radius: 10px;
  border: 1px solid #c9d6ea;
  background: linear-gradient(135deg, #f8fbff 0%, #eaf2ff 100%);
  color: #163a6b;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 12px rgba(22, 58, 107, 0.12);
  transition: all 0.2s ease;
}

.week-arrow-btn:hover {
  background: linear-gradient(135deg, #eaf2ff 0%, #dce9ff 100%);
  transform: translateY(-1px);
}

.today-btn {
  height: 38px;
  border-radius: 10px;
  border: 1px solid #c9d6ea;
  background: #ffffff;
  color: #163a6b;
  font-weight: 600;
  padding: 0 0.9rem;
  box-shadow: 0 4px 12px rgba(22, 58, 107, 0.08);
  transition: all 0.2s ease;
}

.today-btn:hover {
  background: #f3f8ff;
  transform: translateY(-1px);
}

.calendar-day {
  cursor: pointer;
  transition: all 0.2s ease;
  border: 2px solid #dee2e6;
  background-color: white;
  padding: 8px 16px;
  border-radius: 8px;
  min-width: 70px;
}

.calendar-day:hover {
  border-color: #0d6efd;
  background-color: #f8f9fa;
}

.calendar-day.active {
  background-color: #0d6efd;
  color: white;
  border-color: #0d6efd;
}

.day-name {
  font-size: 0.75rem;
  font-weight: bold;
  text-transform: uppercase;
}

.day-date {
  font-size: 1.1rem;
  font-weight: bold;
}
</style>

