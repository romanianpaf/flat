# Specificații - Platformă Asociație Proprietari cu Automatizări

## 1. Obiectiv General
Crearea unei platforme complete pentru gestionarea unei asociații de proprietari, cu funcționalități de automatizare, control acces, notificări și raportare.

## 2. Arhitectura Sistemului

### 2.1 Backend (Laravel 11)
**Tehnologii:**
- Laravel 11 cu PHP 8.2+
- Spatie Laravel Permission pentru autorizare
- Laravel Breeze + Inertia pentru autentificare
- MQTT pentru automatizări IoT
- Telegram Bot API pentru notificări
- MySQL/PostgreSQL pentru baza de date

**Responsabilități:**
- API REST pentru frontend și aplicații mobile
- Gestionarea automatizărilor (MQTT, Telegram)
- Control acces (limitare orară, blocare acces)
- Generare rapoarte granularizate
- Logging și audit trail

### 2.2 Frontend (React + Inertia)
**Tehnologii:**
- React 18+ cu Inertia.js
- Tailwind CSS pentru styling
- React Query pentru state management
- Real-time updates prin WebSockets

**Responsabilități:**
- Interfață administrativă pentru admin
- Dashboard-uri pentru diferite roluri
- Formulare pentru gestionarea datelor
- Vizualizări pentru rapoarte și statistici

### 2.3 Aplicații Mobile (React Native)
**Tehnologii:**
- React Native cu Expo
- Aceleași API-uri ca frontend-ul
- Push notifications

**Responsabilități:**
- Funcționalități identice cu frontend-ul
- Notificări push pentru evenimente importante
- Acces rapid la informații critice

## 3. Structura Bazei de Date

### 3.1 Tabele de Autentificare și Autorizare
```sql
-- Utilizatori și autentificare
users (id, name, email, password, email_verified_at, created_at, updated_at)

-- Sistem de roluri și permisiuni (Spatie)
roles (id, name, guard_name, created_at, updated_at)
permissions (id, name, guard_name, created_at, updated_at)
model_has_roles (role_id, model_type, model_id)
model_has_permissions (permission_id, model_type, model_id)
role_has_permissions (permission_id, role_id)
```

### 3.2 Tabele pentru Asociația de Proprietari
```sql
-- Proprietăți și unități
properties (id, name, address, total_units, created_at, updated_at)
units (id, property_id, number, floor, area, owner_id, tenant_id, created_at, updated_at)

-- Utilizatori extinși
user_profiles (id, user_id, phone, address, emergency_contact, created_at, updated_at)

-- Facturi și plăți
invoices (id, unit_id, type, amount, due_date, status, created_at, updated_at)
payments (id, invoice_id, amount, payment_date, method, created_at, updated_at)

-- Întreținere și reparații
maintenance_requests (id, unit_id, user_id, title, description, priority, status, created_at, updated_at)
maintenance_logs (id, request_id, technician_id, action, notes, created_at, updated_at)
```

### 3.3 Tabele pentru Automatizări
```sql
-- Dispozitive IoT
devices (id, name, type, location, mqtt_topic, status, created_at, updated_at)
device_logs (id, device_id, value, timestamp, created_at)

-- Control acces
access_points (id, name, location, device_id, created_at, updated_at)
access_logs (id, access_point_id, user_id, action, timestamp, created_at)
access_schedules (id, user_id, access_point_id, day_of_week, start_time, end_time, active)

-- Notificări
notifications (id, user_id, type, title, message, read_at, created_at)
notification_templates (id, type, title_template, message_template, created_at)
```

## 4. Sistemul de Roluri și Permisiuni

### 4.1 Roluri Definitive
1. **Super Admin** - Acces complet la toate funcționalitățile
2. **Admin** - Administrare utilizatori, permisiuni, rapoarte
3. **Manager** - Gestionare proprietăți, întreținere, facturi
4. **Owner** - Proprietar de unitate
5. **Tenant** - Chiriaș
6. **Technician** - Tehnician pentru întreținere

### 4.2 Permisiuni Granulare
```php
// Dashboard
'dashboard.view'

// Utilizatori
'users.view', 'users.create', 'users.edit', 'users.delete'
'users.roles.assign', 'users.permissions.assign'

// Proprietăți
'properties.view', 'properties.create', 'properties.edit', 'properties.delete'
'units.view', 'units.create', 'units.edit', 'units.delete'

// Financiar
'invoices.view', 'invoices.create', 'invoices.edit', 'invoices.delete'
'payments.view', 'payments.create', 'payments.edit'

// Întreținere
'maintenance.view', 'maintenance.create', 'maintenance.edit', 'maintenance.delete'
'maintenance.assign', 'maintenance.complete'

// Automatizări
'automation.view', 'automation.control', 'automation.configure'
'access.view', 'access.control', 'access.schedule'

// Rapoarte
'reports.view', 'reports.generate', 'reports.export'
```

## 5. Funcționalități Principale

