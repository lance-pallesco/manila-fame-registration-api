# Manila FAME Registration API

Laravel API backend for the Manila FAME multi-step registration system.

## Requirements

- PHP 8.2+
- Composer
- MySQL 8.0+
- Node.js (for frontend assets, optional)

## Installation

### 1. Clone and Install Dependencies

```bash
cd multi-step-registration-api
composer install
```

### 2. Environment Setup

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 3. Configure Database

Edit `.env` and set your database credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=manila_fame_registration
DB_USERNAME=root
DB_PASSWORD=your_password
```

Create the database:

```sql
CREATE DATABASE manila_fame_registration;
```

### 4. Run Migrations

```bash
php artisan migrate
```

### 5. Create Storage Link

This enables public access to uploaded brochures:

```bash
php artisan storage:link
```

### 6. Start the Server

```bash
php artisan serve
```

The API will be available at `http://localhost:8000`.

## API Endpoint

### POST /api/register

Register a new user with company information.

**Content-Type:** `multipart/form-data`

#### Request Fields

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `account_info[first_name]` | string | Yes | User's first name |
| `account_info[last_name]` | string | Yes | User's last name |
| `account_info[email]` | string | Yes | Email address (unique) |
| `account_info[username]` | string | Yes | Username (alphanumeric, unique) |
| `account_info[password]` | string | Yes | Password (min 8 chars) |
| `account_info[password_confirmation]` | string | Yes | Password confirmation |
| `account_info[participation_type]` | string | Yes | Buyer, Exhibitor, or Visitor |
| `company_info[company_name]` | string | Yes | Company name |
| `company_info[address_line]` | string | Yes | Company address |
| `company_info[city]` | string | Yes | City |
| `company_info[region]` | string | Yes | Region/State |
| `company_info[country]` | string | Yes | Country |
| `company_info[year_established]` | integer | Yes | 4-digit year (1800-current) |
| `company_info[website]` | string | No | Company website URL |
| `brochure` | file | No | PDF, DOC, or DOCX (max 2MB) |

#### Success Response (201)

```json
{
  "success": true,
  "message": "Registration successful"
}
```

#### Validation Error Response (422)

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "account_info.email": ["This email is already registered."],
    "company_info.year_established": ["Year cannot be in the future."]
  }
}
```

#### Server Error Response (500)

```json
{
  "success": false,
  "message": "Registration failed. Please try again later."
}
```

## Project Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   └── Api/
│   │       └── RegisterController.php   # Thin controller
│   └── Requests/
│       └── RegisterRequest.php          # Validation rules
├── Models/
│   ├── User.php                         # User model
│   └── Company.php                      # Company model
└── Services/
    └── RegisterService.php              # Business logic

database/
└── migrations/
    ├── 0001_01_01_000000_create_users_table.php
    └── 0001_01_01_000003_create_companies_table.php

routes/
└── api.php                              # API routes

config/
└── cors.php                             # CORS configuration
```

## Architecture

This API follows clean architecture principles:

- **Controllers** are thin and only handle HTTP concerns
- **Form Requests** handle validation
- **Services** contain business logic
- **Models** handle database operations
- **DB Transactions** ensure data consistency

### Data Flow

```
Request → Controller → FormRequest (validation)
                    ↓
              RegisterService
                    ↓
         DB::transaction()
                    ↓
    User::create() → Company::create() → Storage::put()
                    ↓
              Response (JSON)
```

## Frontend Integration

The Vue SPA frontend should:

1. Set `VITE_API_BASE_URL=http://localhost:8000` in `.env`
2. Set `USE_MOCK = false` in `registrationService.js`
3. Submit data as `multipart/form-data` with nested field names

Example FormData construction:

```javascript
const formData = new FormData();
formData.append('account_info[first_name]', 'John');
formData.append('account_info[last_name]', 'Doe');
// ... other fields
formData.append('brochure', fileObject);
```

## CORS Configuration

CORS is configured in `config/cors.php`. By default, it allows:

- `http://localhost:5173` (Vite default)
- `http://localhost:3000`
- `http://localhost:8080`

Update `FRONTEND_URL` in `.env` for production.

## File Storage

Brochures are stored in `storage/app/public/brochures/`.

After running `php artisan storage:link`, files are accessible at:
`http://localhost:8000/storage/brochures/{filename}`

## Testing

```bash
# Run all tests
php artisan test

# Run with coverage
php artisan test --coverage
```

## License

This project is proprietary software for Manila FAME.
