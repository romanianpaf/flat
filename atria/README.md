# F1 Atria - Sistem de Administrare

## Descriere

F1 Atria este un sistem complet de administrare cu interfață modernă și responsive, construit cu React/TypeScript pentru frontend și Laravel pentru backend.

## Funcționalități Implementate

### 🎨 Interfață Responsive
- **Navigare adaptivă**: Bara de navigare se adaptează automat la dimensiunea ecranului
- **Design mobil-first**: Interfață optimizată pentru dispozitive mobile
- **Drawer pentru mobil**: Meniu lateral pentru ecrane mici
- **Componente responsive**: Toate componentele se adaptează la diferite rezoluții

### 📊 Sistem de Logging Complet
- **Logging automat**: Toate cererile HTTP sunt înregistrate automat
- **Logging manual**: Funcții pentru înregistrarea acțiunilor specifice
- **Nivele de log**: info, warning, error, success
- **Metadata completă**: IP, User Agent, date de cerere, etc.
- **Securitate**: Datele sensibile (parole, token-uri) sunt mascate

### 🔐 Autentificare și Autorizare
- **JWT Authentication**: Sistem de autentificare securizat
- **Role-based access**: Controlul accesului bazat pe roluri
- **Middleware de securitate**: Protecție pentru rutele API
- **Logging de securitate**: Toate încercările de autentificare sunt logate

### 👥 Gestionare Utilizatori
- **CRUD complet**: Creare, citire, actualizare, ștergere utilizatori
- **Atribuire roluri**: Asocierea utilizatorilor cu roluri
- **Validare date**: Validare completă a datelor de intrare
- **Logging acțiuni**: Toate acțiunile cu utilizatori sunt logate

### 🛡️ Gestionare Roluri
- **CRUD roluri**: Gestionarea completă a rolurilor
- **Validare integritate**: Verificări înainte de ștergere
- **Logging modificări**: Toate modificările de roluri sunt logate

### 📈 Dashboard și Statistici
- **Dashboard principal**: Vizualizare generală a sistemului
- **Statistici în timp real**: Numărul de utilizatori, loguri, etc.
- **Acțiuni rapide**: Acces rapid la funcționalități importante
- **Informații sistem**: Status, versiune, ultima actualizare

## Structura Proiectului

```
atria/
├── frontend/                 # Aplicația React/TypeScript
│   ├── src/
│   │   ├── components/       # Componente reutilizabile
│   │   │   ├── Navigation.tsx    # Bara de navigare responsive
│   │   │   └── AdminLayout.tsx   # Layout pentru admin
│   │   ├── pages/           # Pagini ale aplicației
│   │   │   ├── Home.tsx         # Pagina principală
│   │   │   ├── LogsPage.tsx     # Pagina de loguri
│   │   │   ├── AdminDashboard.tsx
│   │   │   ├── UsersManagement.tsx
│   │   │   └── RolesManagement.tsx
│   │   └── lib/
│   │       └── axios.ts     # Configurare API client
│   └── build/               # Build de producție
└── backend/                 # Aplicația Laravel
    ├── app/
    │   ├── Models/
    │   │   ├── User.php
    │   │   ├── Role.php
    │   │   └── Log.php      # Model pentru loguri
    │   ├── Http/
    │   │   ├── Controllers/
    │   │   │   ├── API/     # Controllere API
    │   │   │   └── Admin/   # Controllere Admin
    │   │   └── Middleware/
    │   │       └── LogRequests.php  # Middleware de logging
    │   └── database/
    │       ├── migrations/  # Migrații baza de date
    │       └── seeders/     # Seederi pentru date de test
    └── routes/
        └── api.php          # Rutele API
```

## Tehnologii Utilizate

### Frontend
- **React 18** cu TypeScript
- **Chakra UI** pentru component UI
- **React Router** pentru navigare
- **Axios** pentru cereri HTTP
- **React Icons** pentru iconuri

### Backend
- **Laravel 11** cu PHP 8.2+
- **Laravel Passport** pentru OAuth2
- **MySQL** pentru baza de date
- **Eloquent ORM** pentru modele
- **Middleware** pentru logging automat

## Configurare și Instalare

### Cerințe
- PHP 8.2+
- Node.js 18+
- MySQL 8.0+
- Composer
- npm

### Backend
```bash
cd atria/backend
composer install
cp .env.example .env
# Configurare baza de date în .env
php artisan migrate
php artisan passport:install
php artisan db:seed --class=LogSeeder
```

### Frontend
```bash
cd atria/frontend
npm install
npm run build
```

