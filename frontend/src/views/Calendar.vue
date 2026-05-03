<template>
  <main class="calendar-page">
    <header class="calendar-header">
      <div class="title-group">
        <button class="quiet-button" type="button" @click="router.push('/dashboard')">Notikumu statistika</button>
        <div>
          <h1>Kalendārs</h1>
          <p>{{ weekRangeLabel }}</p>
        </div>
      </div>

      <div class="header-actions">
        <button class="quiet-button" type="button" @click="previousWeek">Iepriekšējā nedēļa</button>
        <button class="primary-button" type="button" @click="goToToday">Šodien</button>
        <button class="quiet-button" type="button" @click="nextWeek">Nākamā nedēļa</button>
      </div>
    </header>

    <section class="calendar-layout">
      <section class="planner-panel">
        <div class="week-grid">
          <div class="time-spacer"></div>

          <button
            v-for="day in weekDays"
            :key="day.date"
            type="button"
            class="day-head"
            :class="{ active: day.date === selectedDate, today: day.isToday }"
            @click="selectDate(day.date)"
          >
            <span>{{ day.shortName }}</span>
            <strong>{{ day.day }}</strong>
          </button>

          <template v-for="hour in hours" :key="hour">
            <div class="time-label">{{ formatHour(hour) }}</div>

            <button
              v-for="day in weekDays"
              :key="`${day.date}-${hour}`"
              type="button"
              class="time-cell"
              :class="{ selected: day.date === selectedDate }"
              @click="openForm(day.date, hour)"
            >
              <article
                v-for="event in eventsForDayHour(day.date, hour)"
                :key="event.id"
                class="event-chip"
                :class="event.priority || 'medium'"
                :style="{ borderColor: event.color || '#2563eb' }"
              >
                <strong>{{ event.title }}</strong>
                <span>{{ normalizeTime(event.time) || formatHour(hour) }}</span>
              </article>
            </button>
          </template>
        </div>
      </section>

      <aside class="side-panel">
        <section class="tool-panel">
          <label>
            Meklēšana
            <input v-model="search" placeholder="Nosaukums, vieta vai apraksts" />
          </label>
        </section>

        <section class="tool-panel">
          <div class="month-nav">
            <button class="icon-button" type="button" aria-label="Iepriekšējais mēnesis" @click="previousMonth">‹</button>
            <h2>{{ monthNames[currentMonth] }} {{ currentYear }}</h2>
            <button class="icon-button" type="button" aria-label="Nākamais mēnesis" @click="nextMonth">›</button>
          </div>

          <div class="mini-calendar">
            <span v-for="dayName in miniDayNames" :key="dayName" class="mini-day-name">{{ dayName }}</span>

            <button
              v-for="day in monthDays"
              :key="day.key"
              type="button"
              class="mini-day"
              :class="{
                muted: !day.currentMonth,
                today: day.isToday,
                selected: day.date === selectedDate
              }"
              @click="selectDate(day.date)"
            >
              <span>{{ day.day }}</span>
              <i v-if="day.events.length > 0"></i>
            </button>
          </div>
        </section>

        <section class="tool-panel">
          <h2>{{ selectedDayTitle }}</h2>
          <div v-if="selectedDayEvents.length" class="agenda-list">
            <article v-for="event in selectedDayEvents" :key="event.id" class="agenda-item">
              <span class="agenda-time">{{ normalizeTime(event.time) || '--:--' }}</span>
              <div>
                <strong>{{ event.title }}</strong>
                <p v-if="event.location">{{ event.location }}</p>
              </div>
            </article>
          </div>
          <p v-else class="empty">Šajā dienā nav notikumu.</p>
        </section>

        <section class="tool-panel form-panel">
          <h2>Pievienot notikumu</h2>

          <label>
            Nosaukums
            <input v-model="form.title" placeholder="Notikuma nosaukums" />
          </label>

          <div class="form-row">
            <label>
              Datums
              <input v-model="selectedDate" type="date" />
            </label>
            <label>
              Laiks
              <input v-model="form.time" type="time" />
            </label>
          </div>

          <label>
            Vieta
            <input v-model="form.location" placeholder="Vieta" />
          </label>

          <label>
            Prioritāte
            <select v-model="form.priority">
              <option value="low">Zema</option>
              <option value="medium">Vidēja</option>
              <option value="high">Augsta</option>
            </select>
          </label>

          <label>
            Apraksts
            <textarea v-model="form.description" placeholder="Apraksts"></textarea>
          </label>

          <button class="primary-button full-width" type="button" @click="saveEvent">Saglabāt</button>
          <p v-if="error" class="error">{{ error }}</p>
        </section>
      </aside>
    </section>
  </main>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { clearSession, events as eventsApi, getUser } from '../services/api'

