# Chat Application Backend

A PHP-based chat application backend built with Slim Framework and SQLite. This application provides a RESTful API for managing chat groups and messages.

## Features

- User Management
  - Create users with secure tokens
  - Token-based authentication

- Group Management
  - Create chat groups
  - Join and leave groups
  - List group members
  - Group membership validation

- Message Management
  - Send messages to groups
  - List messages in a group
  - Message access control (only group members)

## Requirements

- PHP 8.1 or higher
- SQLite3
- Composer

## Installation

1. Clone the repository:
```bash
git clone <repository-url>
cd chat-application
```

2. Install dependencies:
```bash
composer install
```

3. Set up the database:
```bash
touch database/chat.db
```

The schema will be automatically created when the application starts.

## API Endpoints

### Users
- `POST /users` - Create a new user
  - Response: `{ "id": number, "token": string }`

### Groups
- `POST /api/groups` - Create a new group
  - Headers: `X-User-Token`
  - Body: `{ "name": string }`

- `GET /api/groups` - List all groups
  - Headers: `X-User-Token`

- `POST /api/groups/{id}/join` - Join a group
  - Headers: `X-User-Token`

- `POST /api/groups/{id}/leave` - Leave a group
  - Headers: `X-User-Token`

- `GET /api/groups/{id}/members` - List group members
  - Headers: `X-User-Token`

### Messages
- `POST /api/groups/{id}/messages` - Send a message to a group
  - Headers: `X-User-Token`
  - Body: `{ "message": string }`

- `GET /api/groups/{id}/messages` - List messages in a group
  - Headers: `X-User-Token`

## Testing

Run the test suite:
```bash
./vendor/bin/phpunit
```

The test suite includes:
- Integration tests for API endpoints
- Unit tests for Services
- Unit tests for Middleware
- Security tests

## Architecture

The application follows a layered architecture:

1. **Controllers**: Handle HTTP requests and responses
2. **Services**: Implement business logic
3. **Models**: Handle database operations
4. **Middleware**: Handle authentication and request preprocessing

## Security

- Token-based authentication for all protected endpoints
- Group membership validation for message operations
- Input validation and sanitization
- Error handling and appropriate HTTP status codes

## Error Handling

The API uses standard HTTP status codes:
- 200: Success
- 400: Bad Request (invalid input)
- 401: Unauthorized (missing or invalid token)
- 403: Forbidden (e.g., non-member trying to access group messages)
- 404: Not Found (resource doesn't exist)
- 500: Internal Server Error
