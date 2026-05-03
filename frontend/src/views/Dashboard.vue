<template>
  <main :class="['page', { dark: darkMode }]">
    <section class="header">
      <div>
        <h1>Notikumu statistika</h1>
        <p>Pārskati notikumu kopsavilkumu, filtrus un papildu pārvaldības rīkus.</p>
      </div>

      <div class="top-actions">
        <span class="role-badge">{{ roleLabel }}</span>
        <span class="user-email">{{ currentUser?.username || currentUser?.email }}</span>
        <button class="secondary" @click="router.push('/calendar')">Atgriezties kalendārā</button>
        <button class="secondary" @click="toggleDark">
          {{ darkMode ? 'Gaišais režīms' : 'Tumšais režīms' }}
        </button>
        <button class="danger" @click="logout">Iziet</button>
      </div>
    </section>

    <section class="stats">
      <div class="card">
        <span>Visi notikumi</span>
        <strong>{{ stats.total ?? events.length }}</strong>
      </div>
      <div class="card">
        <span>Šodien</span>
        <strong>{{ stats.today ?? 0 }}</strong>
      </div>
      <div class="card">
        <span>Gaidāmie</span>
        <strong>{{ stats.upcoming ?? 0 }}</strong>
      </div>
      <div class="card">
        <span>Izpildīti</span>
        <strong>{{ stats.done ?? 0 }}</strong>
      </div>
    </section>

    <section class="work-grid">
      <section class="form-card">
        <h2>{{ editingId ? 'Rediģēt notikumu' : 'Pievienot notikumu' }}</h2>

        <div class="form-grid">
          <label>
            Nosaukums
            <input v-model="form.title" placeholder="Piemēram, konsultācija" />
          </label>
          <label>
            Datums
            <input v-model="form.date" type="date" />
          </label>
          <label>
            Laiks
            <input v-model="form.time" type="time" />
          </label>
          <label>
            Vieta
            <input v-model="form.location" placeholder="Piemēram, Rīga" />
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
            Krāsa
            <input v-model="form.color" type="color" />
          </label>
        </div>

        <label>
          Apraksts
          <textarea v-model="form.description" placeholder="Īss notikuma apraksts"></textarea>
        </label>

        <div class="buttons">
          <button @click="saveEvent">{{ editingId ? 'Saglabāt' : 'Pievienot' }}</button>
          <button class="secondary" @click="clearForm">Notīrīt</button>
        </div>
      </section>

      <section class="summary-card">
        <h2>Kopsavilkums</h2>
        <div class="summary-row">
          <span>Neizpildīti</span>
          <strong>{{ stats.open ?? 0 }}</strong>
        </div>
        <div class="summary-row">
          <span>Augsta prioritāte</span>
          <strong>{{ stats.by_priority?.high ?? 0 }}</strong>
        </div>
        <div class="summary-row">
          <span>Vidēja prioritāte</span>
          <strong>{{ stats.by_priority?.medium ?? 0 }}</strong>
        </div>
        <div class="summary-row">
          <span>Zema prioritāte</span>
          <strong>{{ stats.by_priority?.low ?? 0 }}</strong>
        </div>

        <div v-if="monthStats.length" class="month-list">
          <h3>Notikumi pa mēnešiem</h3>
          <div v-for="item in monthStats" :key="item.month" class="month-row">
            <span>{{ item.month }}</span>
            <div>
              <span :style="{ width: `${item.width}%` }"></span>
            </div>
            <strong>{{ item.total }}</strong>
          </div>
        </div>
      </section>
    </section>

    <section class="tools">
      <input v-model="filters.search" placeholder="Meklēt pēc nosaukuma, vietas, apraksta vai lietotāja" @keyup.enter="fetchEvents" />
      <input v-model="filters.date_from" type="date" title="No datuma" />
      <input v-model="filters.date_to" type="date" title="Līdz datumam" />
      <select v-model="filters.priority">
        <option value="">Visas prioritātes</option>
        <option value="high">Augsta</option>
        <option value="medium">Vidēja</option>
        <option value="low">Zema</option>
      </select>
      <select v-model="filters.status">
        <option value="">Visi statusi</option>
        <option value="open">Neizpildīti</option>
        <option value="done">Izpildīti</option>
      </select>
      <select v-model="filters.sort_by">
        <option value="date">Kārtot pēc datuma</option>
        <option value="time">Kārtot pēc laika</option>
        <option value="title">Kārtot pēc nosaukuma</option>
        <option value="priority">Kārtot pēc prioritātes</option>
        <option value="done">Kārtot pēc statusa</option>
      </select>
      <select v-model="filters.sort_dir">
        <option value="asc">Augoši</option>
        <option value="desc">Dilstoši</option>
      </select>
      <button @click="fetchEvents">Atlasīt</button>
      <button class="secondary" @click="resetFilters">Notīrīt filtrus</button>
    </section>

    <section class="view-switch">
      <button :class="{ active: viewMode === 'list' }" @click="viewMode = 'list'">Saraksts</button>
      <button :class="{ active: viewMode === 'calendar' }" @click="viewMode = 'calendar'">Mēnesis</button>
    </section>

    <p v-if="error" class="error">{{ error }}</p>

    <section v-if="viewMode === 'list'" class="events">
      <h2>Notikumu saraksts</h2>

      <p v-if="events.length === 0" class="empty">Nav atrasts neviens notikums.</p>

      <article v-for="event in events" :key="event.id" class="event-card">
        <div class="event-color" :style="{ backgroundColor: event.color || '#2563eb' }"></div>

        <div class="event-main">
          <h3>{{ event.title }}</h3>
          <p>{{ formatDate(event.date) }} {{ event.time || '--:--' }}</p>
          <p v-if="event.location">Vieta: {{ event.location }}</p>
          <p v-if="event.description">{{ event.description }}</p>
          <p class="meta">
            {{ priorityLabels[event.priority] || 'Vidēja prioritāte' }} ·
            {{ event.done ? 'Izpildīts' : 'Neizpildīts' }}
            <span v-if="isAdmin && event.user"> · Lietotājs: {{ event.user.username || event.user.email }}</span>
          </p>
        </div>

        <div class="event-actions">
          <button class="secondary" @click="startEdit(event)">Rediģēt</button>
          <button class="secondary" @click="toggleDone(event)">
            {{ event.done ? 'Atzīmēt kā neizpildītu' : 'Atzīmēt kā izpildītu' }}
          </button>
          <button class="danger" @click="deleteEvent(event.id)">Dzēst</button>
        </div>
      </article>
    </section>

    <section v-else class="calendar-view">
      <h2>Mēneša skats</h2>

      <div class="calendar-month">
        <div class="calendar-day-name">P</div>
        <div class="calendar-day-name">O</div>
        <div class="calendar-day-name">T</div>
        <div class="calendar-day-name">C</div>
        <div class="calendar-day-name">P</div>
        <div class="calendar-day-name">S</div>
        <div class="calendar-day-name">Sv</div>

        <div v-for="day in monthDays" :key="day.date" class="calendar-cell">
          <strong>{{ day.day }}</strong>
          <span v-for="event in day.events" :key="event.id" class="mini-event">
            {{ event.time || '--:--' }} {{ event.title }}
          </span>
        </div>
      </div>
    </section>

    <section v-if="isAdmin && events.length > 0" class="admin-zone">
      <h2>Administratora panelis</h2>
      <p>Administrators redz notikumus no visiem lietotājiem un var tos pārvaldīt.</p>
      <button class="danger" @click="deleteAllEvents">Dzēst atlasītos notikumus</button>
    </section>
  </main>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import { auth, clearSession, events as eventsApi, getUser } from '../services/api'

