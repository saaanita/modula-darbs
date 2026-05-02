<template>
  <div class="dashboard-page">
    <aside class="sidebar">
      <div>
        <h2 class="logo">Catlendar</h2>

        <nav class="sidebar-nav">
          <button 
            class="nav-item" 
            :class="{ active: activeTab === 'calendar' }"
            @click="activeTab = 'calendar'"
          >
            Kalendārs
          </button>
          <button 
            class="nav-item"
            :class="{ active: activeTab === 'events' }"
            @click="activeTab = 'events'"
          >
            Notikumi
          </button>
          <button 
            class="nav-item"
            :class="{ active: activeTab === 'settings' }"
            @click="activeTab = 'settings'"
          >
            Iestatījumi
          </button>
        </nav>
      </div>

      <router-link to="/" class="logout-btn">Atpakaļ uz sākumu</router-link>
    </aside>

    <main class="main-content">
      <div class="topbar">
        <div>
          <h1>Mans kalendārs</h1>
          <p>Pārvaldi savus notikumus un atgādinājumus.</p>
        </div>

        <div class="top-actions">
          <input v-model="search" type="text" placeholder="Meklēt notikumus..." />
          <button class="add-btn" @click="openAddModal">+ Pievienot</button>
        </div>
      </div>

      <div class="content-grid" v-if="activeTab === 'calendar'">
        <section class="calendar-section">
          <div class="calendar-header">
            <button class="month-btn" @click="prevMonth">←</button>
            <h2>{{ currentMonth }}</h2>
            <button class="month-btn" @click="nextMonth">→</button>
          </div>

          <div class="weekdays">
            <span v-for="day in weekdays" :key="day">{{ day }}</span>
          </div>

          <div class="calendar-grid">
            <div
              v-for="(cell, index) in calendarCells"
              :key="index"
              class="calendar-cell"
              :class="{
                empty: cell === null,
                selected: cell !== null && selectedDay === cell,
              }"
              @click="cell !== null && selectDay(cell)"
            >
              <template v-if="cell !== null">
                <div class="day-number">{{ cell }}</div>

                <div
                  v-for="event in filteredEventsByDay(cell)"
                  :key="event.id"
                  class="event-chip"
                >
                  <span class="event-title" @click.stop="editEvent(event)">
                    {{ event.title }}
                  </span>
                  <button class="chip-delete" @click.stop="deleteEvent(event.id)">×</button>
                </div>
              </template>
            </div>
          </div>
        </section>

        <aside class="right-panel">
          <div class="panel-card selected-day-card">
            <h3>Izvēlētā diena</h3>
            <p class="selected-day-text">
              {{
                selectedDay
                  ? `${monthNameOnly} ${selectedDay}, ${currentYear}`
                  : 'Izvēlies dienu kalendārā'
              }}
            </p>

            <button v-if="selectedDay" class="mini-add-btn" @click="openAddModal">
              + Pievienot notikumu šai dienai
            </button>
          </div>

          <div class="panel-card">
            <h3>Dienas notikumi</h3>

            <div v-if="selectedDayEvents.length">
              <div
                v-for="event in selectedDayEvents"
                :key="event.id"
                class="event-list-item"
              >
                <div class="event-list-content">
                  <strong>{{ event.title }}</strong>
                  <p v-if="event.time || event.is_all_day">
                    {{ event.is_all_day ? 'Visa diena' : event.time }}
                  </p>
                  <small v-if="event.description">{{ event.description }}</small>
                </div>

                <div class="event-actions">
                  <button @click="editEvent(event)">Rediģēt</button>
                  <button class="delete-btn" @click="deleteEvent(event.id)">Dzēst</button>
                </div>
              </div>
            </div>

            <p v-else class="empty-state">Šai dienai vēl nav notikumu.</p>
          </div>
        </aside>
      </div>

      <div v-if="activeTab === 'events'" class="content-area">
        <h2>Visi notikumi</h2>
        <div class="events-list">
          <div v-if="events.length" class="all-events">
            <div v-for="event in events" :key="event.id" class="event-card">
              <div class="event-header">
                <h3>{{ event.title }}</h3>
                <div class="event-meta">
                  <span v-if="event.is_all_day">Visa diena</span>
                  <span v-else>{{ event.time }}</span>
                </div>
              </div>
              <p v-if="event.description">{{ event.description }}</p>
              <div class="event-card-actions">
                <button @click="editEvent(event)">Rediģēt</button>
                <button class="delete-btn" @click="deleteEvent(event.id)">Dzēst</button>
              </div>
            </div>
          </div>
          <p v-else class="empty-state">Vēl nav neviena notikuma.</p>
        </div>
      </div>

      <div v-if="activeTab === 'settings'" class="content-area">
        <h2>Iestatījumi</h2>
        <div class="settings-panel">
          <p>Iestatījumi paredzēti turpmākai attīstībai.</p>
        </div>
      </div>
    </main>

    <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
      <div class="modal-card">
        <div class="modal-top">
          <div>
            <h2>{{ editingEvent ? 'Rediģēt notikumu' : 'Pievienot notikumu' }}</h2>
            <p>{{ editingEvent ? 'Atjaunini notikuma informāciju.' : 'Aizpildi informāciju par jauno notikumu.' }}</p>
          </div>

          <button class="modal-close" @click="closeModal">×</button>
        </div>