const router = useRouter()
const currentUser = getUser()

if (!currentUser) {
  router.push('/auth')
}

const events = ref([])
const search = ref('')
const error = ref('')

const today = new Date()
const currentYear = ref(today.getFullYear())
const currentMonth = ref(today.getMonth())
const selectedDate = ref(formatDate(today.getFullYear(), today.getMonth(), today.getDate()))

const form = ref(defaultForm())

const hours = Array.from({ length: 14 }, (_, index) => index + 7)
const miniDayNames = ['P', 'O', 'T', 'C', 'P', 'S', 'Sv']

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
const shortDayNames = ['Sv', 'P', 'O', 'T', 'C', 'P', 'S']

onMounted(fetchEvents)

function defaultForm() {
  return {
    title: '',
    time: '',
    location: '',
    description: '',
    priority: 'medium',
    color: '#2563eb'
  }
}

async function fetchEvents() {
  try {
    error.value = ''
    events.value = await eventsApi.getAll()
  } catch (err) {
    handleRequestError(err)
  }
}

function formatDate(year, month, day) {
  return `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`
}

function formatHour(hour) {
  return `${String(hour).padStart(2, '0')}:00`
}

function formatShortDate(date) {
  const [, month, day] = date.split('-')
  return `${day}.${month}`
}

function normalizeTime(time) {
  return time ? String(time).slice(0, 5) : ''
}

function dateFromInput(date) {
  return new Date(`${date}T12:00:00`)
}

function syncVisibleMonth(date) {
  const value = dateFromInput(date)
  currentYear.value = value.getFullYear()
  currentMonth.value = value.getMonth()
}

const filteredEvents = computed(() => {
  const query = search.value.trim().toLowerCase()

  if (!query) return events.value

  return events.value.filter((event) =>
    event.title.toLowerCase().includes(query) ||
    (event.location || '').toLowerCase().includes(query) ||
    (event.description || '').toLowerCase().includes(query)
  )
})

const weekStart = computed(() => {
  const base = dateFromInput(selectedDate.value)
  const day = base.getDay()
  const mondayOffset = day === 0 ? -6 : 1 - day
  const monday = new Date(base)
  monday.setDate(base.getDate() + mondayOffset)
  return monday
})

const weekDays = computed(() =>
  Array.from({ length: 7 }).map((_, index) => {
    const dateObj = new Date(weekStart.value)
    dateObj.setDate(weekStart.value.getDate() + index)
    const date = formatDate(dateObj.getFullYear(), dateObj.getMonth(), dateObj.getDate())

    return {
      date,
      day: dateObj.getDate(),
      name: dayNames[dateObj.getDay()],
      shortName: shortDayNames[dateObj.getDay()],
      isToday: isToday(date)
    }
  })
)

const weekRangeLabel = computed(() => {
  const first = weekDays.value[0]?.date
  const last = weekDays.value[6]?.date

  if (!first || !last) return ''
  return `${formatShortDate(first)} - ${formatShortDate(last)}`
})

const selectedDayTitle = computed(() => {
  const date = dateFromInput(selectedDate.value)
  return `${dayNames[date.getDay()]}, ${formatShortDate(selectedDate.value)}`
})

const selectedDayEvents = computed(() =>
  filteredEvents.value
    .filter((event) => event.date === selectedDate.value)
    .sort((a, b) => normalizeTime(a.time).localeCompare(normalizeTime(b.time)))
)

function eventsForDayHour(date, hour) {
  return filteredEvents.value.filter((event) => {
    const eventHour = event.time ? Number(event.time.slice(0, 2)) : 7
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
    days.push(buildMonthDay(date, day, false))
  }

  for (let day = 1; day <= lastDay.getDate(); day++) {
    const date = formatDate(currentYear.value, currentMonth.value, day)
    days.push(buildMonthDay(date, day, true))
  }

  while (days.length % 7 !== 0) {
    const nextDay = days.length - (startOffset + lastDay.getDate()) + 1
    const dateObj = new Date(currentYear.value, currentMonth.value + 1, nextDay)
    const date = formatDate(dateObj.getFullYear(), dateObj.getMonth(), dateObj.getDate())
    days.push(buildMonthDay(date, nextDay, false))
  }

  return days
})

