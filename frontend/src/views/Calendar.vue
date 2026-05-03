<template>
  <main class="calendar-page">
    <section class="calendar-shell">
      <section class="left-panel">
        <header class="topbar">
          <button class="back-btn" @click="router.push('/dashboard')">⬅ Dashboard</button>
          <div>
            <h1>Catlendar</h1>
            <p>Nedēļas un mēneša plānotājs</p>
          </div>
        </header>

        <section class="week-grid">
          <div class="time-col"></div>

          <div v-for="day in weekDays" :key="day.date" class="week-head">
            <strong>{{ day.name }}</strong>
            <span>{{ formatShortDate(day.date) }}</span>
          </div>

          <template v-for="hour in hours" :key="hour">
            <div class="time-label">{{ hour }}:00</div>

            <div
              v-for="day in weekDays"
              :key="day.date + hour"
              class="time-cell"
              @click="openForm(day.date, hour)"
            >
              <div
                v-for="event in eventsForDayHour(day.date, hour)"
                :key="event.id"
                class="event-block"
              >
                <strong>{{ event.title }}</strong>
                <span>{{ event.time || '--:--' }}</span>
              </div>
            </div>
          </template>
        </section>
      </section>

      <aside class="right-panel">
        <div class="search-box">
          🔎 <input v-model="search" placeholder="Search" />
        </div>

        <section class="month-card">
          <div class="month-nav">
            <button @click="previousMonth">‹</button>
            <h2>{{ monthNames[currentMonth] }} {{ currentYear }}</h2>
            <button @click="nextMonth">›</button>
          </div>

          <div class="mini-calendar">
            <div class="mini-day-name">P</div>
            <div class="mini-day-name">O</div>
            <div class="mini-day-name">T</div>
            <div class="mini-day-name">C</div>
            <div class="mini-day-name">P</div>
            <div class="mini-day-name">S</div>
            <div class="mini-day-name">Sv</div>

            <button
              v-for="day in monthDays"
              :key="day.key"
              class="mini-day"
              :class="{ muted: !day.currentMonth, today: day.isToday, selected: day.date === selectedDate }"
              @click="selectDate(day.date)"
            >
              {{ day.day }}
              <span v-if="day.events.length > 0" class="dot"></span>
            </button>
          </div>
        </section>

        <section class="form-card">
          <h2>Pievienot notikumu</h2>

          <input v-model="form.title" placeholder="Nosaukums" />
          <input v-model="selectedDate" type="date" />
          <input v-model="form.time" type="time" />
          <input v-model="form.location" placeholder="Vieta" />
          <textarea v-model="form.description" placeholder="Apraksts"></textarea>

          <button class="submit-btn" @click="saveEvent">Saglabāt</button>
        </section>
      </aside>
    </section>
  </main>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const currentUser = JSON.parse(localStorage.getItem('catlendar_user') || 'null')

if (!currentUser) {
  router.push('/auth')
}

const events = ref([])
const search = ref('')

const today = new Date()
const currentYear = ref(today.getFullYear())
const currentMonth = ref(today.getMonth())
const selectedDate = ref(formatDate(today.getFullYear(), today.getMonth(), today.getDate()))

const form = ref({
  title: '',
  time: '',
  location: '',
  description: ''
})

const hours = [8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18]

const monthNames = [
  'Janvāris',
  'Februāris',
  'Marts',
  'Aprīlis',
  'Maijs',
  'Jūnijs',
  'Jūlijs',
  'Augusts',
  'Septembris',
  'Oktobris',
  'Novembris',
  'Decembris'
]

const dayNames = ['Svētdiena', 'Pirmdiena', 'Otrdiena', 'Trešdiena', 'Ceturtdiena', 'Piektdiena', 'Sestdiena']

onMounted(() => {
  fetchEvents()
})

async function fetchEvents() {
  const res = await fetch(`http://127.0.0.1:8000/api/events?user_id=${currentUser.id}`)
  events.value = await res.json()
}

function formatDate(year, month, day) {
  return `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`
}

function formatShortDate(date) {
  const [, month, day] = date.split('-')
  return `${day}.${month}`
}

const filteredEvents = computed(() => {
  if (!search.value) return events.value

  const query = search.value.toLowerCase()
  return events.value.filter((event) =>
    event.title.toLowerCase().includes(query) ||
    (event.location || '').toLowerCase().includes(query)
  )
})

