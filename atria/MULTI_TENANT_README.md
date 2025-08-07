# Sistem Multi-Tenant F1 Atria

## Descriere

Sistemul multi-tenant F1 Atria permite rularea mai multor asociații de proprietari pe aceeași aplicație, cu izolare completă a datelor și control centralizat prin sysadmin.

## Arhitectura Multi-Tenant

### 🔐 **Model de Acces**
- **Sysadmin**: Acces complet la toate tenant-urile și funcționalitățile
- **Admin per Tenant**: Acces la tenant-ul propriu
- **CEX/Tehnic/User**: Acces limitat în funcție de rol

### 🏢 **Structura Tenant**
Fiecare tenant reprezintă o asociație de proprietari cu:
- Date complet izolate
- Configurații personalizate
- Limite de utilizatori și automatizări
- Planuri de abonament diferite

## Funcționalități Principale

### 👑 **Gestionare Sysadmin**
- **Creare Tenant-uri**: Definirea noilor asociații
- **Atribuire Utilizatori**: Asocierea utilizatorilor la tenant-uri
- **Monitorizare**: Statistici pentru toate tenant-urile
- **Configurare**: Setări globale și per tenant

### 🏠 **Izolare Date**
- **Utilizatori**: Fiecare tenant are proprii utilizatori
- **Automatizări**: Configurări separate per tenant
- **Loguri**: Istoric izolat per tenant
- **Roluri**: Permisiuni specifice per tenant

### 📊 **Monitorizare și Statistici**
- **Utilizatori activi** per tenant
- **Automatizări configurate** per tenant
- **Loguri de activitate** per tenant
- **Status abonament** per tenant

## Configurare Tenant

### 📋 **Parametri de Bază**
```json
{
  "name": "F1 Atria - Asociația Principală",
  "slug": "f1-atria-main",
  "domain": "f1.atria.live",
  "description": "Asociația principală de proprietari F1 Atria",
  "contact_email": "admin@f1.atria.live",
  "contact_phone": "+40 123 456 789",
  "address": "Strada Exemplu, Nr. 123, București"
}
```

### 🎯 **Planuri de Abonament**
- **Basic**: 10 utilizatori, 5 automatizări
- **Premium**: 50 utilizatori, 10 automatizări
- **Enterprise**: 200 utilizatori, 30 automatizări

### ⚙️ **Configurații**
```json
{
  "timezone": "Europe/Bucharest",
  "language": "ro",
  "date_format": "d.m.Y",
  "time_format": "H:i"
}
```

### 🚀 **Funcționalități**
```json
{
  "features": [
    "pool_access",
    "proxy_config", 
    "advanced_logging",
    "user_management",
    "role_management",
    "automation_management",
    "advanced_analytics",
    "mobile_app"
  ]
}
```

## API Endpoints

### 🏢 **Gestionare Tenant-uri (Sysadmin)**
- `GET /api/tenants` - Lista toate tenant-urile
- `POST /api/tenants` - Creare tenant nou
- `GET /api/tenants/{id}` - Detalii tenant
- `PUT /api/tenants/{id}` - Actualizare tenant
- `DELETE /api/tenants/{id}` - Ștergere tenant
- `GET /api/tenants/{id}/statistics` - Statistici tenant

### 🔄 **Context și Schimbare**
- `GET /api/tenants/current` - Informații tenant curent
- `POST /api/tenants/switch` - Schimbare context (sysadmin)
- `GET /api/tenants/all` - Toate tenant-urile (sysadmin)

## Utilizatori și Roluri

### 👤 **Tipuri de Utilizatori**

#### **Sysadmin**
- Acces complet la toate tenant-urile
- Poate crea, edita, șterge tenant-uri
- Poate atribui utilizatori la tenant-uri
- Poate schimba contextul între tenant-uri

#### **Admin per Tenant**
- Acces complet la tenant-ul propriu
- Poate gestiona utilizatori și roluri
- Poate configura automatizări
- Poate vizualiza loguri

#### **CEX (Manager)**
- Acces la control și monitorizare
- Poate controla automatizări
- Poate vizualiza rapoarte

#### **Tehnic**
- Acces la configurare și mentenanță
- Poate configura automatizări
- Poate accesa loguri de mentenanță

#### **User**
- Acces limitat la dashboard
- Poate vizualiza informații de bază