<form @submit.prevent="saveEvent" class="event-form">
  <div class="form-group">
    <label>Notikuma nosaukums</label>
    <input
      v-model="form.title"
      type="text"
      placeholder="Piemēram, sapulce vai konsultācija"
      required
    />
  </div>

  <div class="form-group checkbox-group">
    <label class="checkbox-label">
      <input v-model="form.is_all_day" type="checkbox" />
      Visa diena
    </label>
  </div>

  <div class="form-row">
    <div class="form-group">
      <label>Diena</label>
      <input
        v-model="form.day"
        type="number"
        :min="1"
        :max="daysInMonth"
        placeholder="Diena"
        required
      />
    </div>

    <div class="form-group" v-if="!form.is_all_day">
      <label>Laiks</label>
      <input
        v-model="form.time"
        type="time"
      />
    </div>
  </div>

  <div class="form-group">
    <label>Apraksts</label>
    <textarea
      v-model="form.description"
      rows="4"
      placeholder="Īss apraksts par notikumu"
    ></textarea>
  </div>

  <div class="modal-actions">
    <button type="button" class="secondary-btn" @click="closeModal">
      Atcelt
    </button>
    <button type="submit" class="primary-btn">
      {{ editingEvent ? 'Saglabāt izmaiņas' : 'Pievienot notikumu' }}
    </button>
  </div>
</form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'

const weekdays = ['P', 'O', 'T', 'C', 'Pk', 'S', 'Sv']

const activeTab = ref('calendar')
const currentDate = ref(new Date())
const search = ref('')
const selectedDay = ref(null)
const showModal = ref(false)
const editingEvent = ref(null)

const form = ref({
  title: '',
  day: '',
  time: '',
  description: '',
  is_all_day: false,
})

const events = ref([])

const currentMonth = computed(() => {
  return currentDate.value.toLocaleString('lv-LV', {
    month: 'long',
    year: 'numeric',
  })
})

const monthNameOnly = computed(() => {
  return currentDate.value.toLocaleString('lv-LV', {
    month: 'long',
  })
})

const currentYear = computed(() => {
  return currentDate.value.getFullYear()
})

const currentMonthIndex = computed(() => {
  return currentDate.value.getMonth()
})

const daysInMonth = computed(() => {
  const year = currentDate.value.getFullYear()
  const month = currentDate.value.getMonth()
  return new Date(year, month + 1, 0).getDate()
})

const firstDayOfMonth = computed(() => {
  const year = currentDate.value.getFullYear()
  const month = currentDate.value.getMonth()
  const day = new Date(year, month, 1).getDay()
  return day === 0 ? 6 : day - 1
})

const calendarCells = computed(() => {
  const cells = []

  for (let i = 0; i < firstDayOfMonth.value; i++) {
    cells.push(null)
  }

  for (let day = 1; day <= daysInMonth.value; day++) {
    cells.push(day)
  }

  while (cells.length % 7 !== 0) {
    cells.push(null)
  }

  return cells
})