const weekDays = computed(() => {
  const base = new Date(selectedDate.value)
  const day = base.getDay()
  const mondayOffset = day === 0 ? -6 : 1 - day

  const monday = new Date(base)
  monday.setDate(base.getDate() + mondayOffset)

  return Array.from({ length: 5 }).map((_, index) => {
    const dateObj = new Date(monday)
    dateObj.setDate(monday.getDate() + index)

    const date = formatDate(dateObj.getFullYear(), dateObj.getMonth(), dateObj.getDate())

    return {
      date,
      name: dayNames[dateObj.getDay()]
    }
  })
})

function eventsForDayHour(date, hour) {
  return filteredEvents.value.filter((event) => {
    const eventHour = event.time ? Number(event.time.slice(0, 2)) : 8
    return event.date === date && eventHour === hour
  })
}

const monthDays = computed(() => {
  const days = []

  const firstDay = new Date(currentYear.value, currentMonth.value, 1)
  const lastDay = new Date(currentYear.value, currentMonth.value + 1, 0)

  let startOffset = firstDay.getDay() - 1
  if (startOffset < 0) startOffset = 6

  const previousMonthLastDay = new Date(currentYear.value, currentMonth.value, 0).getDate()

  for (let i = startOffset; i > 0; i--) {
    const day = previousMonthLastDay - i + 1
    const dateObj = new Date(currentYear.value, currentMonth.value - 1, day)
    const date = formatDate(dateObj.getFullYear(), dateObj.getMonth(), dateObj.getDate())

    days.push({
      key: `prev-${date}`,
      day,
      date,
      currentMonth: false,
      isToday: isToday(date),
      events: filteredEvents.value.filter((event) => event.date === date)
    })
  }

  for (let day = 1; day <= lastDay.getDate(); day++) {
    const date = formatDate(currentYear.value, currentMonth.value, day)

    days.push({
      key: `current-${date}`,
      day,
      date,
      currentMonth: true,
      isToday: isToday(date),
      events: filteredEvents.value.filter((event) => event.date === date)
    })
  }

  while (days.length % 7 !== 0) {
    const nextDay = days.length - (startOffset + lastDay.getDate()) + 1
    const dateObj = new Date(currentYear.value, currentMonth.value + 1, nextDay)
    const date = formatDate(dateObj.getFullYear(), dateObj.getMonth(), dateObj.getDate())

    days.push({
      key: `next-${date}`,
      day: nextDay,
      date,
      currentMonth: false,
      isToday: isToday(date),
      events: filteredEvents.value.filter((event) => event.date === date)
    })
  }

  return days
})

function isToday(date) {
  const now = new Date()
  return date === formatDate(now.getFullYear(), now.getMonth(), now.getDate())
}

function selectDate(date) {
  selectedDate.value = date
}

function openForm(date, hour) {
  selectedDate.value = date
  form.value.time = `${String(hour).padStart(2, '0')}:00`
}

function previousMonth() {
  if (currentMonth.value === 0) {
    currentMonth.value = 11
    currentYear.value--
  } else {
    currentMonth.value--
  }
}

function nextMonth() {
  if (currentMonth.value === 11) {
    currentMonth.value = 0
    currentYear.value++
  } else {
    currentMonth.value++
  }
}

async function saveEvent() {
  const title = form.value.title.trim()

  if (title.length < 3) {
    alert('Nosaukumam jābūt vismaz 3 simboli!')
    return
  }

  await fetch('http://127.0.0.1:8000/api/events', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      title,
      date: selectedDate.value,
      time: form.value.time,
      location: form.value.location,
      description: form.value.description,
      done: false,
      user_id: currentUser.id
    })
  })

  form.value = {
    title: '',
    time: '',
    location: '',
    description: ''
  }

  await fetchEvents()
}
</script>