### 🔐 **Sistem de Permisiuni**
```json
{
  "admin": [
    "users.manage",
    "roles.manage", 
    "automations.manage",
    "logs.view",
    "settings.manage"
  ],
  "cex": [
    "automations.control",
    "logs.view",
    "reports.view"
  ],
  "tehnic": [
    "automations.configure",
    "logs.view",
    "maintenance.access"
  ],
  "user": [
    "dashboard.view"
  ]
}
```

## Implementare Tehnică

### 🗄️ **Structura Bazei de Date**

#### **Tabelul Tenants**
```sql
CREATE TABLE tenants (
    id BIGINT PRIMARY KEY,
    name VARCHAR(255),
    slug VARCHAR(255) UNIQUE,
    domain VARCHAR(255) UNIQUE,
    description TEXT,
    contact_email VARCHAR(255),
    contact_phone VARCHAR(20),
    address TEXT,
    logo_url VARCHAR(255),
    settings JSON,
    status ENUM('active', 'inactive', 'suspended'),
    subscription_plan VARCHAR(50),
    subscription_expires_at TIMESTAMP,
    max_users INT,
    max_automations INT,
    features JSON,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

#### **Relații Multi-Tenant**
- `users.tenant_id` → `tenants.id`
- `roles.tenant_id` → `tenants.id`
- `automations.tenant_id` → `tenants.id`
- `logs.tenant_id` → `tenants.id`
- `pool_access_logs.tenant_id` → `tenants.id`

### 🔒 **Securitate și Izolare**

#### **Middleware de Acces**
```php
// Verificare acces tenant
public function canAccessTenant($tenantId)
{
    if ($this->isSysAdmin()) {
        return true;
    }
    return $this->tenant_id === $tenantId;
}
```

#### **Scoping Automat**
```php
// Toate query-urile sunt automat filtrate pe tenant
public function scopeForTenant($query, $tenantId)
{
    return $query->where('tenant_id', $tenantId);
}
```

## Exemple de Utilizare

### 🏢 **Creare Tenant Nou**
```bash
curl -X POST https://backend.atria.live/api/tenants \
  -H "Authorization: Bearer SYSDMIN_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Asociația Nouă",
    "slug": "asociatia-noua",
    "domain": "noua.atria.live",
    "subscription_plan": "premium",
    "max_users": 50,
    "max_automations": 10
  }'
```

### 👤 **Atribuire Utilizator la Tenant**
```bash
curl -X POST https://backend.atria.live/api/users \
  -H "Authorization: Bearer SYSDMIN_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Ion Popescu",
    "email": "ion.popescu@noua.atria.live",
    "password": "password123",
    "tenant_id": 4,
    "role": "admin"
  }'
```

### 📊 **Statistici Tenant**
```bash
curl -X GET https://backend.atria.live/api/tenants/4/statistics \
  -H "Authorization: Bearer ADMIN_TOKEN"
```

## Monitorizare și Mentenanță

### 📈 **Metrici Importante**
- **Utilizatori activi** per tenant
- **Automatizări configurate** per tenant
- **Loguri generate** per tenant
- **Status abonament** per tenant
- **Performanță** per tenant

### 🔧 **Mentenanță**
- **Backup** per tenant
- **Migrări** per tenant
- **Actualizări** per tenant
- **Monitorizare** per tenant

### 🚨 **Alerting**
- **Abonament expirat**
- **Limită utilizatori atinsă**
- **Limită automatizări atinsă**
- **Erori critice** per tenant

## Dezvoltare Viitoare

### 🚀 **Funcționalități Planificate**
- [ ] **Subdomain routing** automat
- [ ] **Custom branding** per tenant
- [ ] **API rate limiting** per tenant
- [ ] **Advanced analytics** per tenant
- [ ] **Multi-language support** per tenant
- [ ] **Custom workflows** per tenant

### 🔧 **Îmbunătățiri Tehnice**
- [ ] **Database sharding** pentru performanță
- [ ] **Caching** per tenant
- [ ] **Queue management** per tenant
- [ ] **File storage** izolat per tenant
- [ ] **Backup automation** per tenant

## Suport și Contact

Pentru suport tehnic sau întrebări despre implementare:
- **Email**: support@f1.atria.live
- **Documentație**: https://docs.f1.atria.live
- **GitHub**: https://github.com/f1-atria/multi-tenant

---

**Versiune**: 1.0.0  
**Ultima actualizare**: 6 August 2025  
**Status**: Producție 