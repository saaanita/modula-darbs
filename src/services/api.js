/**
 * API service for Catlendar frontend
 * Handles all communication with the backend
 */

const API_BASE = import.meta.env.VITE_API_URL || 'http://localhost:8000/api';

let token = localStorage.getItem('token');

function setToken(newToken) {
  token = newToken;
  if (newToken) {
    localStorage.setItem('token', newToken);
  } else {
    localStorage.removeItem('token');
  }
}

function getAuthHeader() {
  return token ? { 'Authorization': `Bearer ${token}` } : {};
}

async function request(endpoint, options = {}) {
  const url = `${API_BASE}${endpoint}`;
  const response = await fetch(url, {
    headers: {
      'Content-Type': 'application/json',
      ...getAuthHeader(),
      ...options.headers
    },
    ...options
  });

  if (!response.ok) {
    const error = await response.json();
    throw new Error(error.error || 'Request failed');
  }

  return response.json();
}

// Auth
export const auth = {
  register: (username, email, password) =>
    request('/auth/register', {
      method: 'POST',
      body: JSON.stringify({ username, email, password })
    }).then(data => {
      setToken(data.token);
      return data;
    }),

  login: (email, password) =>
    request('/auth/login', {
      method: 'POST',
      body: JSON.stringify({ email, password })
    }).then(data => {
      setToken(data.token);
      return data;
    }),

  logout: () => setToken(null)
};

// Events
export const events = {
  getAll: () => request('/events'),

  get: (id) => request(`/events/${id}`),

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

  delete: (id) =>
    request(`/events/${id}`, { method: 'DELETE' })
};

// Users
export const users = {
  getMe: () => request('/users/me'),

  get: (id) => request(`/users/${id}`)
};

// Health check
export const health = () => request('/health');

export { setToken, getAuthHeader };