<style scoped>
.calendar-page {
  min-height: 100vh;
  padding: 42px;
  box-sizing: border-box;
  background:
    radial-gradient(circle at top left, #a78bfa 0%, transparent 35%),
    linear-gradient(135deg, #5b21b6, #7c3aed, #c084fc);
  font-family: Arial, sans-serif;
  color: #1f1147;
}

.calendar-shell {
  max-width: 1250px;
  margin: 0 auto;
  display: grid;
  grid-template-columns: 1.8fr 1fr;
  gap: 24px;
  background: rgba(255,255,255,0.88);
  border-radius: 34px;
  padding: 28px;
  box-shadow: 0 30px 80px rgba(30, 27, 75, 0.35);
}

.left-panel {
  background: rgba(255,255,255,0.72);
  border-radius: 24px;
  padding: 22px;
}

.topbar {
  display: flex;
  align-items: center;
  gap: 18px;
  margin-bottom: 22px;
}

.topbar h1 {
  margin: 0;
  font-size: 28px;
}

.topbar p {
  margin: 4px 0 0;
  color: #6d5f91;
}

button {
  border: none;
  border-radius: 14px;
  padding: 11px 14px;
  cursor: pointer;
  background: #7c3aed;
  color: white;
  font-weight: bold;
}

.back-btn {
  background: #4c1d95;
}

.week-grid {
  display: grid;
  grid-template-columns: 70px repeat(5, 1fr);
  border: 1px solid #ddd6fe;
  border-radius: 18px;
  overflow: hidden;
  background: #f5f3ff;
}

.week-head {
  padding: 16px;
  text-align: center;
  border-left: 1px solid #ddd6fe;
  background: #ede9fe;
}

.week-head span {
  display: block;
  margin-top: 5px;
  color: #7c3aed;
  font-size: 13px;
}

.time-label {
  min-height: 82px;
  padding: 12px;
  color: #7c6aa7;
  background: #faf5ff;
  border-top: 1px solid #ddd6fe;
}

.time-cell {
  min-height: 82px;
  border-left: 1px solid #ddd6fe;
  border-top: 1px solid #ddd6fe;
  padding: 8px;
  cursor: pointer;
}

.time-cell:hover {
  background: #ede9fe;
}

.event-block {
  background: white;
  border-left: 5px solid #8b5cf6;
  border-radius: 14px;
  padding: 10px;
  box-shadow: 0 10px 24px rgba(124, 58, 237, 0.18);
}

.event-block strong {
  display: block;
  font-size: 13px;
}

.event-block span {
  font-size: 12px;
  color: #6d5f91;
}

.right-panel {
  background: #6d28d9;
  border-radius: 28px;
  padding: 22px;
  color: white;
}

.search-box {
  background: #5b21b6;
  border-radius: 18px;
  padding: 12px;
  display: flex;
  gap: 8px;
  margin-bottom: 22px;
}

.search-box input {
  background: transparent;
  border: none;
  color: white;
  outline: none;
  width: 100%;
}

.search-box input::placeholder {
  color: #ddd6fe;
}

.month-card {
  margin-bottom: 22px;
}

.month-nav {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.month-nav h2 {
  font-size: 20px;
}

.month-nav button {
  background: transparent;
  font-size: 28px;
  padding: 4px 10px;
}

.mini-calendar {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 8px;
  margin-top: 16px;
}

.mini-day-name {
  text-align: center;
  font-size: 12px;
  color: #ddd6fe;
}

.mini-day {
  position: relative;
  background: transparent;
  color: white;
  padding: 10px 0;
  border-radius: 50%;
}

.mini-day:hover {
  background: rgba(255,255,255,0.18);
}

.mini-day.muted {
  opacity: 0.35;
}

.mini-day.today,
.mini-day.selected {
  background: white;
  color: #6d28d9;
}

.dot {
  position: absolute;
  bottom: 4px;
  left: 50%;
  width: 5px;
  height: 5px;
  background: #fdf2f8;
  border-radius: 50%;
  transform: translateX(-50%);
}

.form-card {
  background: white;
  color: #1f1147;
  border-radius: 24px;
  padding: 22px;
}

.form-card h2 {
  margin-top: 0;
}

input,
textarea {
  width: 100%;
  box-sizing: border-box;
  padding: 12px;
  margin-bottom: 12px;
  border: none;
  border-bottom: 1px solid #c4b5fd;
  outline: none;
  font-size: 15px;
}

textarea {
  min-height: 80px;
  resize: vertical;
}

.submit-btn {
  width: 100%;
  background: #7c3aed;
  margin-top: 8px;
}

@media (max-width: 950px) {
  .calendar-shell {
    grid-template-columns: 1fr;
  }

  .week-grid {
    grid-template-columns: 1fr;
  }

  .time-col,
  .time-label,
  .week-head {
    display: none;
  }
}
</style>