### 5.1 Dashboard-uri Specializate

#### Dashboard Super Admin
- Statistici generale platformă
- Monitorizare automatizări
- Log-uri de securitate
- Rapoarte de performanță

#### Dashboard Admin
- Gestionare utilizatori și roluri
- Configurare permisiuni
- Monitorizare activitate
- Backup și mentenanță

#### Dashboard Manager
- Gestionare proprietăți și unități
- Facturi și plăți
- Cereri întreținere
- Rapoarte financiare

#### Dashboard Owner/Tenant
- Informații unitate
- Facturi și plăți
- Cereri întreținere
- Notificări personale

### 5.2 Gestionare Utilizatori
- CRUD complet utilizatori
- Atribuire roluri și permisiuni
- Profiluri extinse (telefon, adresă, contact de urgență)
- Istoric activitate

### 5.3 Gestionare Proprietăți
- CRUD proprietăți și unități
- Asociere proprietari și chiriași
- Calcul automat facturi
- Istoric proprietate

### 5.4 Sistem Financiar
- Generare automată facturi
- Tracking plăți
- Rapoarte financiare
- Export date pentru contabilitate

### 5.5 Întreținere și Reparații
- Creare cereri întreținere
- Atribuire tehnicieni
- Tracking progres
- Istoric întreținere

### 5.6 Automatizări IoT
- Integrare dispozitive MQTT
- Control acces automat
- Monitorizare senzori
- Alert-uri în timp real

### 5.7 Notificări
- Integrare Telegram Bot
- Email notifications
- Push notifications mobile
- Template-uri personalizabile

### 5.8 Control Acces
- Limitare orară acces
- Blocare acces temporar/permanent
- Log-uri acces
- Programe de acces

## 6. API Endpoints

### 6.1 Autentificare
```php
POST /api/auth/login
POST /api/auth/logout
POST /api/auth/refresh
GET  /api/auth/user
```

### 6.2 Utilizatori
```php
GET    /api/users
POST   /api/users
GET    /api/users/{id}
PUT    /api/users/{id}
DELETE /api/users/{id}
POST   /api/users/{id}/roles
POST   /api/users/{id}/permissions
```

### 6.3 Proprietăți
```php
GET    /api/properties
POST   /api/properties
GET    /api/properties/{id}
PUT    /api/properties/{id}
DELETE /api/properties/{id}
GET    /api/properties/{id}/units
```

### 6.4 Automatizări
```php
GET    /api/devices
POST   /api/devices/{id}/control
GET    /api/access-logs
POST   /api/access-control
GET    /api/automation/status
```

## 7. Securitate

### 7.1 Autentificare
- JWT tokens pentru API
- Session-based pentru web
- 2FA pentru admin
- Rate limiting

### 7.2 Autorizare
- Middleware pentru fiecare ruta
- Verificare permisiuni granulare
- Audit trail pentru acțiuni critice

### 7.3 Protecție Date
- Encryptare date sensibile
- Backup automat
- GDPR compliance
- Log-uri de securitate

## 8. Performanță

### 8.1 Optimizări
- Caching Redis pentru date frecvente
- Database indexing
- Lazy loading pentru componente mari
- API pagination

### 8.2 Monitorizare
- Log-uri de performanță
- Alert-uri pentru probleme
- Metrics pentru utilizare
- Health checks

## 9. Deployment

### 9.1 Cerințe Server
- PHP 8.2+
- MySQL 8.0+ sau PostgreSQL 13+
- Redis pentru caching
- Node.js pentru build assets

### 9.2 Pași Deployment
1. Setup server și dependințe
2. Configurare .env
3. Rulare migrații
4. Rulare seeder-e
5. Build assets production
6. Configurare cron jobs
7. Setup monitoring

## 10. Testare

### 10.1 Teste Unitare
- Teste pentru modele
- Teste pentru controllere
- Teste pentru middleware

### 10.2 Teste de Integrare
- Teste API endpoints
- Teste automatizări
- Teste notificări

### 10.3 Teste de Securitate
- Teste autorizare
- Teste validare input
- Teste CSRF protection

## 11. Documentație

### 11.1 API Documentation
- Swagger/OpenAPI specs
- Exemple de utilizare
- Coduri de eroare

### 11.2 User Manuals
- Ghid admin
- Ghid utilizatori
- Ghid tehnicieni

### 11.3 Developer Docs
- Setup development
- Contributing guidelines
- Architecture decisions

## 12. Roadmap

### 12.1 Faza 1 (MVP)
- Autentificare și autorizare
- CRUD utilizatori și roluri
- Dashboard-uri de bază
- API endpoints fundamentale

### 12.2 Faza 2
- Gestionare proprietăți
- Sistem financiar
- Notificări email

### 12.3 Faza 3
- Automatizări IoT
- Control acces
- Aplicații mobile

### 12.4 Faza 4
- Rapoarte avansate
- Integrări externe
- Optimizări performanță

