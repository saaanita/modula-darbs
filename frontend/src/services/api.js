const API_BASE = import.meta.env?.VITE_API_URL || 'http://127.0.0.1:8000/api'
const storage = typeof localStorage !== 'undefined'
  ? localStorage
  : {
      getItem: () => null,
      setItem: () => {},
      removeItem: () => {}
    }

let token = storage.getItem('catlendar_token')

function setToken(newToken) {
  token = newToken

  if (newToken) {
    storage.setItem('catlendar_token', newToken)
  } else {
    storage.removeItem('catlendar_token')
  }
}

function setUser(user) {
  if (user) {
    storage.setItem('catlendar_user', JSON.stringify(user))
  } else {
    storage.removeItem('catlendar_user')
  }
}

function getUser() {
  return JSON.parse(storage.getItem('catlendar_user') || 'null')
}

function clearSession() {
  setToken(null)
  setUser(null)
}

function getAuthHeader() {
  return token ? { Authorization: `Bearer ${token}` } : {}
}

function toQuery(params = {}) {
  const query = new URLSearchParams()

  Object.entries(params).forEach(([key, value]) => {
    if (value !== undefined && value !== null && value !== '') {
      query.append(key, value)
    }
  })

  const value = query.toString()
  return value ? `?${value}` : ''
}

async function request(endpoint, options = {}) {
  const headers = {
    Accept: 'application/json',
    ...getAuthHeader(),
    ...options.headers
  }

  if (options.body) {
    headers['Content-Type'] = 'application/json'
  }

  const response = await fetch(`${API_BASE}${endpoint}`, {
    ...options,
    headers
  })

  const text = await response.text()
  const data = text ? JSON.parse(text) : null

  if (!response.ok) {
    throw new Error(data?.message || data?.error || 'Pieprasījums neizdevās')
  }

  return data
}

function saveAuth(data) {
  setToken(data.token)
  setUser(data.user)
  return data
}

export const auth = {
  register: (payload) =>
    request('/auth/register', {
      method: 'POST',
      body: JSON.stringify(payload)
    }).then(saveAuth),

  login: (payload) =>
    request('/auth/login', {
      method: 'POST',
      body: JSON.stringify(payload)
    }).then(saveAuth),

  me: () => request('/auth/me'),

  logout: async () => {
    try {
      if (token) {
        await request('/auth/logout', { method: 'POST' })
      }
    } finally {
      clearSession()
    }
  }
}

export const events = {
  getAll: (params = {}) => request(`/events${toQuery(params)}`),
  stats: () => request('/events/stats'),

  create: (eventData) =>
    request('/events', {
      method: 'POST',
      body: JSON.stringify(eventData)
    }),

  update: (id, eventData) =>
    request(`/events/${id}`, {
      method: 'PUT',
      body: JSON.stringify(eventData)
    }),

  delete: (id) => request(`/events/${id}`, { method: 'DELETE' })
}

export const groups = {
  getAll: () => request('/groups'),
  create: (groupData) =>
    request('/groups', {
      method: 'POST',
      body: JSON.stringify(groupData)
    })
}

export const tasks = {
  getAll: (groupId) => request(`/tasks${toQuery({ group_id: groupId })}`),
  create: (taskData) =>
    request('/tasks', {
      method: 'POST',
      body: JSON.stringify(taskData)
    })
}

export const health = () => request('/health')

export { clearSession, getUser, setToken, setUser }