### Configurare Domenii
- **Frontend**: `f1.atria.live` → `/public_html/f1.atria.live/atria/frontend/build`
- **Backend**: `backend.atria.live` → `/public_html/f1.atria.live/atria/backend/public`

## API Endpoints

### Autentificare
- `POST /api/login` - Autentificare utilizator
- `POST /api/logout` - Deconectare
- `GET /api/user` - Informații utilizator curent

### Admin
- `GET /api/admin/info` - Informații admin
- `GET /api/admin/logs` - Lista loguri (cu filtrare)
- `GET /api/admin/logs/export` - Export CSV loguri
- `GET /api/admin/logs/stats` - Statistici loguri

### Utilizatori
- `GET /api/users` - Lista utilizatori
- `POST /api/users` - Creare utilizator
- `GET /api/users/{id}` - Detalii utilizator
- `PUT /api/users/{id}` - Actualizare utilizator
- `DELETE /api/users/{id}` - Ștergere utilizator

### Roluri
- `GET /api/roles` - Lista roluri
- `POST /api/roles` - Creare rol
- `GET /api/roles/{id}` - Detalii rol
- `PUT /api/roles/{id}` - Actualizare rol
- `DELETE /api/roles/{id}` - Ștergere rol

## Sistem de Logging

### Logging Automat
Middleware-ul `LogRequests` înregistrează automat:
- Toate cererile HTTP
- IP-ul clientului
- User Agent
- Metoda HTTP
- Status code
- Datele de cerere (mascate pentru securitate)

### Logging Manual
```php
// Logging cu niveluri diferite
Log::logInfo('action_name', 'Descriere acțiune');
Log::logWarning('action_name', 'Descriere warning');
Log::logError('action_name', 'Descriere eroare');
Log::logSuccess('action_name', 'Descriere succes');

// Logging cu metadata
Log::log('action_name', 'Descriere', 'info', [
    'user_id' => 1,
    'additional_data' => 'value'
]);
```

### Nivele de Log
- **info**: Informații generale
- **warning**: Avertismente
- **error**: Erori
- **success**: Acțiuni reușite

## Securitate

### Măsuri Implementate
- **JWT Authentication** cu Laravel Passport
- **CORS** configurat pentru domeniile specifice
- **Validare date** strictă pe toate endpoint-urile
- **Sanitizare date** în loguri (parole mascate)
- **Middleware de securitate** pentru rutele protejate
- **Logging de securitate** pentru toate acțiunile

### Best Practices
- Toate parolele sunt hash-uite cu bcrypt
- Token-urile sunt revocate la logout
- Validare strictă a input-urilor
- Logging complet pentru audit trail
- Rate limiting (poate fi adăugat)

## Responsive Design

### Breakpoints
- **Mobile**: < 768px (drawer navigation)
- **Tablet**: 768px - 1024px (sidebar navigation)
- **Desktop**: > 1024px (sidebar navigation)

### Componente Responsive
- **Navigation**: Drawer pe mobil, sidebar pe desktop
- **Tables**: Scroll orizontal pe ecrane mici
- **Forms**: Layout adaptiv pentru diferite dimensiuni
- **Cards**: Grid responsive pentru statistici

## Monitorizare și Mentenanță

### Logging
- Toate acțiunile sunt logate automat
- Logurile conțin metadata completă
- Export CSV disponibil
- Filtrare și căutare în loguri

### Statistici
- Numărul total de loguri
- Loguri pe zi
- Erori în sistem
- Utilizatori activi

### Backup și Curățenie
- Funcție pentru ștergerea logurilor vechi
- Export automat pentru backup
- Indexare pentru performanță optimă

## Dezvoltare Viitoare

### Funcționalități Planificate
- [ ] Dashboard cu grafice interactive
- [ ] Notificări în timp real
- [ ] Export în mai multe formate (PDF, Excel)
- [ ] Backup automat al logurilor
- [ ] Alert-uri pentru erori critice
- [ ] API rate limiting
- [ ] Audit trail pentru modificări

### Îmbunătățiri Tehnice
- [ ] Cache pentru performanță
- [ ] Queue pentru procesarea logurilor
- [ ] WebSocket pentru actualizări live
- [ ] PWA pentru acces mobil
- [ ] Teste automate

## Suport și Contact

Pentru suport tehnic sau întrebări despre implementare, contactează echipa de dezvoltare.

---

**Versiune**: 1.0.0  
**Ultima actualizare**: 6 August 2025  
**Status**: Producție 