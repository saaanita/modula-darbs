<template>
  <main :class="['page', { dark: darkMode }]">
    <section class="header">
      <div>
        <h1>Catlendar</h1>
        <p>Pievieno, rediģē, meklē un pārvaldi savus notikumus.</p>
      </div>

      <div class="top-actions">
        <select v-model="role" @change="persist">
          <option value="user">Lietotājs</option>
          <option value="admin">Administrators</option>
        </select>

      <div>
          {{ currentUser?.avatar }} {{ currentUser?.email }}
      </div>

      <button @click="router.push('/calendar')"> Kalendārs </button>
        <button class="danger" @click="logout"> Iziet</button>
      </div>
    </section>

    <section class="stats">
      <div class="card">
        <strong>{{ events.length }}</strong>
        <span>Visi notikumi</span>
      </div>

      <div class="card">
        <strong>{{ todayEvents.length }}</strong>
        <span>Šodien</span>
      </div>

      <div class="card">
        <strong>{{ upcomingEvents.length }}</strong>
        <span>Gaidāmie</span>
      </div>
    </section>

    <section class="form-card">
      <h2>{{ editingId ? 'Rediģēt notikumu' : 'Pievienot notikumu' }}</h2>

      <div class="form-grid">
        <input v-model="form.title" placeholder="Nosaukums" />
        <input v-model="form.date" type="date" required />
        <input v-model="form.time" type="time" />
        <input v-model="form.location" placeholder="Vieta" />
      </div>

      <textarea v-model="form.description" placeholder="Apraksts"></textarea>

      <div class="buttons">
        <button @click="saveEvent">
          {{ editingId ? 'Saglabāt' : 'Pievienot' }}
        </button>

        <button class="secondary" @click="clearForm">
          Notīrīt
        </button>
      </div>
    </section>

    <section class="tools">
      <input v-model="search" placeholder="Meklēt pēc nosaukuma vai vietas..." />
      <input v-model="filterDate" type="date" />

    <select v-model="avatar">
        <option value="🐱">🐱</option>
        <option value="😺">😺</option>
        <option value="😸">😸</option>
        <option value="😻">😻</option>
    </select>

      <select v-model="sortOrder">
        <option value="asc">Tuvākie pirmie</option>
        <option value="desc">Vēlākie pirmie</option>
      </select>

      <button
        v-if="role === 'admin' && filterDate"
        class="danger"
        @click="deleteByDate"
      >
        Dzēst izvēlēto datumu
      </button>
    </section>

    <button @click="toggleDark">
  {{ darkMode ? 'light' : 'dark' }}
</button>

    <section v-if="viewMode === 'list'" class="events">
      <h2>Notikumu saraksts</h2>
    
      <p v-if="filteredEvents.length === 0" class="empty">
      🐱 Te vēl nav neviena notikuma...
      </p>

      <article
        v-for="event in filteredEvents"
        :key="event.id"
        class="event-card"
      >
        <div>
          <h3>{{ event.title }}</h3>

          <p>
            Statuss:
            <strong>{{ event.done ? 'Izdarīts' : 'Neizdarīts' }}</strong>
          </p>

          <p>
            📆 {{ formatDate(event.date) }}
            ⏰ {{ event.time || 'Nav norādīts' }}
          </p>

          <p v-if="event.location">📍 {{ event.location }}</p>
          <p v-if="event.description">{{ event.description }}</p>
        </div>

        <div class="event-actions">
          <button @click="startEdit(event)">Rediģēt</button>
          <button @click="toggleDone(event.id)">
            {{ event.done ? 'Atcelt statusu' : 'Atzīmēt kā izdarītu' }}
          </button>
          <button class="danger" @click="deleteEvent(event.id)">Dzēst</button>
        </div>
      </article>
    </section>

<section v-else class="calendar-view">
  <h2>Kalendāra skats</h2>

  <div class="calendar-month">
    <div class="calendar-day-name">P</div>
    <div class="calendar-day-name">O</div>
    <div class="calendar-day-name">T</div>
    <div class="calendar-day-name">C</div>
    <div class="calendar-day-name">P</div>
    <div class="calendar-day-name">S</div>
    <div class="calendar-day-name">Sv</div>

    <div
      v-for="day in monthDays"
      :key="day.date"
      class="calendar-cell"
    >
      <strong>{{ day.day }}</strong>

      <div
        v-for="event in day.events"
        :key="event.id"
        class="mini-event"
      >
        {{ event.time || '--:--' }} {{ event.title }}
      </div>
    </div>
  </div>
