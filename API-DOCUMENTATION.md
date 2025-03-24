# Application Management REST API Documentation

This documentation provides details on all available endpoints, request formats, and responses for the Application Management REST API.

## Base URL

```
http://your-api-domain.com/api
```

For local development:
```
http://localhost:8000/api
```

## Authentication

The API uses token-based authentication with Laravel Sanctum. Once logged in, you'll receive a token that must be included in all subsequent requests.

Include the token in your request headers:
```
Authorization: Bearer {your_token}
```

## Response Format

All responses follow a standard format:

```json
{
  "success": true,
  "status_code": 200,
  "message": "Operation successful",
  "data": {
    // Response data here
  }
}
```

For errors:
```json
{
  "success": false,
  "status_code": 400,
  "message": "Error message",
  "errors": {
    // Validation errors or other error details
  }
}
```

## API Endpoints

### Authentication

#### Register a New User

```
POST /register
```

**Request Body:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "role": "candidate" // or "recruiter"
}
```

**Response:**
```json
{
  "success": true,
  "status_code": 201,
  "message": "User registered successfully",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "role": "candidate",
      "created_at": "2025-03-13T09:50:11.000000Z",
      "updated_at": "2025-03-13T09:50:11.000000Z"
    },
    "token": "your_authentication_token"
  }
}
```

#### Login

```
POST /login
```

**Request Body:**
```json
{
  "email": "john@example.com",
  "password": "password123"
}
```

**Response:**
```json
{
  "success": true,
  "status_code": 200,
  "message": "Logged in successfully",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "role": "candidate",
      "created_at": "2025-03-13T09:50:11.000000Z",
      "updated_at": "2025-03-13T09:50:11.000000Z"
    },
    "token": "your_authentication_token",
    "redirect": "/candidate/dashboard"
  }
}
```

#### Logout

```
POST /logout
```

**Headers:**
```
Authorization: Bearer {your_token}
```

**Response:**
```json
{
  "success": true,
  "status_code": 200,
  "message": "Logged out successfully"
}
```

### User Profile

#### Get User Profile

```
GET /profile
```

**Headers:**
```
Authorization: Bearer {your_token}
```

**Response:**
```json
{
  "success": true,
  "status_code": 200,
  "data": {
    "profile": {
      "id": 1,
      "user_id": 1,
      "phone_number": "555-123-4567",
      "skills": "JavaScript, PHP, Laravel",
      "created_at": "2025-03-13T09:50:11.000000Z",
      "updated_at": "2025-03-13T09:50:11.000000Z"
    }
  }
}
```

#### Update User Profile

```
PUT /profile
```

**Headers:**
```
Authorization: Bearer {your_token}
```

**Request Body:**
```json
{
  "phone_number": "555-123-4567",
  "skills": "JavaScript, PHP, Laravel, MySQL"
}
```

**Response:**
```json
{
  "success": true,
  "status_code": 200,
  "message": "Profile updated successfully",
  "data": {
    "profile": {
      "id": 1,
      "user_id": 1,
      "phone_number": "555-123-4567",
      "skills": "JavaScript, PHP, Laravel, MySQL",
      "created_at": "2025-03-13T09:50:11.000000Z",
      "updated_at": "2025-03-13T10:15:22.000000Z"
    }
  }
}
```

### CV Management

#### List User's CVs

```
GET /cvs
```

**Headers:**
```
Authorization: Bearer {your_token}
```

**Response:**
```json
{
  "success": true,
  "status_code": 200,
  "data": {
    "cvs": [
      {
        "id": 1,
        "user_id": 1,
        "file_path": "cvs/1647253871_resume.pdf",
        "file_name": "1647253871_resume.pdf",
        "mime_type": "application/pdf",
        "file_size": 245678,
        "summary": null,
        "created_at": "2025-03-13T10:30:11.000000Z",
        "updated_at": "2025-03-13T10:30:11.000000Z"
      }
    ]
  }
}
```

#### Upload CV

```
POST /cvs
```

**Headers:**
```
Authorization: Bearer {your_token}
Content-Type: multipart/form-data
```

**Request Body:**
```
cv: [File upload]
```

**Response:**
```json
{
  "success": true,
  "status_code": 201,
  "message": "CV uploaded successfully",
  "data": {
    "cv": {
      "id": 1,
      "user_id": 1,
      "file_path": "cvs/1647253871_resume.pdf",
      "file_name": "1647253871_resume.pdf",
      "mime_type": "application/pdf",
      "file_size": 245678,
      "summary": null,
      "created_at": "2025-03-13T10:30:11.000000Z",
      "updated_at": "2025-03-13T10:30:11.000000Z"
    }
  }
}
```

#### Get CV Details

```
GET /cvs/{cv_id}
```

**Headers:**
```
Authorization: Bearer {your_token}
```

**Response:**
```json
{
  "success": true,
  "status_code": 200,
  "data": {
    "cv": {
      "id": 1,
      "user_id": 1,
      "file_path": "cvs/1647253871_resume.pdf",
      "file_name": "1647253871_resume.pdf",
      "mime_type": "application/pdf",
      "file_size": 245678,
      "summary": null,
      "created_at": "2025-03-13T10:30:11.000000Z",
      "updated_at": "2025-03-13T10:30:11.000000Z"
    }
  }
}
```

#### Delete CV

```
DELETE /cvs/{cv_id}
```

**Headers:**
```
Authorization: Bearer {your_token}
```

**Response:**
```json
{
  "success": true,
  "status_code": 200,
  "message": "CV deleted successfully"
}
```

### Job Offers

#### List Job Offers

```
GET /job-offers
```

**Query Parameters:**
```
category: Software Development (optional)
location: Remote (optional)
contract_type: Full-time (optional)
```

**Response:**
```json
{
  "success": true,
  "status_code": 200,
  "data": {
    "job_offers": {
      "current_page": 1,
      "data": [
        {
          "id": 1,
          "title": "Senior Laravel Developer",
          "description": "We are looking for an experienced Laravel developer...",
          "category": "Software Development",
          "location": "Remote",
          "contract_type": "Full-time",
          "salary": "75000.00",
          "posted_at": "2025-03-13T11:00:00.000000Z",
          "recruiter_id": 2,
          "created_at": "2025-03-13T11:00:00.000000Z",
          "updated_at": "2025-03-13T11:00:00.000000Z"
        }
      ],
      "first_page_url": "http://localhost:8000/api/job-offers?page=1",
      "from": 1,
      "last_page": 1,
      "last_page_url": "http://localhost:8000/api/job-offers?page=1",
      "links": [
        {
          "url": null,
          "label": "&laquo; Previous",
          "active": false
        },
        {
          "url": "http://localhost:8000/api/job-offers?page=1",
          "label": "1",
          "active": true
        },
        {
          "url": null,
          "label": "Next &raquo;",
          "active": false
        }
      ],
      "next_page_url": null,
      "path": "http://localhost:8000/api/job-offers",
      "per_page": 10,
      "prev_page_url": null,
      "to": 1,
      "total": 1
    }
  }
}
```

#### Create Job Offer (Recruiter Only)

```
POST /job-offers
```

**Headers:**
```
Authorization: Bearer {your_token}
```

**Request Body:**
```json
{
  "title": "Senior Laravel Developer",
  "description": "We are looking for an experienced Laravel developer...",
  "category": "Software Development",
  "location": "Remote",
  "contract_type": "Full-time",
  "salary": 75000
}
```

**Response:**
```json
{
  "success": true,
  "status_code": 201,
  "message": "Job offer created successfully",
  "data": {
    "job_offer": {
      "id": 1,
      "title": "Senior Laravel Developer",
      "description": "We are looking for an experienced Laravel developer...",
      "category": "Software Development",
      "location": "Remote",
      "contract_type": "Full-time",
      "salary": "75000.00",
      "posted_at": "2025-03-13T11:00:00.000000Z",
      "recruiter_id": 2,
      "created_at": "2025-03-13T11:00:00.000000Z",
      "updated_at": "2025-03-13T11:00:00.000000Z"
    }
  }
}
```

#### Get Job Offer Details

```
GET /job-offers/{job_offer_id}
```

**Response:**
```json
{
  "success": true,
  "status_code": 200,
  "data": {
    "job_offer": {
      "id": 1,
      "title": "Senior Laravel Developer",
      "description": "We are looking for an experienced Laravel developer...",
      "category": "Software Development",
      "location": "Remote",
      "contract_type": "Full-time",
      "salary": "75000.00",
      "posted_at": "2025-03-13T11:00:00.000000Z",
      "recruiter_id": 2,
      "created_at": "2025-03-13T11:00:00.000000Z",
      "updated_at": "2025-03-13T11:00:00.000000Z"
    }
  }
}
```

#### Update Job Offer (Recruiter Only)

```
PUT /job-offers/{job_offer_id}
```

**Headers:**
```
Authorization: Bearer {your_token}
```

**Request Body:**
```json
{
  "title": "Senior Laravel Developer",
  "description": "Updated job description...",
  "category": "Software Development",
  "location": "Remote",
  "contract_type": "Full-time",
  "salary": 80000
}
```

**Response:**
```json
{
  "success": true,
  "status_code": 200,
  "message": "Job offer updated successfully",
  "data": {
    "job_offer": {
      "id": 1,
      "title": "Senior Laravel Developer",
      "description": "Updated job description...",
      "category": "Software Development",
      "location": "Remote",
      "contract_type": "Full-time",
      "salary": "80000.00",
      "posted_at": "2025-03-13T11:00:00.000000Z",
      "recruiter_id": 2,
      "created_at": "2025-03-13T11:00:00.000000Z",
      "updated_at": "2025-03-13T11:30:00.000000Z"
    }
  }
}
```

#### Delete Job Offer (Recruiter Only)

```
DELETE /job-offers/{job_offer_id}
```

**Headers:**
```
Authorization: Bearer {your_token}
```

**Response:**
```json
{
  "success": true,
  "status_code": 200,
  "message": "Job offer deleted successfully"
}
```

### Applications

#### List Applications

```
GET /applications
```

**Headers:**
```
Authorization: Bearer {your_token}
```

**Response for Candidates:**
```json
{
  "success": true,
  "status_code": 200,
  "data": {
    "applications": [
      {
        "id": 1,
        "user_id": 1,
        "job_offer_id": 1,
        "cv_id": 1,
        "status": "pending",
        "cover_letter": "I am very interested in this position...",
        "created_at": "2025-03-13T12:00:00.000000Z",
        "updated_at": "2025-03-13T12:00:00.000000Z",
        "job_offer": {
          "id": 1,
          "title": "Senior Laravel Developer",
          "description": "We are looking for an experienced Laravel developer...",
          "category": "Software Development",
          "location": "Remote",
          "contract_type": "Full-time",
          "salary": "80000.00",
          "posted_at": "2025-03-13T11:00:00.000000Z",
          "recruiter_id": 2,
          "created_at": "2025-03-13T11:00:00.000000Z",
          "updated_at": "2025-03-13T11:30:00.000000Z"
        },
        "cv": {
          "id": 1,
          "user_id": 1,
          "file_path": "cvs/1647253871_resume.pdf",
          "file_name": "1647253871_resume.pdf",
          "mime_type": "application/pdf",
          "file_size": 245678,
          "summary": null,
          "created_at": "2025-03-13T10:30:11.000000Z",
          "updated_at": "2025-03-13T10:30:11.000000Z"
        }
      }
    ]
  }
}
```

**Response for Recruiters:**
```json
{
  "success": true,
  "status_code": 200,
  "data": {
    "applications": [
      {
        "id": 1,
        "user_id": 1,
        "job_offer_id": 1,
        "cv_id": 1,
        "status": "pending",
        "cover_letter": "I am very interested in this position...",
        "created_at": "2025-03-13T12:00:00.000000Z",
        "updated_at": "2025-03-13T12:00:00.000000Z",
        "user": {
          "id": 1,
          "name": "John Doe",
          "email": "john@example.com",
          "role": "candidate",
          "created_at": "2025-03-13T09:50:11.000000Z",
          "updated_at": "2025-03-13T09:50:11.000000Z"
        },
        "job_offer": {
          "id": 1,
          "title": "Senior Laravel Developer",
          "description": "We are looking for an experienced Laravel developer...",
          "category": "Software Development",
          "location": "Remote",
          "contract_type": "Full-time",
          "salary": "80000.00",
          "posted_at": "2025-03-13T11:00:00.000000Z",
          "recruiter_id": 2,
          "created_at": "2025-03-13T11:00:00.000000Z",
          "updated_at": "2025-03-13T11:30:00.000000Z"
        },
        "cv": {
          "id": 1,
          "user_id": 1,
          "file_path": "cvs/1647253871_resume.pdf",
          "file_name": "1647253871_resume.pdf",
          "mime_type": "application/pdf",
          "file_size": 245678,
          "summary": null,
          "created_at": "2025-03-13T10:30:11.000000Z",
          "updated_at": "2025-03-13T10:30:11.000000Z"
        }
      }
    ]
  }
}
```

#### Apply for a Job (Candidate Only)

```
POST /applications
```

**Headers:**
```
Authorization: Bearer {your_token}
```

**Request Body:**
```json
{
  "job_offer_id": 1,
  "cv_id": 1,
  "cover_letter": "I am very interested in this position..."
}
```

**Response:**
```json
{
  "success": true,
  "status_code": 201,
  "message": "Application submitted successfully",
  "data": {
    "application": {
      "id": 1,
      "user_id": 1,
      "job_offer_id": 1,
      "cv_id": 1,
      "status": "pending",
      "cover_letter": "I am very interested in this position...",
      "created_at": "2025-03-13T12:00:00.000000Z",
      "updated_at": "2025-03-13T12:00:00.000000Z"
    }
  }
}
```

#### Get Application Details

```
GET /applications/{application_id}
```

**Headers:**
```
Authorization: Bearer {your_token}
```

**Response:**
```json
{
  "success": true,
  "status_code": 200,
  "data": {
    "application": {
      "id": 1,
      "user_id": 1,
      "job_offer_id": 1,
      "cv_id": 1,
      "status": "pending",
      "cover_letter": "I am very interested in this position...",
      "created_at": "2025-03-13T12:00:00.000000Z",
      "updated_at": "2025-03-13T12:00:00.000000Z",
      "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "role": "candidate",
        "created_at": "2025-03-13T09:50:11.000000Z",
        "updated_at": "2025-03-13T09:50:11.000000Z"
      },
      "job_offer": {
        "id": 1,
        "title": "Senior Laravel Developer",
        "description": "We are looking for an experienced Laravel developer...",
        "category": "Software Development",
        "location": "Remote",
        "contract_type": "Full-time",
        "salary": "80000.00",
        "posted_at": "2025-03-13T11:00:00.000000Z",
        "recruiter_id": 2,
        "created_at": "2025-03-13T11:00:00.000000Z",
        "updated_at": "2025-03-13T11:30:00.000000Z"
      },
      "cv": {
        "id": 1,
        "user_id": 1,
        "file_path": "cvs/1647253871_resume.pdf",
        "file_name": "1647253871_resume.pdf",
        "mime_type": "application/pdf",
        "file_size": 245678,
        "summary": null,
        "created_at": "2025-03-13T10:30:11.000000Z",
        "updated_at": "2025-03-13T10:30:11.000000Z"
      }
    }
  }
}
```

#### Update Application Status (Recruiter Only)

```
PUT /applications/{application_id}
```

**Headers:**
```
Authorization: Bearer {your_token}
```

**Request Body:**
```json
{
  "status": "reviewed"
}
```

**Response:**
```json
{
  "success": true,
  "status_code": 200,
  "message": "Application status updated successfully",
  "data": {
    "application": {
      "id": 1,
      "user_id": 1,
      "job_offer_id": 1,
      "cv_id": 1,
      "status": "reviewed",
      "cover_letter": "I am very interested in this position...",
      "created_at": "2025-03-13T12:00:00.000000Z",
      "updated_at": "2025-03-13T12:30:00.000000Z"
    }
  }
}
```

#### Apply to Multiple Jobs (Candidate Only)

```
POST /apply-multiple
```

**Headers:**
```
Authorization: Bearer {your_token}
```

**Request Body:**
```json
{
  "job_offer_ids": [1, 2, 3],
  "cv_id": 1,
  "cover_letter": "I am very interested in these positions..."
}
```

**Response:**
```json
{
  "success": true,
  "status_code": 200,
  "message": "Multiple applications processed",
  "data": {
    "results": [
      {
        "job_offer_id": 1,
        "status": "success",
        "application_id": 1
      },
      {
        "job_offer_id": 2,
        "status": "success",
        "application_id": 2
      },
      {
        "job_offer_id": 3,
        "status": "success",
        "application_id": 3
      }
    ]
  }
}
```

## Error Responses

### Validation Error

```json
{
  "success": false,
  "status_code": 422,
  "message": "Validation failed",
  "errors": {
    "email": [
      "The email field is required."
    ],
    "password": [
      "The password field must be at least 8 characters."
    ]
  }
}
```

### Authentication Error

```json
{
  "success": false,
  "status_code": 401,
  "message": "Unauthenticated",
  "redirect": "/login"
}
```

### Authorization Error

```json
{
  "success": false,
  "status_code": 403,
  "message": "Unauthorized. You do not have the required role to access this resource.",
  "redirect": "/candidate/dashboard"
}
```

### Resource Not Found

```json
{
  "success": false,
  "status_code": 404,
  "message": "Resource not found"
}
```

## Status Codes

- `200 OK` - Request was successful
- `201 Created` - Resource was successfully created
- `400 Bad Request` - The request was malformed
- `401 Unauthorized` - Authentication failed or token is missing
- `403 Forbidden` - User does not have permission to access the resource
- `404 Not Found` - Resource not found
- `422 Unprocessable Entity` - Validation error
- `500 Internal Server Error` - Server error