const router = useRouter()
const currentUser = ref(getUser())

if (!currentUser.value) {
  router.push('/auth')
}

const events = ref([])
const stats = ref({})
const error = ref('')
const editingId = ref(null)
const viewMode = ref('list')
const darkMode = ref(localStorage.getItem('dark') === 'true')

const filters = ref({
  search: '',
  date_from: '',
  date_to: '',
  priority: '',
  status: '',
  sort_by: 'date',
  sort_dir: 'asc'
})

const form = ref(defaultForm())

const priorityLabels = {
  low: 'Zema prioritāte',
  medium: 'Vidēja prioritāte',
  high: 'Augsta prioritāte'
}

const isAdmin = computed(() => currentUser.value?.role === 'admin')
const roleLabel = computed(() => (isAdmin.value ? 'Administrators' : 'Lietotājs'))

onMounted(fetchEvents)

watch(
  () => [
    filters.value.date_from,
    filters.value.date_to,
    filters.value.priority,
    filters.value.status,
    filters.value.sort_by,
    filters.value.sort_dir
  ],
  fetchEvents
)

function defaultForm() {
  return {
    title: '',
    date: '',
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
    events.value = await eventsApi.getAll(filters.value)
    stats.value = await eventsApi.stats()
  } catch (err) {
    handleRequestError(err)
  }
}

