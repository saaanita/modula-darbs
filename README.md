# Catlendar - Event Management Platform

A modern full-stack event management application with **Vue 3 frontend** and **Laravel API backend**.

## 📁 Project Organization

```
modula-darbs/
├── frontend/          ← Vue 3 + Vuetify application
├── backend/           ← Laravel REST API
└── SETUP.md          ← Detailed setup instructions
```

## 🚀 Quick Start

### Frontend
```bash
cd frontend
npm install
npm run dev
# Runs on http://localhost:5173
```

### Backend
```bash
cd backend
composer install
php artisan migrate
php artisan serve
# Runs on http://localhost:8000/api
```

## 📚 Documentation

- **Frontend**: See [frontend/README.md](frontend/README.md)
- **Backend**: See [backend/README.md](backend/README.md)
- **Full Setup Guide**: See [SETUP.md](SETUP.md)

## ✨ Features

- **Frontend**: Responsive UI with Material Design (Vuetify), Vue Router, Axios
- **Backend**: RESTful API with Laravel Sanctum authentication
- **Database**: SQLite (dev) / MySQL (production)
- **Authentication**: Token-based with Sanctum
- **CORS**: Configured for frontend integration

## 🔗 API Endpoints

All endpoints prefixed with `/api`:

### Auth
- `POST /register` - Register new user
- `POST /login` - Login user
- `POST /logout` - Logout (auth required)

### Events
- `GET /events` - List all events
- `POST /events` - Create event
- `GET /events/{id}` - Get event details
- `PUT /events/{id}` - Update event
- `DELETE /events/{id}` - Delete event

### Users
- `GET /users` - List users
- `GET /profile` - Get current user

## 🛠️ Tech Stack

**Frontend**: Vue 3 • Vuetify 3 • Vue Router • Axios • Vite

**Backend**: Laravel 10 • Laravel Sanctum • PHP 8.1+

**Database**: SQLite / MySQL

## 📋 Requirements

- **Node.js** 20.19.0+ (for frontend)
- **PHP** 8.1+ (for backend)
- **Composer** (PHP package manager)

## 📝 Environment Setup

Detailed instructions available in [SETUP.md](SETUP.md)

## 💡 Tips

- Frontend and backend run on different ports (5173 & 8000)
- API URL in frontend pre-configured for localhost development
- Database migrations auto-create tables
- Sanctum tokens stored in localStorage

## 🤝 Contribution

For development, ensure both servers are running in separate terminals.