const filteredEventsByDay = (day) => {
  return events.value.filter(
    (event) =>
      Number(event.day) === day &&
      Number(event.month) === currentMonthIndex.value &&
      Number(event.year) === currentYear.value &&
      event.title.toLowerCase().includes(search.value.toLowerCase())
  )
}

const selectedDayEvents = computed(() => {
  if (!selectedDay.value) return []
  return filteredEventsByDay(selectedDay.value)
})

async function loadEvents() {
  const res = await fetch('http://127.0.0.1:8000/api/events')
  const data = await res.json()
  events.value = data
}

onMounted(() => {
  loadEvents()
})

function selectDay(day) {
  selectedDay.value = day
}

function openAddModal() {
  editingEvent.value = null
  form.value = {
    title: '',
    day: selectedDay.value || '',
    time: '',
    description: '',
    is_all_day: false,
  }
  showModal.value = true
}

function editEvent(event) {
  editingEvent.value = event
  form.value = {
    title: event.title,
    day: event.day,
    time: event.time || '',
    description: event.description || '',
    is_all_day: !!event.is_all_day,
  }
  showModal.value = true
}

function closeModal() {
  showModal.value = false
  editingEvent.value = null
  form.value = {
    title: '',
    day: '',
    time: '',
    description: '',
    is_all_day: false,
  }
}

async function saveEvent() {
    if (!form.value.is_all_day && !form.value.time) {
      alert('Lūdzu ievadi laiku vai izvēlies "Visa diena"')
      return
    }   
  
    const eventData = {
    title: form.value.title,
    description: form.value.description,
    day: Number(form.value.day),
    month: currentMonthIndex.value,
    year: currentYear.value,
    time: form.value.is_all_day ? null : form.value.time,
    is_all_day: form.value.is_all_day,
    user_id: null,
    group_id: null,
    category_id: null,
  }

  if (editingEvent.value) {
    const res = await fetch(`http://127.0.0.1:8000/api/events/${editingEvent.value.id}`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: JSON.stringify(eventData),
    })

    if (!res.ok) {
      const errorData = await res.json()
      console.error('Rediģēšanas kļūda:', JSON.stringify(errorData, null, 2))
      alert('Neizdevās rediģēt notikumu')
      return
    }

    const updatedEvent = await res.json()
    const index = events.value.findIndex((e) => e.id === updatedEvent.id)

    if (index !== -1) {
      events.value[index] = updatedEvent
    }

    closeModal()
    return
  }

  const res = await fetch('http://127.0.0.1:8000/api/events', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    },
    body: JSON.stringify(eventData),
  })

  if (!res.ok) {
    const errorData = await res.json()
    console.error('Saglabāšanas kļūda:', JSON.stringify(errorData, null, 2))
    alert('Neizdevās saglabāt notikumu')
    return
  }

  const data = await res.json()
  events.value.push(data)
  closeModal()
}

async function deleteEvent(id) {
  const res = await fetch(`http://127.0.0.1:8000/api/events/${id}`, {
    method: 'DELETE',
    headers: {
      'Accept': 'application/json',
    },
  })

  if (!res.ok) {
    alert('Neizdevās izdzēst notikumu')
    return
  }

  events.value = events.value.filter((event) => event.id !== id)
}
function nextMonth() {
  currentDate.value = new Date(
    currentDate.value.getFullYear(),
    currentDate.value.getMonth() + 1,
    1
  )
  selectedDay.value = null
}

function prevMonth() {
  currentDate.value = new Date(
    currentDate.value.getFullYear(),
    currentDate.value.getMonth() - 1,
    1
  )
  selectedDay.value = null
}
</script>

<style scoped>
.dashboard-page {
  min-height: 100vh;
  background: #efe7ff;
  display: grid;
  grid-template-columns: 260px 1fr;
}

.sidebar {
  background: #f7f5fc;
  padding: 32px 20px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  border-right: 1px solid #e9ddff;
}

.logo {
  color: #2d1457;
  font-size: 32px;
  margin-bottom: 40px;
}

