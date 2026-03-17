<template>
  <div class="calendar-toolbar mb-3">
    <div class="calendar-title-wrap">
      <h5 class="m-0">Select a Week</h5>
      <span class="selected-month">{{ selectedMonthLabel }}</span>
    </div>
    <div class="calendar-picker-wrap">
      <button class="calendar-pick-btn" type="button" @click="openCalendarPicker">
        <i class="bi bi-calendar2-week me-2"></i>
        Choose Week
      </button>
      <input
        ref="weekPicker"
        type="week"
        class="day-picker-input"
        :value="selectedWeek"
        @change="onWeekChange"
      />
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const props = defineProps({
  selectedMonthLabel: String,
  selectedWeek: String
})

const emit = defineEmits(['update:week'])

const weekPicker = ref(null)

const openCalendarPicker = () => {
  const picker = weekPicker.value
  if (!picker) return
  if (typeof picker.showPicker === 'function') {
    picker.showPicker()
  } else {
    picker.click()
  }
}

const onWeekChange = (event) => {
  const selected = event?.target?.value
  if (selected) {
    emit('update:week', selected)
  }
}
</script>

<style scoped>
.calendar-title-wrap {
  display: flex;
  flex-direction: column;
  align-items: center;
  line-height: 1.2;
}

.selected-month {
  margin-top: 0.15rem;
  font-size: 0.85rem;
  color: #40638f;
  font-weight: 600;
}

.calendar-pick-btn {
  border: 1px solid #c9d6ea;
  background: linear-gradient(135deg, #f8fbff 0%, #eaf2ff 100%);
  color: #163a6b;
  border-radius: 10px;
  padding: 0.45rem 0.9rem;
  font-weight: 600;
  box-shadow: 0 4px 12px rgba(22, 58, 107, 0.12);
  transition: all 0.2s ease;
}

.calendar-pick-btn:hover {
  background: linear-gradient(135deg, #eaf2ff 0%, #dce9ff 100%);
  transform: translateY(-1px);
}

.day-picker-input {
  position: absolute;
  opacity: 0;
  width: 0;
  height: 0;
  pointer-events: none;
}
</style>