</section>

    <section v-if="role === 'admin' && events.length > 0" class="admin-zone">
      <h2>Administratora panelis</h2>

      <button class="danger" @click="deleteAllEvents">
        Dzēst visus notikumus
      </button>
    </section>
  </main>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const currentUser = ref(JSON.parse(localStorage.getItem('catlendar_user') || 'null'))

if (!currentUser.value) {
  router.push('/auth')
}

const events = ref([])
const role = ref(currentUser.value?.role || 'user')
const viewMode = ref('list')
const search = ref('')
const filterDate = ref('')
const sortOrder = ref('asc')
const editingId = ref(null)
const darkMode = ref(localStorage.getItem('dark') === 'true')

const form = ref({
  title: '',
  date: '',
  time: '',
  location: '',
  description: ''
})

onMounted(() => {
  fetchEvents()
})

async function fetchEvents() {
  const res = await fetch(`http://127.0.0.1:8000/api/events?user_id=${currentUser.value.id}`)
  events.value = await res.json()
}

async function createEvent(event) {
  await fetch('http://127.0.0.1:8000/api/events', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(event)
  })
}

async function updateEventApi(id, event) {
  await fetch(`http://127.0.0.1:8000/api/events/${id}`, {
    method: 'PUT',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(event)
  })
}

async function deleteEventApi(id) {
  await fetch(`http://127.0.0.1:8000/api/events/${id}`, {
    method: 'DELETE'
  })
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

  if (editingId.value) {
    await updateEventApi(editingId.value, {
      ...form.value,
      title,
      user_id: currentUser.value.id
    })
  } else {
    await createEvent({
      ...form.value,
      title,
      done: false,
      user_id: currentUser.value.id
    })
  }

  clearForm()
  await fetchEvents()
}

function startEdit(event) {
  editingId.value = event.id
  form.value = {
    title: event.title,
    date: event.date,
    time: event.time,
    location: event.location,
    description: event.description
  }
}

function clearForm() {
  editingId.value = null
  form.value = {
    title: '',
    date: '',
    time: '',
    location: '',
    description: ''
  }
}

async function deleteEvent(id) {
  await deleteEventApi(id)
  await fetchEvents()
}

async function toggleDone(id) {
  const event = events.value.find((event) => event.id === id)
  if (!event) return

  await updateEventApi(id, {
    ...event,
    done: !event.done
  })

  await fetchEvents()
}

async function deleteAllEvents() {
  if (!confirm('Vai tiešām dzēst visus notikumus?')) return

  for (const event of events.value) {
    await deleteEventApi(event.id)
  }

  await fetchEvents()
}

async function deleteByDate() {
  if (!filterDate.value) return

  if (!confirm(`Vai dzēst visus notikumus datumā ${filterDate.value}?`)) return

  const toDelete = events.value.filter((event) => event.date === filterDate.value)

  for (const event of toDelete) {
    await deleteEventApi(event.id)
  }

  await fetchEvents()
}

function exportData() {
  const data = JSON.stringify(events.value, null, 2)
  const blob = new Blob([data], { type: 'application/json' })
  const url = URL.createObjectURL(blob)

  const link = document.createElement('a')
  link.href = url
  link.download = 'events.json'
  link.click()

  URL.revokeObjectURL(url)
}

function importData() {
  alert('Importu pagaidām atstāj kā papildu funkciju. DB jau strādā caur API.')
}

function formatDate(date) {
  if (!date) return ''
  const [year, month, day] = date.split('-')
  return `${day}.${month}.${year}`
}

function toggleDark() {
  darkMode.value = !darkMode.value
  localStorage.setItem('dark', darkMode.value)
}

function logout() {
  localStorage.removeItem('catlendar_user')
  router.push('/auth')
}

const filteredEvents = computed(() => {
  let result = [...events.value]

  if (search.value) {
    const query = search.value.toLowerCase()
    result = result.filter((event) =>
      event.title.toLowerCase().includes(query) ||
      (event.location || '').toLowerCase().includes(query)
    )
  }

  if (filterDate.value) {
    result = result.filter((event) => event.date === filterDate.value)
  }

  result.sort((a, b) => {
    const first = new Date(`${a.date}T${a.time || '00:00'}`)
    const second = new Date(`${b.date}T${b.time || '00:00'}`)

    return sortOrder.value === 'asc'
      ? first - second
      : second - first
  })

  return result
})

