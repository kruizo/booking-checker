# API Endpoints Documentation

## Authentication

### POST /api/v1/auth/register

Purpose: Register a new user
Request:

```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

Success Response (200):

```json
{
  "status": 200,
  "errorCode": null,
  "message": "Registration successful",
  "timestamp": "2026-01-08T15:45:10+00:00",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "is_admin": false,
      "created_at": "2026-01-08T15:45:10+00:00",
      "token": "1|abc123tokenhere...",
      "token_type": "Bearer"
    }
  }
}
```

Error Response (422 - Validation):

```json
{
  "status": 422,
  "errorCode": "VALIDATION_ERROR",
  "message": "The email has already been taken.",
  "timestamp": "2026-01-08T15:45:10+00:00",
  "errors": {
    "email": ["The email has already been taken."],
    "password": ["The password field must be at least 8 characters."]
  }
}
```

---

### POST /api/v1/auth/login

Purpose: Login user (returns cookie session + token for mobile)
Request:

```json
{
  "email": "john@example.com",
  "password": "password123"
}
```

Success Response (200):

```json
{
  "status": 200,
  "errorCode": null,
  "message": "Login successful",
  "timestamp": "2026-01-08T15:50:00+00:00",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "is_admin": false,
      "created_at": "2026-01-08T15:45:10+00:00",
      "token": "2|xyz789tokenhere...",
      "token_type": "Bearer"
    }
  }
}
```

Error Response (422 - Invalid Credentials):

```json
{
  "status": 422,
  "errorCode": "VALIDATION_ERROR",
  "message": "The provided credentials are incorrect.",
  "timestamp": "2026-01-08T15:50:00+00:00",
  "errors": {
    "email": ["The provided credentials are incorrect."]
  }
}
```

---

### POST /api/v1/logout

Purpose: Logout user (requires auth)
Success Response (200):

```json
{
  "status": 200,
  "errorCode": null,
  "message": "Logged out successfully",
  "timestamp": "2026-01-08T16:00:00+00:00",
  "data": null
}
```

Error Response (401 - Unauthenticated):

```json
{
  "status": 401,
  "errorCode": "UNAUTHENTICATED",
  "message": "Unauthenticated.",
  "timestamp": "2026-01-08T16:00:00+00:00"
}
```

---

### GET /api/v1/user

Purpose: Get current authenticated user
Success Response (200):

```json
{
  "status": 200,
  "errorCode": null,
  "message": "User retrieved successfully",
  "timestamp": "2026-01-08T16:00:00+00:00",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "is_admin": false,
      "created_at": "2026-01-08T15:45:10+00:00"
    }
  }
}
```

### PATCH /api/v1/user/{id}/permission

Purpose: Change user permission (toggles oneself permission to admin or user)
Success Response (200):

```json
{
  "status": 200,
  "errorCode": null,
  "message": "User permission updated successfully",
  "timestamp": "2026-01-08T16:00:00+00:00",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "is_admin": false,
      "created_at": "2026-01-08T15:45:10+00:00"
    }
  }
}
```

---

### PUT /api/v1/user

Purpose: Update current user's profile
Request:

```json
{
  "name": "John Updated",
  "email": "john.updated@example.com",
  "password": "newpassword123",
  "password_confirmation": "newpassword123"
}
```

Success Response (200):

```json
{
  "status": 200,
  "errorCode": null,
  "message": "Profile updated successfully",
  "timestamp": "2026-01-08T16:05:00+00:00",
  "data": {
    "user": {
      "id": 1,
      "name": "John Updated",
      "email": "john.updated@example.com",
      "is_admin": false,
      "created_at": "2026-01-08T15:45:10+00:00"
    }
  }
}
```

---

## Bookings

### GET /api/v1/bookings

Purpose: Get all bookings (paginated). Admin sees all, user sees own bookings.
Query Parameters:

- `page` - Page number (default: 1)
- `per_page` - Items per page (default: 15, max: 100)
- `sort_by` - Sort field: id, date, start_time, end_time, created_at (default: date)
- `sort_direction` - asc or desc (default: desc)
- `date` - Filter by exact date (YYYY-MM-DD)
- `date_from` - Filter from date
- `date_to` - Filter to date
- `start_time` - Filter by start time (HH:MM)
- `end_time` - Filter by end time (HH:MM)
- `keyword` - (Admin only) Search by user name/email

Success Response (200):

```json
{
  "status": 200,
  "errorCode": null,
  "message": "Bookings retrieved successfully",
  "timestamp": "2026-01-08T18:07:50+00:00",
  "data": [
    {
      "id": 3,
      "user_id": 4,
      "user": {
        "id": 4,
        "name": "John Doe",
        "email": "ken4@example.com",
        "is_admin": true,
        "created_at": "2026-01-08T15:51:06+00:00"
      },
      "date": "2026-03-15",
      "start_time": "09:00",
      "end_time": "10:00",
      "created_at": "2026-01-08T16:11:34+00:00",
      "updated_at": "2026-01-08T16:11:34+00:00"
    }
  ],
  "pagination": {
    "current_page": 1,
    "per_page": 15,
    "total": 3,
    "total_pages": 1,
    "has_next_page": false,
    "has_prev_page": false,
    "from": 1,
    "to": 3
  }
}
```

---

### POST /api/v1/bookings

Purpose: Create a new booking
Request:

```json
{
  "date": "2026-05-15",
  "start_time": "12:00",
  "end_time": "14:00"
}
```

Success Response (200):

```json
{
  "status": 200,
  "errorCode": null,
  "message": "Booking created successfully",
  "timestamp": "2026-01-08T18:14:40+00:00",
  "data": {
    "booking": {
      "id": 8,
      "user_id": 4,
      "date": "2026-05-15",
      "start_time": "12:00",
      "end_time": "14:00",
      "created_at": "2026-01-08T18:14:40+00:00",
      "updated_at": "2026-01-08T18:14:40+00:00"
    }
  }
}
```

Error Response (422 - Validation):

```json
{
  "status": 422,
  "errorCode": "VALIDATION_ERROR",
  "message": "The date field is required.",
  "timestamp": "2026-01-08T18:14:40+00:00",
  "errors": {
    "date": ["The date field is required."],
    "start_time": ["The start time field is required."],
    "end_time": ["The end time must be after start time."]
  }
}
```

Error Response (422 - Time Overlap):

```json
{
  "status": 422,
  "errorCode": "VALIDATION_ERROR",
  "message": "This booking overlaps with an existing booking.",
  "timestamp": "2026-01-08T18:14:40+00:00",
  "errors": {
    "time": ["This booking overlaps with an existing booking."]
  }
}
```

---

### GET /api/v1/bookings/{id}

Purpose: Get a specific booking
Success Response (200):

```json
{
  "status": 200,
  "errorCode": null,
  "message": "Booking retrieved successfully",
  "timestamp": "2026-01-08T18:10:00+00:00",
  "data": {
    "booking": {
      "id": 8,
      "user_id": 4,
      "user": {
        "id": 4,
        "name": "John Doe",
        "email": "ken4@example.com",
        "is_admin": true,
        "created_at": "2026-01-08T15:51:06+00:00"
      },
      "date": "2026-05-15",
      "start_time": "12:00",
      "end_time": "14:00",
      "created_at": "2026-01-08T18:14:40+00:00",
      "updated_at": "2026-01-08T18:14:40+00:00"
    }
  }
}
```

Error Response (404 - Not Found):

```json
{
  "status": 404,
  "errorCode": "RESOURCE_NOT_FOUND",
  "message": "Booking not found",
  "timestamp": "2026-01-08T18:10:00+00:00"
}
```

Error Response (403 - Forbidden):

```json
{
  "status": 403,
  "errorCode": "FORBIDDEN",
  "message": "Unauthorized to access this booking.",
  "timestamp": "2026-01-08T18:10:00+00:00"
}
```

---

### PUT /api/v1/bookings/{id}

Purpose: Update a booking
Request:

```json
{
  "date": "2026-05-16",
  "start_time": "10:00",
  "end_time": "12:00"
}
```

Success Response (200):

```json
{
  "status": 200,
  "errorCode": null,
  "message": "Booking updated successfully",
  "timestamp": "2026-01-08T18:20:00+00:00",
  "data": {
    "booking": {
      "id": 8,
      "user_id": 4,
      "date": "2026-05-16",
      "start_time": "10:00",
      "end_time": "12:00",
      "created_at": "2026-01-08T18:14:40+00:00",
      "updated_at": "2026-01-08T18:20:00+00:00"
    }
  }
}
```

---

### DELETE /api/v1/bookings/{id}

Purpose: Delete a booking
Success Response (200):

```json
{
  "status": 200,
  "errorCode": null,
  "message": "Booking deleted successfully",
  "timestamp": "2026-01-08T18:25:00+00:00",
  "data": null
}
```

---

### GET /api/v1/bookings/{id}/validate

Purpose: Check booking for conflicts before submitting
Success Response (200):

```json
{
  "status": 200,
  "errorCode": null,
  "message": "Booking validation completed",
  "timestamp": "2026-01-08T18:14:27+00:00",
  "data": {
    "booking": {
      "id": 7,
      "user_id": 4,
      "user": {
        "id": 4,
        "name": "John Doe",
        "email": "ken4@example.com",
        "is_admin": true,
        "created_at": "2026-01-08T15:51:06+00:00"
      },
      "date": "2026-05-15",
      "start_time": "12:00",
      "end_time": "13:00",
      "created_at": "2026-01-08T18:13:47+00:00",
      "updated_at": "2026-01-08T18:13:47+00:00"
    },
    "has_conflicts": false,
    "overlapping": [],
    "conflicts": []
  }
}
```

---

## Admin Endpoints

### GET /api/v1/admin/users

Purpose: Get all users (admin only, paginated)
Query Parameters:

- `page` - Page number (default: 1)
- `per_page` - Items per page (default: 15, max: 100)
- `sort_by` - Sort field: id, name, email, created_at, is_admin (default: created_at)
- `sort_direction` - asc or desc (default: desc)
- `keyword` - Search by name or email

Success Response (200):

```json
{
  "status": 200,
  "errorCode": null,
  "message": "Users retrieved successfully",
  "timestamp": "2026-01-08T18:30:00+00:00",
  "data": [
    {
      "id": 4,
      "name": "John Doe",
      "email": "ken4@example.com",
      "is_admin": true,
      "created_at": "2026-01-08T15:51:06+00:00"
    },
    {
      "id": 3,
      "name": "Jane Doe",
      "email": "jane@example.com",
      "is_admin": false,
      "created_at": "2026-01-08T15:50:29+00:00"
    }
  ],
  "pagination": {
    "current_page": 1,
    "per_page": 15,
    "total": 4,
    "total_pages": 1,
    "has_next_page": false,
    "has_prev_page": false,
    "from": 1,
    "to": 4
  }
}
```

Error Response (403 - Not Admin):

```json
{
  "status": 403,
  "errorCode": "FORBIDDEN",
  "message": "Admin access required.",
  "timestamp": "2026-01-08T18:30:00+00:00"
}
```

---

### GET /api/v1/admin/users/{id}

Purpose: Get a specific user (admin only)
Success Response (200):

```json
{
  "status": 200,
  "errorCode": null,
  "message": "User retrieved successfully",
  "timestamp": "2026-01-08T18:32:00+00:00",
  "data": {
    "user": {
      "id": 3,
      "name": "Jane Doe",
      "email": "jane@example.com",
      "is_admin": false,
      "created_at": "2026-01-08T15:50:29+00:00"
    }
  }
}
```

---

### PUT /api/v1/admin/users/{id}

Purpose: Update any user (admin only)
Request:

```json
{
  "name": "Jane Updated",
  "email": "jane.updated@example.com"
}
```

Success Response (200):

```json
{
  "status": 200,
  "errorCode": null,
  "message": "User updated successfully",
  "timestamp": "2026-01-08T18:35:00+00:00",
  "data": {
    "user": {
      "id": 3,
      "name": "Jane Updated",
      "email": "jane.updated@example.com",
      "is_admin": false,
      "created_at": "2026-01-08T15:50:29+00:00"
    }
  }
}
```

---

### PATCH /api/v1/admin/users/{id}/permission

Purpose: Toggle user admin status (admin only)
Note: No request body needed - toggles current status
Success Response (200):

```json
{
  "status": 200,
  "errorCode": null,
  "message": "User permission updated successfully",
  "timestamp": "2026-01-08T18:40:00+00:00",
  "data": {
    "user": {
      "id": 3,
      "name": "Jane Doe",
      "email": "jane@example.com",
      "is_admin": true,
      "created_at": "2026-01-08T15:50:29+00:00"
    }
  }
}
```

Error Response (403 - Self Demotion):

```json
{
  "status": 403,
  "errorCode": "FORBIDDEN",
  "message": "You cannot remove your own admin status.",
  "timestamp": "2026-01-08T18:40:00+00:00"
}
```

---

### GET /api/v1/admin/conflicts

Purpose: Get conflict report for all bookings (admin only)
Success Response (200):

```json
{
  "status": 200,
  "errorCode": null,
  "message": "Conflict report generated successfully",
  "timestamp": "2026-01-08T18:14:49+00:00",
  "data": {
    "overlapping": [
      {
        "booking_1": {
          "id": 7,
          "user": "John Doe",
          "date": "2026-05-15",
          "start_time": "12:00",
          "end_time": "13:00"
        },
        "booking_2": {
          "id": 8,
          "user": "John Doe",
          "date": "2026-05-15",
          "start_time": "12:00",
          "end_time": "14:00"
        },
        "overlap_type": "partial"
      }
    ],
    "conflicts": [
      {
        "booking_1": {
          "id": 5,
          "user": "John Doe",
          "date": "2026-05-15",
          "start_time": "09:00",
          "end_time": "10:00"
        },
        "booking_2": {
          "id": 6,
          "user": "John Doe",
          "date": "2026-05-15",
          "start_time": "09:00",
          "end_time": "10:00"
        },
        "conflict_type": "exact_match"
      }
    ],
    "gaps": [
      {
        "date": "2026-05-15",
        "between_bookings": [
          {
            "id": 6,
            "user": "John Doe",
            "end_time": "10:00"
          },
          {
            "id": 7,
            "user": "John Doe",
            "start_time": "12:00"
          }
        ],
        "gap_duration_minutes": 120,
        "gap_start": "10:00",
        "gap_end": "12:00"
      }
    ],
    "summary": {
      "total_bookings": 7,
      "overlapping_count": 1,
      "conflict_count": 1,
      "gap_count": 1
    }
  }
}
```

---

### GET /api/v1/admin/statistics

Purpose: Get dashboard statistics (admin only)
Query Parameters:

- `period` - daily, weekly, or yearly (default: daily)

Success Response (200):

```json
{
  "status": 200,
  "errorCode": null,
  "message": "Statistics retrieved successfully",
  "timestamp": "2026-01-08T18:15:52+00:00",
  "data": {
    "period": "daily",
    "intervals": [
      {
        "date": "2026-01-02",
        "bookings": 0,
        "signups": 0
      },
      {
        "date": "2026-01-03",
        "bookings": 0,
        "signups": 0
      },
      {
        "date": "2026-01-04",
        "bookings": 0,
        "signups": 0
      },
      {
        "date": "2026-01-05",
        "bookings": 0,
        "signups": 0
      },
      {
        "date": "2026-01-06",
        "bookings": 0,
        "signups": 0
      },
      {
        "date": "2026-01-07",
        "bookings": 0,
        "signups": 0
      },
      {
        "date": "2026-01-08",
        "bookings": 7,
        "signups": 4
      }
    ],
    "recent_signups": [
      {
        "id": 4,
        "name": "John Doe",
        "email": "ken4@example.com",
        "created_at": "2026-01-08T15:51:06.000000Z"
      },
      {
        "id": 3,
        "name": "John Doe",
        "email": "ken3@example.com",
        "created_at": "2026-01-08T15:50:29.000000Z"
      }
    ],
    "summary": {
      "total_bookings": 7,
      "total_signups": 4
    }
  }
}
```

---

## Utility Endpoints

### GET /api/v1/health

Purpose: Health check
Response:

```json
{
  "status": "ok",
  "timestamp": "2026-01-08T18:00:00+00:00"
}
```

---

### GET /sanctum/csrf-cookie

Purpose: Get CSRF cookie for SPA authentication
Response: Sets `XSRF-TOKEN` cookie (no body)

---

## Common Error Responses

### 401 - Unauthenticated

```json
{
  "status": 401,
  "errorCode": "UNAUTHENTICATED",
  "message": "Unauthenticated.",
  "timestamp": "2026-01-08T18:00:00+00:00"
}
```

### 403 - Forbidden

```json
{
  "status": 403,
  "errorCode": "FORBIDDEN",
  "message": "This action is unauthorized.",
  "timestamp": "2026-01-08T18:00:00+00:00"
}
```

### 404 - Not Found

```json
{
  "status": 404,
  "errorCode": "RESOURCE_NOT_FOUND",
  "message": "Resource not found",
  "timestamp": "2026-01-08T18:00:00+00:00"
}
```

### 422 - Validation Error

```json
{
  "status": 422,
  "errorCode": "VALIDATION_ERROR",
  "message": "The given data was invalid.",
  "timestamp": "2026-01-08T18:00:00+00:00",
  "errors": {
    "field_name": ["Error message for this field."]
  }
}
```

### 500 - Server Error

```json
{
  "status": 500,
  "errorCode": "INTERNAL_SERVER_ERROR",
  "message": "Internal server error has occurred",
  "timestamp": "2026-01-08T18:00:00+00:00"
}
```

Note: In debug mode, the actual error message is shown. In production, a generic message is returned for security.
