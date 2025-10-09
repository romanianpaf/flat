# API Documentation - F1 Atria Live

## Base URL
```
https://f1.atria.live/api/v2
```

## Authentication
- **Type:** OAuth2 (Laravel Passport)
- **Login:** `POST /login`
- **Register:** `POST /register`
- **Logout:** `POST /logout`
- **Refresh Token:** `POST /oauth/token`

### Headers
```
Authorization: Bearer {access_token}
Accept: application/vnd.api+json
Content-Type: application/vnd.api+json
```

## API Endpoints

### 1. Authentication
| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| POST | `/login` | User login | No |
| POST | `/register` | User registration | No |
| POST | `/logout` | User logout | Yes |
| POST | `/password-forgot` | Forgot password | No |
| POST | `/password-reset` | Reset password | No |
| GET | `/me` | Get current user profile | Yes |
| PATCH | `/me` | Update current user profile | Yes |

### 2. Tenants (Beneficiari)
| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/tenants` | List tenants | Yes |
| GET | `/tenants/{id}` | Get tenant details | Yes |
| POST | `/tenants` | Create tenant | Yes |
| PATCH | `/tenants/{id}` | Update tenant | Yes |
| DELETE | `/tenants/{id}` | Delete tenant | Yes |

**Tenant Fields:**
- `name` (string, required)
- `address` (string, optional)
- `fiscal_code` (string, unique, optional)
- `description` (string, optional)
- `contact_data` (json, optional)

### 3. Users (Utilizatori)
| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/users` | List users | Yes |
| GET | `/users/{id}` | Get user details | Yes |
| POST | `/users` | Create user | Yes |
| PATCH | `/users/{id}` | Update user | Yes |
| DELETE | `/users/{id}` | Delete user | Yes |

**User Fields:**
- `name` (string, required)
- `email` (string, required, unique)
- `password` (string, required)
- `tenant_id` (integer, optional)

### 4. Roles (Roluri)
| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/roles` | List roles | Yes |
| GET | `/roles/{id}` | Get role details | Yes |
| POST | `/roles` | Create role | Yes |
| PATCH | `/roles/{id}` | Update role | Yes |
| DELETE | `/roles/{id}` | Delete role | Yes |

**Role Fields:**
- `name` (string, required)
- `tenant_id` (integer, optional)

### 5. Polls (Sondaje)
| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/polls` | List polls | Yes |
| GET | `/polls/{id}` | Get poll details | Yes |
| POST | `/polls` | Create poll | Yes |
| PATCH | `/polls/{id}` | Update poll | Yes |
| DELETE | `/polls/{id}` | Delete poll | Yes |

**Poll Fields:**
- `title` (string, required)
- `description` (string, optional)
- `is_active` (boolean, default: true)
- `allow_multiple_votes` (boolean, default: false)
- `start_date` (datetime, optional)
- `end_date` (datetime, optional)
- `tenant_id` (integer, optional)

### 6. Poll Options (Opțiuni Sondaje)
| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/poll-options` | List poll options | Yes |
| GET | `/poll-options/{id}` | Get poll option details | Yes |
| POST | `/poll-options` | Create poll option | Yes |
| PATCH | `/poll-options/{id}` | Update poll option | Yes |
| DELETE | `/poll-options/{id}` | Delete poll option | Yes |

**Poll Option Fields:**
- `poll_id` (integer, required)
- `option_text` (string, required)
- `votes_count` (integer, default: 0)
- `order` (integer, default: 0)

### 7. User Voices (Sugestii)
| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/user-voices` | List user voices | Yes |
| GET | `/user-voices/{id}` | Get user voice details | Yes |
| POST | `/user-voices` | Create user voice | Yes |
| PATCH | `/user-voices/{id}` | Update user voice | Yes |
| DELETE | `/user-voices/{id}` | Delete user voice | Yes |
| POST | `/user-voices/{id}/vote` | Vote on user voice | Yes |

**User Voice Fields:**
- `suggestion` (string, required, min: 10 chars)
- `user_id` (integer, auto-set)
- `tenant_id` (integer, optional)
- `votes_up` (integer, default: 0)
- `votes_down` (integer, default: 0)
- `is_active` (boolean, default: true)

**Vote Request:**
```json
{
  "type": "up" // or "down"
}
```

### 8. Automations (Automatizări)
| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/automations` | List automations | Yes |
| GET | `/automations/{id}` | Get automation details | Yes |
| POST | `/automations` | Create automation | Yes |
| PATCH | `/automations/{id}` | Update automation | Yes |
| DELETE | `/automations/{id}` | Delete automation | Yes |

**Automation Fields:**
- `name` (string, required)
- `description` (string, optional)
- `type` (enum: switch, sensor, actuator, light, lock)
- `mqtt_broker_host` (string, required)
- `mqtt_broker_port` (integer, default: 1883)
- `mqtt_broker_username` (string, optional)
- `mqtt_broker_password` (string, optional)
- `mqtt_topic` (string, required)
- `mqtt_payload_on` (json, optional)
- `mqtt_payload_off` (json, optional)
- `mqtt_qos` (integer, default: 0)
- `is_active` (boolean, default: true)
- `tenant_id` (integer, required)

### 9. Categories (Categorii)
| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/categories` | List categories | Yes |
| GET | `/categories/{id}` | Get category details | Yes |
| POST | `/categories` | Create category | Yes |
| PATCH | `/categories/{id}` | Update category | Yes |
| DELETE | `/categories/{id}` | Delete category | Yes |