function buildMonthDay(date, day, currentMonthValue) {
  return {
    key: `${currentMonthValue ? 'current' : 'adjacent'}-${date}`,
    day,
    date,
    currentMonth: currentMonthValue,
    isToday: isToday(date),
    events: filteredEvents.value.filter((event) => event.date === date)
  }
}

function isToday(date) {
  return date === formatDate(today.getFullYear(), today.getMonth(), today.getDate())
}

function selectDate(date) {
  selectedDate.value = date
  syncVisibleMonth(date)
}

function openForm(date, hour) {
  selectDate(date)
  form.value.time = formatHour(hour)
}

function previousWeek() {
  const date = dateFromInput(selectedDate.value)
  date.setDate(date.getDate() - 7)
  selectDate(formatDate(date.getFullYear(), date.getMonth(), date.getDate()))
}

function nextWeek() {
  const date = dateFromInput(selectedDate.value)
  date.setDate(date.getDate() + 7)
  selectDate(formatDate(date.getFullYear(), date.getMonth(), date.getDate()))
}

function goToToday() {
  selectDate(formatDate(today.getFullYear(), today.getMonth(), today.getDate()))
}

function previousMonth() {
  const date = new Date(currentYear.value, currentMonth.value - 1, 1)
  currentYear.value = date.getFullYear()
  currentMonth.value = date.getMonth()
}

function nextMonth() {
  const date = new Date(currentYear.value, currentMonth.value + 1, 1)
  currentYear.value = date.getFullYear()
  currentMonth.value = date.getMonth()
}

async function saveEvent() {
  const title = form.value.title.trim()

  if (title.length < 3) {
    alert('Nosaukumam jābūt vismaz 3 simboli!')
    return
  }

  try {
    await eventsApi.create({
      title,
      date: selectedDate.value,
      time: form.value.time || null,
      location: form.value.location || null,
      description: form.value.description || null,
      priority: form.value.priority,
      color: form.value.color,
      done: false
    })

    form.value = defaultForm()
    await fetchEvents()
  } catch (err) {
    handleRequestError(err)
  }
}

function handleRequestError(err) {
  error.value = err.message || 'Darbība neizdevās'

  if (error.value.includes('Unauthenticated')) {
    clearSession()
    router.push('/auth')
  }
}
</script>

<style scoped>
.calendar-page {
  min-height: 100vh;
  padding: 28px;
  box-sizing: border-box;
  background: #f4f7fb;
  color: #172033;
  font-family: Arial, sans-serif;
}

.calendar-header {
  max-width: 1440px;
  margin: 0 auto 18px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 18px;
}

.title-group,
.header-actions {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
}

.title-group h1 {
  margin: 0;
  font-size: 30px;
}

.title-group p {
  margin: 4px 0 0;
  color: #667085;
}

.calendar-layout {
  max-width: 1440px;
  margin: 0 auto;
  display: grid;
  grid-template-columns: minmax(0, 1fr) 360px;
  gap: 18px;
  align-items: start;
}

.planner-panel,
.tool-panel {
  background: #ffffff;
  border: 1px solid #d9e1ee;
  border-radius: 8px;
}

.planner-panel {
  overflow: hidden;
}

.week-grid {
  display: grid;
  grid-template-columns: 72px repeat(7, minmax(112px, 1fr));
  overflow-x: auto;
}

.time-spacer,
.day-head,
.time-label,
.time-cell {
  border-right: 1px solid #e4eaf3;
  border-bottom: 1px solid #e4eaf3;
}

.day-head {
  min-height: 74px;
  padding: 10px;
  background: #f8fafc;
  color: #344054;
  text-align: left;
}

.day-head span {
  display: block;
  font-size: 13px;
  color: #667085;
}

.day-head strong {
  display: block;
  margin-top: 4px;
  font-size: 24px;
}

.day-head.active {
  background: #dbeafe;
  color: #1e3a8a;
}

.day-head.today strong {
  color: #047857;
}

.time-label {
  min-height: 76px;
  padding: 10px;
  background: #fbfdff;
  color: #667085;
  font-size: 13px;
}

.time-cell {
  min-height: 76px;
  padding: 7px;
  background: #ffffff;
  text-align: left;
  vertical-align: top;
}

.time-cell:hover,
.time-cell.selected {
  background: #f1f6ff;
}

.event-chip {
  display: grid;
  gap: 4px;
  width: 100%;
  margin-bottom: 6px;
  padding: 8px;
  border-left: 4px solid #2563eb;
  border-radius: 8px;
  background: #f8fafc;
  color: #172033;
}

.event-chip strong {
  overflow-wrap: anywhere;
  font-size: 13px;
}