const todayEvents = computed(() => {
  const today = new Date().toISOString().slice(0, 10)
  return events.value.filter((event) => event.date === today)
})

const upcomingEvents = computed(() => {
  const today = new Date().toISOString().slice(0, 10)
  return events.value.filter((event) => event.date >= today)
})

const calendarDays = computed(() => {
  const grouped = {}

  filteredEvents.value.forEach((event) => {
    if (!grouped[event.date]) {
      grouped[event.date] = []
    }

    grouped[event.date].push(event)
  })

  return Object.keys(grouped)
    .sort()
    .map((date) => ({
      date,
      events: grouped[date]
    }))
})

const monthDays = computed(() => {
  const now = new Date()
  const year = now.getFullYear()
  const month = now.getMonth()
  const lastDay = new Date(year, month + 1, 0).getDate()

  const days = []

  for (let day = 1; day <= lastDay; day++) {
    const date = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`

    days.push({
      day,
      date,
      events: filteredEvents.value.filter((event) => event.date === date)
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

  font-family: Arial, sans-serif;

  background:
    linear-gradient(rgba(255,255,255,0.85), rgba(255,255,255,0.85)),
    url('https://i.pinimg.com/1200x/06/5e/4a/065e4a0c4063eda47416995e008babf7.jpg') center / cover no-repeat;
}
.header {
  display: flex;
  justify-content: space-between;
  gap: 24px;
  align-items: center;
  margin-bottom: 24px;
}

.header h1 {
  margin-bottom: 8px;
}

.top-actions {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

.stats {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 18px;
  margin-bottom: 24px;
}

.card,
.form-card,
.event-card,
.day-card,
.admin-zone {
  background: white;
  border-radius: 16px;
  box-shadow: 0 6px 18px rgba(0,0,0,0.08);
}

.card {
  padding: 20px;
}

.card strong {
  display: block;
  font-size: 32px;
}

.card span {
  color: #667085;
}

.form-card,
.admin-zone {
  padding: 24px;
  margin-bottom: 24px;
}

.form-grid,
.tools {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 12px;
}

input,
textarea,
select {
  padding: 12px;
  border: 1px solid #d0d5dd;
  border-radius: 10px;
  font-size: 15px;
}

textarea {
  width: 100%;
  min-height: 90px;
  margin-top: 12px;
  box-sizing: border-box;
}

.buttons {
  margin-top: 12px;
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

button,
.file-button {
  padding: 10px 14px;
  border: none;
  border-radius: 10px;
  background: #1d4ed8;
  color: white;
  cursor: pointer;
  font-size: 14px;
}

button:hover,
.file-button:hover {
  opacity: 0.9;
}

.secondary {
  background: #667085;
}

.danger {
  background: #dc2626;
}

.tools {
  margin-bottom: 24px;
}

.event-card {
  padding: 18px;
  margin-bottom: 14px;
  display: flex;
  justify-content: space-between;
  gap: 16px;
}

.event-card h3 {
  margin-top: 0;
}

.event-actions {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
  align-items: flex-start;
}

.empty {
  color: #667085;
}

.calendar-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 18px;
}

.day-card {
  padding: 18px;
}

.day-card h3 {
  margin-top: 0;
}

.mini-event {
  background: #eef4ff;
  padding: 10px;
  border-radius: 10px;
  margin-bottom: 8px;
  display: flex;
  gap: 8px;
}

.dark {
  background: #0f172a !important;
  color: white !important;
}

@media (max-width: 800px) {
  .header,
  .event-card {
    flex-direction: column;
    align-items: stretch;
  }

  .stats,
  .form-grid,
  .tools,
  .calendar-grid {
    grid-template-columns: 1fr;
  }

.event-card {
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.event-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 10px 25px rgba(0,0,0,0.15);
}
}

.calendar-month {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 10px;
}

.calendar-day-name {
  font-weight: bold;
  text-align: center;
  padding: 10px;
}

.calendar-cell {
  min-height: 120px;
  background: white;
  border-radius: 14px;
  padding: 10px;
  box-shadow: 0 6px 18px rgba(0,0,0,0.08);
}

.mini-event {
  margin-top: 8px;
  background: #eef4ff;
  border-radius: 8px;
  padding: 6px;
  font-size: 13px;
}
</style>