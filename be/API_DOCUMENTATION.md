# User API Documentation

This document provides a comprehensive overview of the User API endpoints available in our platform.

## Base URL

All URLs referenced in the documentation have the following base:

```
http://your-domain.com/api/v1
```

## Authentication

Authentication requirements are not specified in this documentation. Check with the development team for authentication requirements.

## Response Format

All responses are returned in JSON format with appropriate HTTP status codes.

## Health Check

### Check API Health

```
GET /health
```

Returns the health status of the API.

## User Management

### List All Users

```
GET /users
```

Returns a paginated list of all users.

**Query Parameters**

| Parameter     | Type    | Description                                 |
|---------------|---------|---------------------------------------------|
| page          | integer | Page number for pagination                  |
| per_page      | integer | Number of results per page                  |
| search        | string  | Search term to filter users                 |
| role          | string  | Filter by user role                         |
| is_active     | boolean | Filter by active status                     |
| is_teacher    | boolean | Filter by teacher status                    |
| domain        | string  | Filter by email domain                      |
| sort_by       | string  | Field to sort by                            |
| sort_dir      | string  | Sort direction ('asc' or 'desc')            |

### Get User Count

```
GET /users/count
```

Returns the total count of users in the system.

### Get Active Users

```
GET /users/active
```

Returns a list of all active users.

### Get Users by Role

```
GET /users/role/{role}
```

Returns users with a specific role.

**Path Parameters**

| Parameter | Type   | Description            |
|-----------|--------|------------------------|
| role      | string | Role to filter by      |

### Get Teachers

```
GET /users/teachers
```

Returns a list of all users who are teachers.

### Get Users by Domain

```
GET /users/domain/{domain}
```

Returns users with email addresses in a specific domain.

**Path Parameters**

| Parameter | Type   | Description            |
|-----------|--------|------------------------|
| domain    | string | Email domain to filter by |

### Get User Statistics

```
GET /users/statistics
```

Returns statistical information about users in the system.

### Create User

```
POST /users
```

Creates a new user.

**Request Body**

| Field       | Type    | Required | Description                   |
|-------------|---------|----------|-------------------------------|
| name        | string  | Yes      | User's full name              |
| email       | string  | Yes      | User's email address          |
| password    | string  | Yes      | User's password               |
| role        | string  | No       | User's role                   |
| is_active   | boolean | No       | Whether user is active        |
| is_teacher  | boolean | No       | Whether user is a teacher     |
| phone       | string  | No       | User's phone number           |

### Get User Details

```
GET /users/{id}
```

Returns details for a specific user.

**Path Parameters**

| Parameter | Type    | Description         |
|-----------|---------|---------------------|
| id        | integer | User ID             |

### Update User

```
PUT /users/{id}
```

Updates an existing user.

**Path Parameters**

| Parameter | Type    | Description         |
|-----------|---------|---------------------|
| id        | integer | User ID             |

**Request Body**

Same fields as Create User, all optional.

### Delete User

```
DELETE /users/{id}
```

Deletes a user (soft delete).

**Path Parameters**

| Parameter | Type    | Description         |
|-----------|---------|---------------------|
| id        | integer | User ID             |

### Toggle User Status

```
PUT /users/{id}/toggle-status
```

Toggles a user's active status.

**Path Parameters**

| Parameter | Type    | Description         |
|-----------|---------|---------------------|
| id        | integer | User ID             |

### Toggle Teacher Status

```
PUT /users/{id}/toggle-teacher
```

Toggles a user's teacher status.

**Path Parameters**

| Parameter | Type    | Description         |
|-----------|---------|---------------------|
| id        | integer | User ID             |

## User Profile Management

### Get User Profile

```
GET /profile
```

Returns the profile of the currently authenticated user.

### Update User Profile

```
PUT /profile
```

Updates the profile of the currently authenticated user.

**Request Body**

| Field       | Type    | Required | Description                   |
|-------------|---------|----------|-------------------------------|
| name        | string  | No       | User's full name              |
| email       | string  | No       | User's email address          |
| password    | string  | No       | User's password               |
| phone       | string  | No       | User's phone number           |

## Authentication and Email Verification

### Send Verification Email

```
POST /users/{id}/send-verification
```

Sends a verification email to a specific user.

**Path Parameters**

| Parameter | Type    | Description         |
|-----------|---------|---------------------|
| id        | integer | User ID             |

### Verify Email

```
GET /users/verify-email/{token}
```

Verifies a user's email address using a token.

**Path Parameters**

| Parameter | Type   | Description                    |
|-----------|--------|--------------------------------|
| token     | string | Email verification token       |

### Request Password Reset

```
POST /users/request-password-reset
```

Requests a password reset for a user.

**Request Body**

| Field       | Type    | Required | Description                   |
|-------------|---------|----------|-------------------------------|
| email       | string  | Yes      | User's email address          |

### Reset Password

```
POST /users/reset-password
```

Resets a user's password using a token.

**Request Body**

| Field       | Type    | Required | Description                   |
|-------------|---------|----------|-------------------------------|
| email       | string  | Yes      | User's email address          |
| token       | string  | Yes      | Password reset token          |
| password    | string  | Yes      | New password                  |

## User Activity and Metrics

### Get User Activity Log

```
GET /users/{id}/activity
```

Returns activity history for a specific user.

**Path Parameters**

| Parameter | Type    | Description         |
|-----------|---------|---------------------|
| id        | integer | User ID             |

### Get User Metrics

```
GET /users/{id}/metrics
```

Returns usage metrics for a specific user.

**Path Parameters**

| Parameter | Type    | Description         |
|-----------|---------|---------------------|
| id        | integer | User ID             |

## Error Responses

The API may return the following error status codes:

| Status Code | Description                                     |
|-------------|-------------------------------------------------|
| 400         | Bad Request - Invalid parameters                |
| 401         | Unauthorized - Authentication required          |
| 403         | Forbidden - Insufficient permissions            |
| 404         | Not Found - Resource not found                  |
| 422         | Unprocessable Entity - Validation errors        |
| 500         | Internal Server Error - Something went wrong    |

Error responses will include a message explaining what went wrong.