### 10. Items (Articole)
| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/items` | List items | Yes |
| GET | `/items/{id}` | Get item details | Yes |
| POST | `/items` | Create item | Yes |
| PATCH | `/items/{id}` | Update item | Yes |
| DELETE | `/items/{id}` | Delete item | Yes |

### 11. Tags (Etichete)
| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/tags` | List tags | Yes |
| GET | `/tags/{id}` | Get tag details | Yes |
| POST | `/tags` | Create tag | Yes |
| PATCH | `/tags/{id}` | Update tag | Yes |
| DELETE | `/tags/{id}` | Delete tag | Yes |

### 12. Permissions (Permisiuni)
| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/permissions` | List permissions | Yes |

## Query Parameters

### Pagination
```
?page[size]=10&page[number]=1
```

### Sorting
```
?sort=-created_at  // Descending
?sort=name        // Ascending
```

### Filtering
```
?filter[name]=search_term
?filter[is_active]=true
```

### Including Relationships
```
?include=user,tenant,options
```

## Response Format

All responses follow JSON:API specification:

### Success Response
```json
{
  "jsonapi": {
    "version": "2.0"
  },
  "data": {
    "type": "users",
    "id": "1",
    "attributes": {
      "name": "John Doe",
      "email": "john@example.com"
    },
    "relationships": {
      "tenant": {
        "data": {
          "type": "tenants",
          "id": "1"
        }
      }
    }
  },
  "included": [
    {
      "type": "tenants",
      "id": "1",
      "attributes": {
        "name": "Company Name"
      }
    }
  ],
  "meta": {
    "page": {
      "current-page": 1,
      "per-page": 10,
      "total": 100
    }
  }
}
```

### Error Response
```json
{
  "jsonapi": {
    "version": "2.0"
  },
  "errors": [
    {
      "status": "422",
      "title": "Unprocessable Entity",
      "detail": "The given data was invalid.",
      "source": {
        "pointer": "/data/attributes/email"
      }
    }
  ]
}
```

## Frontend Routes

### Public Routes
- `/login` - Login page
- `/register` - Registration page
- `/password-forgot` - Forgot password
- `/password-reset` - Reset password

### Protected Routes (require authentication)
- `/` - Dashboard
- `/examples/polls/list` - Polls list
- `/examples/polls/new` - Create poll
- `/examples/polls/edit/:id` - Edit poll
- `/examples/user-voices/list` - User voices list
- `/examples/user-voices/new` - Create user voice
- `/examples/user-voices/edit/:id` - Edit user voice
- `/examples/tenants/list` - Tenants list
- `/examples/tenants/new` - Create tenant
- `/examples/tenants/edit/:id` - Edit tenant
- `/examples/users/list` - Users list
- `/examples/users/new` - Create user
- `/examples/users/edit/:id` - Edit user
- `/examples/roles/list` - Roles list
- `/examples/roles/new` - Create role
- `/examples/roles/edit/:id` - Edit role
- `/examples/automations/list` - Automations list
- `/examples/automations/new` - Create automation
- `/examples/automations/edit/:id` - Edit automation

## User Roles & Permissions

### Admin (Global)
- Can access all resources
- Can manage all tenants
- Can create/edit/delete everything

### Creator
- Can create and manage content
- Limited to own tenant

### Regular User
- Can view and vote
- Limited to own tenant

## Multi-tenancy

- All resources are scoped by `tenant_id`
- Global admin (no tenant_id) can access everything
- Regular users can only access their tenant's resources

## Environment Variables

### Frontend (.env)
```
VUE_APP_API_BASE_URL=https://f1.atria.live/api/v2
VUE_APP_CLIENT_ID=2
VUE_APP_CLIENT_SECRET=taFFz2C9Gh0ThmUiBtrumpnt4ijITGOcF7YFML5z
```

### Backend (.env)
```
APP_URL=https://f1.atria.live
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=f1_atria_live
DB_USERNAME=root
DB_PASSWORD=

PASSPORT_PERSONAL_ACCESS_CLIENT_ID=1
PASSPORT_PERSONAL_ACCESS_CLIENT_SECRET=your-secret
```

## Mobile App Considerations

### Authentication Flow
1. Login with email/password
2. Store access_token and refresh_token
3. Use access_token for API calls
4. Refresh token when expired (401 response)

### Key Features for Mobile
1. **Polls** - View, create, vote on polls
2. **User Voices** - View, create, vote on suggestions
3. **Profile Management** - View/edit user profile
4. **Multi-tenant Support** - Switch between tenants if admin

### Recommended Mobile Architecture
- **State Management:** Redux/Vuex equivalent
- **HTTP Client:** Axios with interceptors
- **Authentication:** JWT token storage
- **Offline Support:** Cache critical data locally
- **Push Notifications:** For new polls/suggestions

## Security Notes

- All API endpoints require authentication except login/register
- CORS is configured for the frontend domain
- Rate limiting is implemented
- Input validation on all endpoints
- SQL injection protection via Eloquent ORM
- XSS protection via proper escaping