.sidebar-nav {
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.nav-item {
  border: none;
  background: transparent;
  padding: 14px 18px;
  border-radius: 14px;
  text-align: left;
  font-weight: 600;
  color: #5b4b75;
  cursor: pointer;
}

.nav-item.active {
  background: linear-gradient(135deg, #7c3aed, #9333ea);
  color: white;
}

.logout-btn {
  text-decoration: none;
  background: #e9ddff;
  color: #5b4b75;
  padding: 14px 18px;
  border-radius: 14px;
  text-align: center;
  font-weight: 600;
}

.main-content {
  padding: 32px;
}

.topbar {
  display: flex;
  justify-content: space-between;
  gap: 20px;
  align-items: center;
  margin-bottom: 28px;
}

.topbar h1 {
  font-size: 40px;
  color: #2d1457;
}

.topbar p {
  color: #7b6e96;
}

.top-actions {
  display: flex;
  gap: 12px;
  align-items: center;
}

.top-actions input {
  padding: 14px 16px;
  border-radius: 14px;
  border: none;
  min-width: 240px;
  background: white;
}

.add-btn,
.mini-add-btn {
  background: #7c3aed;
  color: white;
  border: none;
  border-radius: 14px;
  padding: 14px 18px;
  font-weight: 700;
  cursor: pointer;
}

.mini-add-btn {
  padding: 12px 16px;
  margin-top: 14px;
  width: 100%;
}

.content-grid {
  display: grid;
  grid-template-columns: 2fr 340px;
  gap: 24px;
}

.calendar-section,
.panel-card {
  background: #fff;
  border-radius: 28px;
  padding: 24px;
  box-shadow: 0 10px 30px rgba(124, 58, 237, 0.06);
}

.calendar-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 20px;
}

.calendar-header h2 {
  color: #2d1457;
  text-transform: capitalize;
}

.month-btn {
  border: none;
  background: #ede9fe;
  color: #5b21b6;
  padding: 10px 14px;
  border-radius: 10px;
  cursor: pointer;
  font-weight: 700;
}

.weekdays {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  margin-bottom: 10px;
  color: #7b6e96;
  font-weight: 600;
}

.calendar-grid {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 10px;
}

.calendar-cell {
  min-height: 110px;
  border: 1px solid #f0e9ff;
  border-radius: 18px;
  padding: 10px;
  background: #fcfbff;
  cursor: pointer;
  transition: 0.2s ease;
}

.calendar-cell:hover {
  transform: translateY(-1px);
  box-shadow: 0 8px 20px rgba(124, 58, 237, 0.08);
}

.calendar-cell.selected {
  border: 2px solid #7c3aed;
  background: #f7f0ff;
}

.calendar-cell.empty {
  background: transparent;
  border: none;
  cursor: default;
  box-shadow: none;
}

.day-number {
  font-weight: 700;
  margin-bottom: 8px;
  color: #4b3a69;
}

.event-chip {
  background: #fbcfe8;
  color: #9d174d;
  border-radius: 12px;
  padding: 6px 10px;
  font-size: 12px;
  margin-bottom: 6px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 8px;
}

.event-title {
  cursor: pointer;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.chip-delete {
  border: none;
  background: transparent;
  color: #9d174d;
  font-weight: 700;
  cursor: pointer;
  padding: 0;
  font-size: 14px;
}

.right-panel {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.selected-day-card {
  background: linear-gradient(135deg, #f7f0ff, #fff);
}

.selected-day-text {
  color: #4b3a69;
  font-weight: 600;
  margin-top: 8px;
}

.event-list-item {
  display: flex;
  justify-content: space-between;
  gap: 14px;
  align-items: flex-start;
  padding: 14px 0;
  border-bottom: 1px solid #f2ecff;
}

.event-list-content p {
  color: #7b6e96;
  margin-top: 4px;
}

.event-list-content small {
  color: #8b7aa7;
  display: block;
  margin-top: 6px;
  line-height: 1.4;
}

.event-actions {
  display: flex;
  gap: 8px;
}

.event-actions button {
  border: none;
  background: #ede9fe;
  color: #5b21b6;
  padding: 8px 12px;
  border-radius: 10px;
  cursor: pointer;
}

.event-actions .delete-btn {
  background: #ffe4e6;
  color: #be123c;
}

.empty-state {
  color: #7b6e96;
}

.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(17, 24, 39, 0.35);
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 24px;
}

.modal-card {
  width: 100%;
  max-width: 500px;
  background: white;
  border-radius: 24px;
  padding: 28px;
}

.modal-top {
  display: flex;
  justify-content: space-between;
  gap: 16px;
  align-items: flex-start;
  margin-bottom: 18px;
}

.modal-top h2 {
  margin-bottom: 6px;
}

.modal-top p {
  color: #7b6e96;
}

.modal-close {
  border: none;
  background: #f3f0ff;
  color: #5b21b6;
  width: 36px;
  height: 36px;
  border-radius: 10px;
  cursor: pointer;
  font-size: 20px;
}

.event-form {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 14px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.form-group label {
  font-weight: 600;
  color: #4b3a69;
}

.event-form input,
.event-form textarea {
  border: 1px solid #ddd6fe;
  border-radius: 12px;
  padding: 14px 16px;
  font: inherit;
  resize: vertical;
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  margin-top: 10px;
}

.primary-btn,
.secondary-btn {
  border: none;
  border-radius: 12px;
  padding: 12px 16px;
  font-weight: 700;
  cursor: pointer;
}

.primary-btn {
  background: #7c3aed;
  color: white;
}

.secondary-btn {
  background: #ede9fe;
  color: #5b21b6;
}

@media (max-width: 1100px) {
  .content-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 900px) {
  .dashboard-page {
    grid-template-columns: 1fr;
  }

  .sidebar {
    border-right: none;
    border-bottom: 1px solid #e9ddff;
  }

  .topbar {
    flex-direction: column;
    align-items: flex-start;
  }

  .top-actions {
    width: 100%;
    flex-direction: column;
    align-items: stretch;
  }

  .form-row {
    grid-template-columns: 1fr;
  }
}

.checkbox-group {
  margin-top: -4px;
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 10px;
  font-weight: 600;
  color: #4b3a69;
}

.checkbox-label input {
  width: 16px;
  height: 16px;
}

.content-area {
  padding: 24px;
  background: white;
  border-radius: 28px;
  box-shadow: 0 10px 30px rgba(124, 58, 237, 0.06);
}

.content-area h2 {
  color: #2d1457;
  margin-bottom: 24px;
  font-size: 28px;
}

.events-list {
  display: flex;
  flex-direction: column;
}

.all-events {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 20px;
}

.event-card {
  background: #fcfbff;
  border: 1px solid #f0e9ff;
  border-radius: 16px;
  padding: 20px;
  transition: 0.2s ease;
}

.event-card:hover {
  box-shadow: 0 8px 20px rgba(124, 58, 237, 0.08);
  transform: translateY(-2px);
}

.event-header {
  display: flex;
  justify-content: space-between;
  align-items: start;
  gap: 12px;
  margin-bottom: 12px;
}

.event-header h3 {
  color: #2d1457;
  margin: 0;
}

.event-meta {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 4px;
}

.event-meta span {
  font-size: 12px;
  color: #7b6e96;
  background: #ede9fe;
  padding: 4px 8px;
  border-radius: 6px;
}

.event-card p {
  color: #7b6e96;
  margin: 8px 0 16px 0;
  line-height: 1.5;
}

.event-card-actions {
  display: flex;
  gap: 8px;
}

.event-card-actions button {
  flex: 1;
  border: none;
  background: #ede9fe;
  color: #5b21b6;
  padding: 10px 12px;
  border-radius: 10px;
  cursor: pointer;
  font-weight: 600;
  transition: 0.2s ease;
}

.event-card-actions button:hover {
  background: #ddd6fe;
}

.event-card-actions .delete-btn {
  background: #ffe4e6;
  color: #be123c;
}

.event-card-actions .delete-btn:hover {
  background: #ffd1d5;
}

.settings-panel {
  background: #fcfbff;
  border: 1px solid #f0e9ff;
  border-radius: 16px;
  padding: 40px;
  text-align: center;
  color: #7b6e96;
}

</style>