async function saveEvent() {
  const title = String(form.value.title || '').trim()

  if (title.length < 3) {
    alert('Nosaukumam jābūt vismaz 3 simboli!')
    return
  }

  if (!form.value.date) {
    alert('Izvēlies datumu!')
    return
  }

  const payload = {
    ...form.value,
    title,
    time: form.value.time || null,
    location: form.value.location || null,
    description: form.value.description || null,
    done: Boolean(form.value.done)
  }

  try {
    if (editingId.value) {
      await eventsApi.update(editingId.value, payload)
    } else {
      await eventsApi.create(payload)
    }

    clearForm()
    await fetchEvents()
  } catch (err) {
    handleRequestError(err)
  }
}

function startEdit(event) {
  editingId.value = event.id
  form.value = {
    title: event.title,
    date: event.date,
    time: normalizeTime(event.time),
    location: event.location || '',
    description: event.description || '',
    priority: event.priority || 'medium',
    color: event.color || '#2563eb',
    done: Boolean(event.done)
  }
}

function clearForm() {
  editingId.value = null
  form.value = defaultForm()
}

async function deleteEvent(id) {
  if (!confirm('Vai dzēst šo notikumu?')) return

  try {
    await eventsApi.delete(id)
    await fetchEvents()
  } catch (err) {
    handleRequestError(err)
  }
}

async function toggleDone(event) {
  try {
    await eventsApi.update(event.id, {
      title: event.title,
      date: event.date,
      time: normalizeTime(event.time),
      location: event.location,
      description: event.description,
      priority: event.priority || 'medium',
      color: event.color || '#2563eb',
      done: !event.done
    })

    await fetchEvents()
  } catch (err) {
    handleRequestError(err)
  }
}

async function deleteAllEvents() {
  if (!confirm('Vai dzēst visus pašreiz atlasītos notikumus?')) return

  try {
    for (const event of events.value) {
      await eventsApi.delete(event.id)
    }

    await fetchEvents()
  } catch (err) {
    handleRequestError(err)
  }
}

function resetFilters() {
  filters.value = {
    search: '',
    date_from: '',
    date_to: '',
    priority: '',
    status: '',
    sort_by: 'date',
    sort_dir: 'asc'
  }
}

function formatDate(date) {
  if (!date) return ''
  const [year, month, day] = date.split('-')
  return `${day}.${month}.${year}`
}

function normalizeTime(time) {
  return time ? String(time).slice(0, 5) : null
}

function toggleDark() {
  darkMode.value = !darkMode.value
  localStorage.setItem('dark', darkMode.value)
}

async function logout() {
  await auth.logout()
  router.push('/auth')
}

function handleRequestError(err) {
  error.value = err.message || 'Darbība neizdevās'

  if (error.value.includes('Unauthenticated')) {
    clearSession()
    router.push('/auth')
  }
}

const monthStats = computed(() => {
  const values = Object.entries(stats.value.by_month || {}).map(([month, total]) => ({
    month,
    total: Number(total)
  }))

  const max = Math.max(...values.map((item) => item.total), 1)

  return values.map((item) => ({
    ...item,
    width: Math.max(8, Math.round((item.total / max) * 100))
  }))
})

