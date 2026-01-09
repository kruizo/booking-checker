# Booking Checker API - Test Requests

## 1. Register Users

### Register Regular User

```bash
curl -X POST http://127.0.0.1:8000/api/v1/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

### Register Admin User

```bash
curl -X POST http://127.0.0.1:8000/api/v1/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Admin User",
    "email": "admin@example.com",
    "password": "admin123",
    "password_confirmation": "admin123",
    "is_admin": true
  }'
```

### Register Another User (Jane)

```bash
curl -X POST http://127.0.0.1:8000/api/v1/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Jane Smith",
    "email": "jane@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

## 2. Login

### Login as John

```bash
curl -X POST http://127.0.0.1:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "password123"
  }'
```

### Booking 1 - John (9:00 - 10:00)

```bash
curl -X POST http://127.0.0.1:8000/api/v1/bookings \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "date": "2026-01-15",
    "start_time": "09:00",
    "end_time": "10:00"
  }'
```

### Booking 2 - John (11:00 - 12:00) - GAP with Booking 1

```bash
curl -X POST http://127.0.0.1:8000/api/v1/bookings \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "date": "2026-01-15",
    "start_time": "11:00",
    "end_time": "12:00"
  }'
```

### Booking 3 - John (14:00 - 15:00)

```bash
curl -X POST http://127.0.0.1:8000/api/v1/bookings \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "date": "2026-01-16",
    "start_time": "14:00",
    "end_time": "15:00"
  }'
```

**Now login as Jane to create overlapping bookings:**

### Login as Jane

```bash
curl -X POST http://127.0.0.1:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "jane@example.com",
    "password": "password123"
  }'
```

### Booking 4 - Jane (09:30 - 10:30) - OVERLAPS with Booking 1

```bash
curl -X POST http://127.0.0.1:8000/api/v1/bookings \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer JANE_TOKEN" \
  -d '{
    "date": "2026-01-15",
    "start_time": "09:30",
    "end_time": "10:30"
  }'
```

### Booking 5 - Jane (14:00 - 15:00) - EXACT CONFLICT with Booking 3

```bash
curl -X POST http://127.0.0.1:8000/api/v1/bookings \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer JANE_TOKEN" \
  -d '{
    "date": "2026-01-16",
    "start_time": "14:00",
    "end_time": "15:00"
  }'
```

### Booking 6 - Jane (08:00 - 09:00) - Before Booking 1

```bash
curl -X POST http://127.0.0.1:8000/api/v1/bookings \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer JANE_TOKEN" \
  -d '{
    "date": "2026-01-15",
    "start_time": "08:00",
    "end_time": "09:00"
  }'
```

## 4. Get All Bookings

### As Regular User (sees only their own)

```bash
curl -X GET http://127.0.0.1:8000/api/v1/bookings \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### As Admin (sees all bookings)

```bash
curl -X GET http://127.0.0.1:8000/api/v1/bookings \
  -H "Authorization: Bearer ADMIN_TOKEN"
```

---

## 5. Validate Specific Booking

```bash
curl -X GET http://127.0.0.1:8000/api/v1/bookings/1/validate \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## 6. Admin Conflict Report

```bash
curl -X GET http://127.0.0.1:8000/api/v1/admin/conflicts \
  -H "Authorization: Bearer ADMIN_TOKEN"
```

## 7. Update Booking

```bash
curl -X PUT http://127.0.0.1:8000/api/v1/bookings/1 \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "date": "2026-01-17",
    "start_time": "10:00",
    "end_time": "11:30"
  }'
```

## 8. Delete Booking

```bash
curl -X DELETE http://127.0.0.1:8000/api/v1/bookings/1 \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## 9. Get Single Booking

```bash
curl -X GET http://127.0.0.1:8000/api/v1/bookings/1 \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## 10. Get Current User

```bash
curl -X GET http://127.0.0.1:8000/api/v1/user \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## 11. Logout

```bash
curl -X POST http://127.0.0.1:8000/api/v1/logout \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## 12. Health Check

```bash
curl -X GET http://127.0.0.1:8000/api/v1/health
```

## Error Responses

### 401 Unauthenticated

```bash
curl -X GET http://127.0.0.1:8000/api/v1/bookings
```

### 403 Forbidden (Non-admin accessing admin route)

```bash
curl -X GET http://127.0.0.1:8000/api/v1/admin/conflicts \
  -H "Authorization: Bearer NON_ADMIN_TOKEN"
```

### 404 Not Found

```bash
curl -X GET http://127.0.0.1:8000/api/v1/bookings/999 \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### 422 Validation Error

```bash
curl -X POST http://127.0.0.1:8000/api/v1/bookings \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "date": "2026-01-15",
    "start_time": "10:00",
    "end_time": "09:00"
  }'
```

## Quick Test Scenario Summary

**Conflict Detection Scenario:**

1. Register John & Jane
2. John creates booking: Jan 15, 9:00-10:00 (Booking 1)
3. John creates booking: Jan 15, 11:00-12:00 (Booking 2) → **Gap between 1 & 2**
4. John creates booking: Jan 16, 14:00-15:00 (Booking 3)
5. Jane creates booking: Jan 15, 9:30-10:30 (Booking 4) → **Overlaps with Booking 1**
6. Jane creates booking: Jan 16, 14:00-15:00 (Booking 5) → **Exact conflict with Booking 3**
7. Check conflicts as admin → See overlaps, exact conflicts, and gaps

**Expected Conflicts:**

- ✅ **1 Overlap**: Booking 1 & 4 (partial time overlap)
- ✅ **1 Exact Conflict**: Booking 3 & 5 (same date/time)
- ✅ **1 Gap**: Between Booking 1 (ends 10:00) and Booking 2 (starts 11:00) = 60 minutes gap
