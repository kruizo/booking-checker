# Booking Checker - Backend API

Laravel 12 REST API for the Booking Conflict Checker application.

## Features

-   ✅ **Sanctum Authentication** - Secure SPA authentication with cookies
-   ✅ **SOLID Principles** - Clean architecture with Services and Repositories
-   ✅ **PSR-12 Compliant** - Industry-standard PHP coding standards
-   ✅ **Thin Controllers** - Business logic in Service classes
-   ✅ **Repository Pattern** - Database operations isolated in Repository classes
-   ✅ **Form Requests** - Validation separated from controllers
-   ✅ **API Resources** - Consistent JSON responses
-   ✅ **Admin Middleware** - Protected admin-only routes
-   ✅ **Conflict Detection** - Smart booking overlap and gap analysis
-   ✅ **Scheduled Cleanup** - Automatic deletion of bookings older than 30 days
-   ✅ **SQLite Database** - Zero-configuration database

## Architecture

```
app/
├── Console/Commands/         # Artisan commands
│   └── DeleteOldBookingsCommand.php
├── Http/
│   ├── Controllers/         # Thin controllers
│   │   ├── AuthController.php
│   │   └── BookingController.php
│   ├── Middleware/          # Custom middleware
│   │   └── AdminMiddleware.php
│   ├── Requests/           # Form validation
│   │   ├── LoginRequest.php
│   │   ├── RegisterRequest.php
│   │   ├── StoreBookingRequest.php
│   │   └── UpdateBookingRequest.php
│   └── Resources/          # API responses
│       ├── BookingResource.php
│       └── UserResource.php
├── Models/                 # Eloquent models
│   ├── Booking.php
│   └── User.php
├── Repositories/          # Database operations
│   ├── BookingRepository.php
│   └── UserRepository.php
└── Services/             # Business logic
    ├── AuthService.php
    ├── BookingService.php
    └── ConflictCheckService.php
```

## API Endpoints

### Authentication

-   `POST /api/v1/register` - Register new user
-   `POST /api/v1/login` - Login user
-   `POST /api/v1/logout` - Logout user (auth required)
-   `GET /api/v1/user` - Get authenticated user (auth required)

### Bookings

-   `GET /api/v1/bookings` - List bookings (user's own or all if admin)
-   `POST /api/v1/bookings` - Create new booking
-   `GET /api/v1/bookings/{id}` - Get single booking
-   `PUT /api/v1/bookings/{id}` - Update booking
-   `DELETE /api/v1/bookings/{id}` - Delete booking
-   `GET /api/v1/bookings/{id}/validate` - Check for conflicts

### Admin Only

-   `GET /api/v1/admin/conflicts` - Get comprehensive conflict report

## Installation

1. **Install Dependencies**

    ```bash
    cd backend
    composer install
    ```

2. **Environment Setup**

    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

3. **Database Setup**

    ```bash
    # Create SQLite database
    touch database/database.sqlite

    # Run migrations
    php artisan migrate
    ```

4. **Start Development Server**
    ```bash
    php artisan serve
    ```
    API will be available at `http://localhost:8000`

## Scheduled Tasks

The application includes a daily scheduled task that deletes bookings older than 30 days.

To run the scheduler in development:

```bash
php artisan schedule:work
```

In production, add this to your cron:

```
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

To manually run the cleanup:

```bash
php artisan bookings:delete-old
```

## Coding Standards Followed

### ✅ PSR-12 Standards

-   4 spaces indentation
-   Opening braces on new lines for classes/methods
-   Proper namespacing and imports
-   Type hints and return types

### ✅ SOLID Principles

-   **Single Responsibility**: Each class has one reason to change
-   **Open/Closed**: Extensible without modification
-   **Liskov Substitution**: Proper inheritance and interfaces
-   **Interface Segregation**: Focused, specific interfaces
-   **Dependency Inversion**: Depend on abstractions

### ✅ Laravel Best Practices

-   **Thin Controllers**: Only handle HTTP concerns
-   **Service Classes**: All business logic
-   **Repository Pattern**: All database operations
-   **Form Requests**: Validation separated
-   **API Resources**: Consistent responses
-   **Middleware**: Authorization logic

## Database Schema

### Users Table

-   `id` - Primary key
-   `name` - User's full name
-   `email` - Unique email address
-   `password` - Hashed password
-   `is_admin` - Boolean flag for admin access
-   `timestamps`

### Bookings Table

-   `id` - Primary key
-   `user_id` - Foreign key to users
-   `date` - Booking date
-   `start_time` - Start time (HH:MM)
-   `end_time` - End time (HH:MM)
-   `timestamps`

## Conflict Detection Logic

The `ConflictCheckService` provides three types of analysis:

1. **Overlapping Bookings**: Different bookings that overlap in time on the same date
2. **Exact Conflicts**: Multiple bookings with identical date and time
3. **Gaps**: Time gaps between consecutive bookings on the same date

## Admin Features

Users with `is_admin = true` can:

-   View all bookings from all users
-   Access comprehensive conflict reports
-   Still perform all regular user operations

Regular users can only:

-   View their own bookings
-   Create, update, delete their own bookings
-   Validate their own bookings for conflicts

## Security

-   **Sanctum Authentication**: Sanctum Cookie Mode + CSRF authentication for SPAs
-   **CORS Configured**: Allows requests from Vue frontend (localhost:5173)
-   **Password Hashing**: Bcrypt with configurable rounds
-   **Authorization Checks**: Middleware and controller-level checks
-   **SQL Injection Protection**: Eloquent ORM with prepared statements
-   **CSRF Protection**: Sanctum CSRF cookie for stateful requests

## Configuration

Key configuration files:

-   `config/sanctum.php` - Sanctum settings
-   `config/cors.php` - CORS configuration
-   `config/database.php` - Database settings
-   `bootstrap/app.php` - Application bootstrap with middleware

## Development Notes

### Creating an Admin User

```php
// In tinker: php artisan tinker
$user = User::create([
    'name' => 'Admin User',
    'email' => 'admin@example.com',
    'password' => Hash::make('password'),
    'is_admin' => true
]);
```

### Checking Scheduled Tasks

```bash
php artisan schedule:list
```

### Running Tests

```bash
php artisan test
```

## License

This project is open-sourced software.
