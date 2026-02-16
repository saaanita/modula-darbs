# Catlendar Laravel Backend

A RESTful API backend for the Catlendar event management platform, built with Laravel.

## Features

- User authentication with Sanctum tokens
- Event management (CRUD operations)
- User management
- CORS support for Vue frontend
- SQLite database (configurable to MySQL)

## Installation

### Prerequisites

- PHP 8.1+
- Composer
- Node.js (for frontend)

### Setup

1. Navigate to the Laravel backend directory:
```bash
cd backend
```

2. Install dependencies:
```bash
composer install
```

3. Copy the environment file:
```bash
cp .env.example .env
```

4. Generate application key:
```bash
php artisan key:generate
```

5. Create database:
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

## API Endpoints

### Authentication
- `POST /api/register` - Register a new user
- `POST /api/login` - Login user
- `POST /api/logout` - Logout user (requires auth)

### Users
- `GET /api/users` - Get all users
- `GET /api/users/{id}` - Get user by ID
- `PUT /api/users/{id}` - Update user
- `DELETE /api/users/{id}` - Delete user
- `GET /api/profile` - Get current user profile

### Events
- `GET /api/events` - Get all events
- `POST /api/events` - Create event
- `GET /api/events/{id}` - Get event by ID
- `PUT /api/events/{id}` - Update event
- `DELETE /api/events/{id}` - Delete event

## Frontend Integration

The frontend communicates with this API. Make sure to:

1. Update the API base URL in `frontend/src/services/api.js`
2. The frontend runs on `localhost:5173` and the backend on `localhost:8000`
3. CORS is configured for the Vue frontend

## Database

The application uses SQLite by default. To switch to MySQL:

1. Update `.env`:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=catlendar
DB_USERNAME=root
DB_PASSWORD=password
```

2. Run migrations with the new database connection.