.event-chip span {
  color: #667085;
  font-size: 12px;
}

.event-chip.high {
  background: #fff1f2;
}

.event-chip.low {
  background: #ecfdf3;
}

.side-panel {
  display: grid;
  gap: 14px;
}

.tool-panel {
  padding: 18px;
}

.tool-panel h2 {
  margin: 0 0 12px;
  font-size: 19px;
}

label {
  display: grid;
  gap: 7px;
  color: #344054;
  font-size: 14px;
  font-weight: 700;
}

input,
textarea,
select {
  width: 100%;
  box-sizing: border-box;
  padding: 11px;
  border: 1px solid #c8d2e1;
  border-radius: 8px;
  background: #ffffff;
  color: #172033;
  font-size: 15px;
}

textarea {
  min-height: 86px;
  resize: vertical;
}

button {
  border: none;
  border-radius: 12px;
  padding: 10px 13px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 700;
}

.primary-button,
.quiet-button {
  position: relative;
  isolation: isolate;
  overflow: visible;
  --button-ear: #2563eb;
  border-radius: 18px 18px 12px 12px;
  transition: opacity 0.18s ease, background-color 0.18s ease;
}

.primary-button::before,
.primary-button::after,
.quiet-button::before,
.quiet-button::after {
  content: "";
  position: absolute;
  top: -9px;
  width: 0;
  height: 0;
  border-left: 8px solid transparent;
  border-right: 8px solid transparent;
  border-bottom: 12px solid var(--button-ear);
}

.primary-button::before,
.quiet-button::before {
  left: 18px;
  transform: rotate(-14deg);
}

.primary-button::after,
.quiet-button::after {
  right: 18px;
  transform: rotate(14deg);
}

.primary-button {
  --button-ear: #2563eb;
  background: #2563eb;
  color: #ffffff;
}

.quiet-button,
.icon-button {
  background: #e8eef9;
  color: #1f3f75;
}

.quiet-button {
  --button-ear: #e8eef9;
}

.icon-button {
  width: 38px;
  height: 38px;
  padding: 0;
  font-size: 24px;
}

.full-width {
  width: 100%;
}

button:hover {
  opacity: 0.9;
}

.month-nav {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
  margin-bottom: 12px;
}

.month-nav h2 {
  margin: 0;
  font-size: 18px;
}

.mini-calendar {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 6px;
}

.mini-day-name {
  padding: 4px 0;
  text-align: center;
  color: #667085;
  font-size: 12px;
  font-weight: 700;
}

.mini-day {
  position: relative;
  min-height: 38px;
  padding: 0;
  background: #f8fafc;
  color: #344054;
}

.mini-day.muted {
  color: #98a2b3;
  background: #fbfdff;
}

.mini-day.today {
  outline: 2px solid #047857;
}

.mini-day.selected {
  background: #2563eb;
  color: #ffffff;
}

.mini-day i {
  position: absolute;
  left: 50%;
  bottom: 5px;
  width: 5px;
  height: 5px;
  border-radius: 999px;
  background: currentColor;
  transform: translateX(-50%);
}

.agenda-list {
  display: grid;
  gap: 8px;
}

.agenda-item {
  display: grid;
  grid-template-columns: 56px 1fr;
  gap: 10px;
  padding: 10px;
  border: 1px solid #e4eaf3;
  border-radius: 8px;
  background: #fbfdff;
}

.agenda-time {
  color: #2563eb;
  font-weight: 700;
}

.agenda-item strong {
  display: block;
  overflow-wrap: anywhere;
}

.agenda-item p,
.empty {
  margin: 4px 0 0;
  color: #667085;
}

.form-panel {
  display: grid;
  gap: 12px;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px;
}

.error {
  margin: 0;
  color: #b42318;
  font-weight: 700;
}

@media (max-width: 1180px) {
  .calendar-layout {
    grid-template-columns: 1fr;
  }

  .side-panel {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  .form-panel {
    grid-column: 1 / -1;
  }
}

@media (max-width: 720px) {
  .calendar-page {
    padding: 16px;
  }

  .calendar-header,
  .title-group,
  .header-actions {
    align-items: stretch;
  }

  .calendar-header {
    flex-direction: column;
  }

  .title-group,
  .header-actions,
  .side-panel,
  .form-row {
    grid-template-columns: 1fr;
    width: 100%;
  }

  .side-panel {
    display: grid;
  }

  .header-actions button,
  .title-group button {
    width: 100%;
  }

  .week-grid {
    grid-template-columns: 58px repeat(7, 104px);
  }
}
</style>
