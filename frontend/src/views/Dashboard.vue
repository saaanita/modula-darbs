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
      <section ref="formCard" class="form-card">
        <h2>{{ editingId ? 'Rediģēt notikumu' : 'Pievienot notikumu' }}</h2>
        <p v-if="editingId" class="edit-note">Tiek rediģēts notikums. Pēc izmaiņām nospied Saglabāt.</p>

        <div class="form-grid">
          <label>
            Nosaukums
            <input ref="titleInput" v-model="form.title" placeholder="Piemēram, konsultācija" />
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
            Kategorija
            <select v-model="form.group_id">
              <option value="">Bez kategorijas</option>
              <option v-for="group in groups" :key="group.id" :value="group.id">{{ group.name }}</option>
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
          <button class="secondary" @click="clearForm">{{ editingId ? 'Atcelt' : 'Notīrīt' }}</button>
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

        <div v-if="groupStats.length" class="month-list">
          <h3>Notikumi pa kategorijām</h3>
          <div v-for="item in groupStats" :key="item.id ?? 'none'" class="summary-row">
            <span>{{ item.name }}</span>
            <strong>{{ item.total }}</strong>
          </div>
        </div>
      </section>
    </section>

    <section class="category-card">
      <div class="section-heading">
        <div>
          <h2>Kategorijas un uzdevumi</h2>
          <p>Notikumus var piesaistīt kategorijai, un katrai kategorijai var pievienot sagatavošanās uzdevumus.</p>
        </div>
      </div>

      <div class="category-grid">
        <div>
          <h3>Kategorijas</h3>
          <div class="inline-form">
            <input v-model="groupForm.name" placeholder="Piemēram, skola" />
            <input v-model="groupForm.description" placeholder="Īss apraksts" />
            <button @click="saveGroup">Pievienot kategoriju</button>
          </div>

          <div v-if="groups.length" class="group-list">
            <button
              v-for="group in groups"
              :key="group.id"
              class="group-chip"
              :class="{ active: String(group.id) === String(selectedGroupId) }"
              @click="selectedGroupId = String(group.id)"
            >
              <span>{{ group.name }}</span>
              <small>{{ group.events_count ?? 0 }} notikumi</small>
            </button>
          </div>
          <p v-else class="empty">Kategorijas vēl nav pievienotas.</p>
        </div>

        <div>
          <h3>{{ selectedGroup ? selectedGroup.name : 'Uzdevumi' }}</h3>
          <p v-if="!selectedGroup" class="empty">Izvēlies kategoriju, lai redzētu tās uzdevumus.</p>

          <template v-else>
            <div class="inline-form task-form">
              <input v-model="taskTitle" placeholder="Jauns uzdevums" @keyup.enter="saveTask" />
              <button @click="saveTask">Pievienot uzdevumu</button>
            </div>

            <div v-if="tasks.length" class="task-list">
              <div v-for="task in tasks" :key="task.id" class="task-row" :class="{ done: task.done }">
                <label>
                  <input type="checkbox" :checked="task.done" @change="toggleTask(task)" />
                  <span>{{ task.title }}</span>
                </label>
                <button class="danger" @click="deleteTask(task.id)">Dzēst</button>
              </div>
            </div>
            <p v-else class="empty">Šai kategorijai vēl nav uzdevumu.</p>
          </template>
        </div>
      </div>
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
      <select v-model="filters.group_id">
        <option value="">Visas kategorijas</option>
        <option v-for="group in groups" :key="group.id" :value="group.id">{{ group.name }}</option>
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
            <span> · Kategorija: {{ event.group?.name || 'Bez kategorijas' }}</span>
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
import { computed, nextTick, onMounted, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import { auth, clearSession, events as eventsApi, getUser, groups as groupsApi, tasks as tasksApi } from '../services/api'

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
const formCard = ref(null)
const titleInput = ref(null)
const groups = ref([])
const tasks = ref([])
const selectedGroupId = ref('')
const groupForm = ref({
  name: '',
  description: ''
})
const taskTitle = ref('')

const filters = ref({
  search: '',
  date_from: '',
  date_to: '',
  priority: '',
  status: '',
  group_id: '',
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
const selectedGroup = computed(() => groups.value.find((group) => String(group.id) === String(selectedGroupId.value)) || null)

onMounted(async () => {
  await Promise.all([fetchEvents(), fetchGroups()])
})

watch(
  () => [
    filters.value.date_from,
    filters.value.date_to,
    filters.value.priority,
    filters.value.status,
    filters.value.group_id,
    filters.value.sort_by,
    filters.value.sort_dir
  ],
  fetchEvents
)

watch(selectedGroupId, (groupId) => {
  fetchTasks(groupId)
})

function defaultForm() {
  return {
    title: '',
    date: '',
    time: '',
    location: '',
    description: '',
    priority: 'medium',
    color: '#2563eb',
    group_id: ''
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

async function fetchGroups() {
  try {
    groups.value = await groupsApi.getAll()

    if (!selectedGroupId.value && groups.value.length > 0) {
      selectedGroupId.value = String(groups.value[0].id)
    }
  } catch (err) {
    handleRequestError(err)
  }
}

async function fetchTasks(groupId = selectedGroupId.value) {
  if (!groupId) {
    tasks.value = []
    return
  }

  try {
    tasks.value = await tasksApi.getAll(groupId)
  } catch (err) {
    handleRequestError(err)
  }
}

async function saveGroup() {
  const name = groupForm.value.name.trim()

  if (name.length < 3) {
    alert('Kategorijas nosaukumam jābūt vismaz 3 simboli!')
    return
  }

  try {
    const group = await groupsApi.create({
      name,
      description: groupForm.value.description || null
    })

    groupForm.value = { name: '', description: '' }
    await fetchGroups()
    selectedGroupId.value = String(group.id)
  } catch (err) {
    handleRequestError(err)
  }
}

async function saveTask() {
  const title = taskTitle.value.trim()

  if (!selectedGroupId.value) {
    alert('Vispirms izvēlies kategoriju!')
    return
  }

  if (title.length < 3) {
    alert('Uzdevuma nosaukumam jābūt vismaz 3 simboli!')
    return
  }

  try {
    await tasksApi.create({
      title,
      group_id: selectedGroupId.value,
      done: false
    })

    taskTitle.value = ''
    await fetchTasks()
  } catch (err) {
    handleRequestError(err)
  }
}

async function toggleTask(task) {
  try {
    await tasksApi.update(task.id, {
      title: task.title,
      group_id: task.group_id,
      done: !task.done
    })

    await fetchTasks()
  } catch (err) {
    handleRequestError(err)
  }
}

async function deleteTask(id) {
  if (!confirm('Vai dzēst šo uzdevumu?')) return

  try {
    await tasksApi.delete(id)
    await fetchTasks()
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
    group_id: form.value.group_id || null,
    done: Boolean(form.value.done)
  }

  try {
    if (editingId.value) {
      await eventsApi.update(editingId.value, payload)
    } else {
      await eventsApi.create(payload)
    }

    clearForm()
    await Promise.all([fetchEvents(), fetchGroups()])
  } catch (err) {
    handleRequestError(err)
  }
}

function startEdit(event) {
  error.value = ''
  editingId.value = event.id
  form.value = {
    title: event.title,
    date: event.date,
    time: normalizeTime(event.time),
    location: event.location || '',
    description: event.description || '',
    priority: event.priority || 'medium',
    color: event.color || '#2563eb',
    group_id: event.group_id || '',
    done: Boolean(event.done)
  }

  nextTick(() => {
    formCard.value?.scrollIntoView({ behavior: 'smooth', block: 'start' })
    titleInput.value?.focus()
  })
}

function clearForm() {
  editingId.value = null
  form.value = defaultForm()
}

async function deleteEvent(id) {
  if (!confirm('Vai dzēst šo notikumu?')) return

  try {
    await eventsApi.delete(id)
    await Promise.all([fetchEvents(), fetchGroups()])
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
      group_id: event.group_id || null,
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

    await Promise.all([fetchEvents(), fetchGroups()])
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
    group_id: '',
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
  window.dispatchEvent(new Event('catlendar-dark-mode'))
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

const groupStats = computed(() => stats.value.by_group || [])

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
.category-card,
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
.dark .category-card,
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
.category-card,
.admin-zone,
.calendar-view {
  padding: 22px;
}

.category-card {
  margin-bottom: 18px;
}

.section-heading {
  display: flex;
  justify-content: space-between;
  gap: 14px;
  margin-bottom: 16px;
}

.section-heading p {
  margin: 4px 0 0;
  color: #667085;
}

.category-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 18px;
}

.inline-form {
  display: grid;
  grid-template-columns: minmax(0, 1fr) minmax(0, 1fr) auto;
  gap: 10px;
  margin-bottom: 12px;
}

.task-form {
  grid-template-columns: minmax(0, 1fr) auto;
}

.group-list,
.task-list {
  display: grid;
  gap: 8px;
}

.group-chip {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 10px;
  width: 100%;
  background: #dce8ef;
  color: #1e3a46;
}

.group-chip.active {
  background: #2f6f73;
  color: #ffffff;
}

.group-chip small {
  font-size: 12px;
  font-weight: 700;
  opacity: 0.8;
}

.task-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 10px;
  padding: 10px;
  border: 1px solid #e4eaf3;
  border-radius: 8px;
  background: #fbfdff;
}

.task-row label {
  display: flex;
  align-items: center;
  gap: 8px;
  font-weight: 700;
}

.task-row input {
  width: auto;
}

.task-row.done span {
  color: #667085;
  text-decoration: line-through;
}

.dark .section-heading p,
.dark .task-row.done span {
  color: #9ca3af;
}

.dark .group-chip {
  background: #334155;
  color: #f8fafc;
}

.dark .group-chip.active {
  background: #2f6f73;
}

.dark .task-row {
  border-color: #374151;
  background: #172033;
}

.edit-note {
  margin: -4px 0 14px;
  color: #2f6f73;
  font-weight: 700;
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
  padding: 10px 13px;
  border: none;
  border-radius: 999px;
  background: #2f6f73;
  color: white;
  cursor: pointer;
  font-size: 14px;
  font-weight: 700;
  transition: opacity 0.18s ease, background-color 0.18s ease;
}

button:hover {
  opacity: 0.9;
}

.secondary {
  background: #6b7a90;
}

.danger {
  background: #d4515f;
}

.tools {
  margin-bottom: 14px;
}

.view-switch {
  margin-bottom: 18px;
}

.view-switch button {
  background: #dce8ef;
  color: #1e3a8a;
}

.view-switch button.active {
  background: #2f6f73;
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
  .category-grid,
  .form-grid,
  .tools,
  .inline-form,
  .task-form,
  .calendar-month {
    grid-template-columns: 1fr;
  }

  .event-card {
    display: grid;
  }
}
</style>
