# Manila FAME Registration - Backend

Laravel API backend for the Manila FAME 2026 multi-step event registration system.

## Tech Stack

- Laravel 12
- PHP 8.2+
- MySQL

## Prerequisites

- PHP 8.2 or higher
- Composer
- MySQL 8.0+
- Laravel CLI (optional)
- Frontend Vue app (see [manila-fame-registration-ui](https://github.com/lance-pallesco/manila-fame-registration-ui))

## Getting Started

### 1. Install Dependencies

```bash
cd manila-fame-registration-api
composer install
```

### 2. Configure Environment

```bash
cp .env.example .env
php artisan key:generate
```

### 3. Set Up Database

Open `.env` and set your MySQL credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=manila_fame_registration_api
DB_USERNAME=root
DB_PASSWORD=your_password
```

Then create the database and run migrations:

```sql
CREATE DATABASE manila_fame_registration;
```

```bash
php artisan migrate
```

### 4. Set Up File Storage

Create the symbolic link so uploaded brochures are publicly accessible:

```bash
php artisan storage:link
```

Brochures are stored in `storage/app/public/brochures/` and served from `/storage/brochures/`.

### 5. Configure CORS

The frontend URL is set in `.env`:

```env
FRONTEND_URL=http://localhost:5173
```

Additional allowed origins can be edited in `config/cors.php`.

### 6. Start the Server

```bash
php artisan serve
```

The API will be available at `http://localhost:8000`.

## API Endpoint

### POST /api/register

Registers a new user with company information in a single request.

**Content-Type:** `multipart/form-data`

#### Request Fields

**Account Information (required):**

| Field | Type | Rules |
|-------|------|-------|
| `account_info[first_name]` | string | Required, max 255 |
| `account_info[last_name]` | string | Required, max 255 |
| `account_info[email]` | string | Required, valid email, unique |
| `account_info[username]` | string | Required, alphanumeric/dash/underscore, min 3, unique |
| `account_info[password]` | string | Required, min 8 characters, must match confirmation |
| `account_info[password_confirmation]` | string | Required |
| `account_info[participation_type]` | string | Required, one of: Buyer, Exhibitor, Visitor |

**Company Information (required):**

| Field | Type | Rules |
|-------|------|-------|
| `company_info[company_name]` | string | Required, max 255 |
| `company_info[address_line]` | string | Required, max 500 |
| `company_info[city]` | string | Required, max 255 |
| `company_info[region]` | string | Optional, max 255 |
| `company_info[country]` | string | Required, max 255 |
| `company_info[year_established]` | integer | Required, 4 digits, 1800 to current year |
| `company_info[website]` | string | Optional, valid URL |

**File Upload (optional):**

| Field | Type | Rules |
|-------|------|-------|
| `brochure` | file | PDF, DOC, or DOCX, max 2MB |

#### Responses

**201 Created** -- Registration successful:

```json
{
  "success": true,
  "message": "Registration successful"
}
```

**422 Unprocessable Entity** -- Validation errors:

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "account_info.email": ["This email is already registered."],
    "company_info.year_established": ["Year cannot be in the future."]
  }
}
```

**500 Internal Server Error** -- Server failure:

```json
{
  "success": false,
  "message": "Registration failed. Please try again later."
}
```

## Architecture

This API follows clean architecture principles with thin controllers:

```
Request
  |
  v
RegisterController        <-- Thin: receives request, returns response
  |
  v
RegisterRequest           <-- Validation: all rules defined here
  |
  v
RegisterService           <-- Business logic: wrapped in DB::transaction()
  |
  +--> User::create()     <-- Creates user with hashed password
  +--> Company::create()  <-- Creates company linked to user
  +--> Storage::put()     <-- Stores brochure file (if provided)
  |
  v
JSON Response
```

**Key principles:**
- Controllers only handle HTTP concerns
- Form Requests handle all validation
- Services contain business logic
- All database operations wrapped in `DB::transaction()` for atomicity
- File uploads use `Storage::disk('public')`

## Project Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   └── Api/
│   │       └── RegisterController.php    # Single-action controller (__invoke)
│   └── Requests/
│       └── RegisterRequest.php           # All validation rules & messages
├── Models/
│   ├── User.php                          # User model (hasOne Company)
│   └── Company.php                       # Company model (belongsTo User)
└── Services/
    └── RegisterService.php               # Registration business logic

database/migrations/
├── 0001_01_01_000000_create_users_table.php
└── 0001_01_01_000003_create_companies_table.php

routes/
└── api.php                               # POST /api/register

config/
└── cors.php                              # CORS settings for Vue SPA
```

## Database Schema

**users**

| Column | Type | Notes |
|--------|------|-------|
| id | bigint | Primary key |
| first_name | string | |
| last_name | string | |
| email | string | Unique |
| username | string | Unique |
| password | string | Hashed automatically |
| participation_type | enum | Buyer, Exhibitor, Visitor |
| created_at | timestamp | |
| updated_at | timestamp | |

**companies**

| Column | Type | Notes |
|--------|------|-------|
| id | bigint | Primary key |
| user_id | bigint | Foreign key to users |
| company_name | string | |
| address | string | |
| city | string | |
| region | string | Nullable |
| country | string | |
| year_established | year | |
| website | string | Nullable |
| brochure_path | string | Nullable, relative path |
| created_at | timestamp | |
| updated_at | timestamp | |

## Connecting to the Frontend

| Backend | Frontend (.env) |
|---------|-----------------|
| `php artisan serve` (port 8000) | `VITE_API_BASE_URL=http://localhost:8000/api` |
| CORS allows `http://localhost:5173` | `npm run dev` (port 5173) |

The frontend submits all registration data as `multipart/form-data` in a single POST request. Field names are nested using bracket notation (`account_info[first_name]`) which Laravel parses into arrays automatically.