const monthDays = computed(() => {
  const now = new Date()
  const year = now.getFullYear()
  const month = now.getMonth()
  const firstDay = new Date(year, month, 1)
  const lastDay = new Date(year, month + 1, 0).getDate()
  const offset = firstDay.getDay() === 0 ? 6 : firstDay.getDay() - 1
  const days = []

  for (let i = 0; i < offset; i++) {
    days.push({ date: `empty-${i}`, day: '', events: [] })
  }

  for (let day = 1; day <= lastDay; day++) {
    const date = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`

    days.push({
      date,
      day,
      events: events.value.filter((event) => event.date === date)
    })
  }

  return days
})
</script>

<style scoped>
.page {
  min-height: 100vh;
  width: 100%;
  margin: 0;
  padding: 32px;
  box-sizing: border-box;
  font-family: Arial, sans-serif;
  background: #f4f7fb;
  color: #172033;
}

.dark {
  background: #111827;
  color: #f9fafb;
}

.header {
  display: flex;
  justify-content: space-between;
  gap: 24px;
  align-items: center;
  margin-bottom: 24px;
}

.header h1,
h2,
h3 {
  margin-top: 0;
}

.top-actions,
.buttons,
.event-actions,
.view-switch {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
  align-items: center;
}

.role-badge,
.user-email {
  background: #e8eef9;
  color: #1f3f75;
  border-radius: 8px;
  padding: 9px 11px;
  font-size: 14px;
}

.stats {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 14px;
  margin-bottom: 18px;
}

.card,
.form-card,
.summary-card,
.event-card,
.admin-zone,
.calendar-cell,
.calendar-view {
  background: #ffffff;
  border: 1px solid #d9e1ee;
  border-radius: 8px;
}

.dark .card,
.dark .form-card,
.dark .summary-card,
.dark .event-card,
.dark .admin-zone,
.dark .calendar-cell,
.dark .calendar-view {
  background: #1f2937;
  border-color: #374151;
}

.card {
  padding: 18px;
}

.card span {
  display: block;
  color: #667085;
  margin-bottom: 8px;
}

.card strong {
  display: block;
  font-size: 30px;
}

.work-grid {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 18px;
  margin-bottom: 18px;
}

.form-card,
.summary-card,
.admin-zone,
.calendar-view {
  padding: 22px;
}

.form-grid,
.tools {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 12px;
}

label {
  display: grid;
  gap: 7px;
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
  font-size: 15px;
  background: white;
}

textarea {
  min-height: 88px;
  margin: 12px 0;
  resize: vertical;
}

button {
  position: relative;
  isolation: isolate;
  overflow: visible;
  --button-bg: #2563eb;
  --button-ear: #2563eb;
  padding: 10px 13px;
  border: none;
  border-radius: 18px 18px 12px 12px;
  background: var(--button-bg);
  color: white;
  cursor: pointer;
  font-size: 14px;
  font-weight: 700;
  transition: opacity 0.18s ease, background-color 0.18s ease;
}

button::before,
button::after {
  content: "";
  position: absolute;
  top: -9px;
  width: 0;
  height: 0;
  border-left: 8px solid transparent;
  border-right: 8px solid transparent;
  border-bottom: 12px solid var(--button-ear);
}

button::before {
  left: 18px;
  transform: rotate(-14deg);
}

button::after {
  right: 18px;
  transform: rotate(14deg);
}

button:hover {
  opacity: 0.9;
}

.secondary {
  --button-bg: #64748b;
  --button-ear: #64748b;
}

.danger {
  --button-bg: #f05261;
  --button-ear: #f05261;
}

.tools {
  margin-bottom: 14px;
}

.view-switch {
  margin-bottom: 18px;
}

.view-switch button {
  --button-bg: #dbeafe;
  --button-ear: #dbeafe;
  color: #1e3a8a;
}

.view-switch button.active {
  --button-bg: #2563eb;
  --button-ear: #2563eb;
  color: white;
}

.summary-row,
.month-row {
  display: grid;
  grid-template-columns: 1fr auto;
  gap: 10px;
  padding: 10px 0;
  border-bottom: 1px solid #e5e7eb;
}

.month-list {
  margin-top: 20px;
}

.month-row {
  grid-template-columns: 84px 1fr auto;
  align-items: center;
}

.month-row div {
  height: 8px;
  border-radius: 8px;
  background: #e5e7eb;
  overflow: hidden;
}

.month-row div span {
  display: block;
  height: 100%;
  background: #2563eb;
}

.events h2 {
  margin-bottom: 14px;
}

.event-card {
  display: grid;
  grid-template-columns: 8px 1fr auto;
  gap: 16px;
  padding: 16px;
  margin-bottom: 12px;
}

.event-color {
  border-radius: 8px;
}

.event-main h3 {
  margin-bottom: 8px;
}

.event-main p {
  margin: 5px 0;
}

.meta,
.empty,
.error {
  color: #667085;
}

.error {
  color: #b42318;
  font-weight: 700;
}

.calendar-month {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 8px;
}

.calendar-day-name {
  text-align: center;
  font-weight: 700;
  padding: 8px;
}

.calendar-cell {
  min-height: 112px;
  padding: 10px;
}

.mini-event {
  display: block;
  margin-top: 7px;
  padding: 6px;
  border-radius: 8px;
  background: #e8eef9;
  color: #172033;
  font-size: 13px;
}

@media (max-width: 960px) {
  .header,
  .event-card {
    grid-template-columns: 1fr;
  }

  .header {
    flex-direction: column;
    align-items: stretch;
  }

  .stats,
  .work-grid,
  .form-grid,
  .tools,
  .calendar-month {
    grid-template-columns: 1fr;
  }

  .event-card {
    display: grid;
  }
}
</style>