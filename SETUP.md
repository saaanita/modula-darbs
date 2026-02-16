# Catlendar - Event Management Platform

A full-stack event management application with a Vue 3 frontend and Laravel backend API.

## Project Structure

```
modula-darbs/
├── frontend/               # Vue 3 frontend application
│   ├── src/
│   │   ├── components/     # Vue components
│   │   ├── pages/          # Page components (HomePage, AboutPage, etc.)
│   │   ├── services/       # API service
│   │   ├── assets/         # CSS and static assets
│   │   ├── App.vue         # Root component
│   │   └── main.js         # Application entry point
│   ├── package.json        # Frontend dependencies and scripts
│   ├── vite.config.js      # Vite configuration
│   ├── jsconfig.json       # JavaScript configuration
│   └── README.md           # Frontend documentation
├── backend/                # Laravel REST API backend
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/  # API controllers
│   │   │   └── Middleware/   # HTTP middleware
│   │   ├── Models/           # Database models
│   │   ├── Console/          # Console commands
│   │   └── Exceptions/       # Exception handlers
│   ├── database/
│   │   └── migrations/       # Database migrations
│   ├── routes/               # API routes
│   ├── config/               # Configuration files
│   ├── bootstrap/            # Bootstrap files
│   ├── composer.json         # PHP dependencies
│   ├── .env.example          # Environment variables template
│   ├── artisan               # Laravel CLI
│   └── README.md             # Backend documentation
└── SETUP.md                # This file - setup instructions
```

## Quick Start

### Frontend Setup

1. Navigate to frontend directory:
```bash
cd frontend
```

2. Install dependencies:
```bash
npm install
```

3. Create `.env` file from template:
```bash
cp .env.example .env
```

4. Start development server:
```bash
npm run dev
```

The frontend will be available at `http://localhost:5173`

### Backend Setup

1. Navigate to the backend directory:
```bash
cd backend
```

2. Install dependencies:
```bash
composer install
```

3. Copy environment file:
```bash
cp .env.example .env
```

4. Generate application key:
```bash
php artisan key:generate
```

5. Create SQLite database:
```bash
touch database/database.sqlite
```

6. Run migrations:
```bash
php artisan migrate
```

7. Start the server:
```bash
php artisan serve
```

The API will be available at `http://localhost:8000/api`

## Features

### Frontend (Vue 3 + Vuetify)
- Responsive UI with Material Design
- Single Page Application (SPA) with Vue Router
- API integration with Axios
- User authentication with Sanctum tokens
- Event management interface
- User profile management

### Backend (Laravel)
- RESTful API with standardized response format
- User authentication with Sanctum
- Event CRUD operations
- User management
- Database migrations for Users, Events, and Access Tokens
- CORS support for frontend integration

## API Endpoints

### Authentication
- `POST /api/register` - Register a new user
- `POST /api/login` - Login and receive token
- `POST /api/logout` - Logout (requires authentication)

### Users
- `GET /api/users` - List all users
- `GET /api/users/{id}` - Get specific user
- `PUT /api/users/{id}` - Update user
- `DELETE /api/users/{id}` - Delete user
- `GET /api/profile` - Get current authenticated user

### Events
- `GET /api/events` - List all events
- `POST /api/events` - Create new event
- `GET /api/events/{id}` - Get specific event
- `PUT /api/events/{id}` - Update event
- `DELETE /api/events/{id}` - Delete event

## Configuration

### Environment Variables

**Frontend (.env)**
Located in `frontend/.env`:
```
VITE_API_URL=http://localhost:8000/api
```

**Backend (.env)**
Located in `backend/.env`:
```
APP_NAME=Catlendar
APP_ENV=local
APP_DEBUG=true
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite
SANCTUM_STATEFUL_DOMAINS=localhost:5173
CORS_ALLOWED_ORIGINS=localhost:5173
```

## Technology Stack

### Frontend
- Vue 3.5+
- Vue Router 4
- Vuetify 3 (Material Design Components)
- Axios (HTTP client)
- Vite (Build tool)

### Backend
- Laravel 10+
- Laravel Sanctum (API authentication)
- SQLite / MySQL (Database)
- PHP 8.1+

## Development

### Build Frontend
```bash
npm run build
```

### Run Tests (Backend)
```bash
cd laravel-backend
php artisan test
```

### Database Management
```bash
# Create migrations
php artisan make:migration create_table_name

# Run migrations
php artisan migrate

# Rollback migrations
php artisan migrate:rollback

# Create new model with migration
php artisan make:model ModelName -m
```

## Notes

- The frontend communicates with the backend API via the configured `VITE_API_URL`
- Authentication tokens are stored in localStorage on the frontend
- CORS must be properly configured for successful API communication
- Default database is SQLite for development; switch to MySQL in production

## Support

For issues or questions, please refer to the respective README files:
- Frontend: See `src/README.md` (if available)
- Backend: See `laravel-backend/README.md`
