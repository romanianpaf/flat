# 🔐 Sistem de Permisiuni

## Prezentare Generală

Aplicația folosește un sistem robust de permisiuni bazat pe **spatie/laravel-permission** în backend și helper-e custom în frontend. **Nu mai verificăm roluri hardcodate** - verificăm permisiuni!

## 📋 Permisiuni Disponibile

### Utilizatori
- `view users` - Vezi lista de utilizatori
- `create users` - Creează utilizatori noi
- `edit users` - Editează utilizatori existenți
- `delete users` - Șterge utilizatori

### Roluri
- `view roles` - Vezi lista de roluri
- `create roles` - Creează roluri noi
- `edit roles` - Editează roluri existente
- `delete roles` - Șterge roluri

### Beneficiari (Tenants)
- `view tenants` - Vezi lista de beneficiari
- `create tenants` - Creează beneficiari noi
- `edit tenants` - Editează beneficiari existenți
- `delete tenants` - Șterge beneficiari

### Automatizări
- `view automations` - Vezi automatizări
- `create automations` - Creează automatizări
- `edit automations` - Editează automatizări
- `delete automations` - Șterge automatizări

### Sondaje
- `view polls` - Vezi sondaje
- `create polls` - Creează sondaje
- `edit polls` - Editează sondaje
- `delete polls` - Șterge sondaje

### Servicii
- `view service categories` - Vezi categorii servicii
- `create service categories` - Creează categorii
- `edit service categories` - Editează categorii
- `delete service categories` - Șterge categorii

- `view service subcategories` - Vezi subcategorii
- `create service subcategories` - Creează subcategorii
- `edit service subcategories` - Editează subcategorii
- `delete service subcategories` - Șterge subcategorii

- `view service providers` - Vezi furnizori
- `create service providers` - Creează furnizori
- `edit service providers` - Editează furnizori
- `delete service providers` - Șterge furnizori

- `view service provider ratings` - Vezi rating-uri
- `create service provider ratings` - Creează rating-uri
- `edit service provider ratings` - Editează rating-uri
- `delete service provider ratings` - Șterge rating-uri

## 🎯 Folosire în Frontend

### 1. În Template (Directiva `v-permission`)

```vue
<!-- Ascunde butonul dacă utilizatorul nu are permisiunea -->
<button v-permission="'create users'">
  Add User
</button>

<!-- Verifică orice permisiune din listă (OR logic) -->
<button v-permission:any="['create users', 'edit users']">
  Add/Edit User
</button>

<!-- Verifică toate permisiunile (AND logic) -->
<button v-permission:all="['create users', 'delete users']">
  Full User Access
</button>

<!-- Exemple pentru meniu -->
<li v-permission="'view tenants'">
  <router-link to="/tenants/list">Beneficiari</router-link>
</li>

<li v-permission:any="['view service categories', 'view service providers']">
  <router-link to="/services">Servicii</router-link>
</li>
```

### 2. În JavaScript (Helper Functions)

```javascript
import { hasPermission, hasAnyPermission, hasAllPermissions } from "@/utils/permissions";

export default {
  methods: {
    saveUser() {
      // Verifică o singură permisiune
      if (hasPermission('create users')) {
        // salvează utilizatorul
      }
    },
    
    manageContent() {
      // Verifică dacă are cel puțin una din permisiuni
      if (hasAnyPermission(['create categories', 'create items'])) {
        // administrează conținut
      }
    },
    
    fullAccess() {
      // Verifică dacă are toate permisiunile
      if (hasAllPermissions(['view users', 'edit users', 'delete users'])) {
        // acces complet
      }
    }
  },
  
  computed: {
    canManageUsers() {
      return hasPermission('view users');
    },
    
    canCreateContent() {
      return hasAnyPermission(['create categories', 'create items', 'create tags']);
    }
  }
}
```

### 3. În Routes (Middleware)

```javascript
import permission from "@/middlewares/permission";

{
  path: "/users/list",
  name: "Users",
  component: Users,
  meta: {
    middleware: [auth, permission('view users')]
  },
},

// Sau pentru multiple permisiuni (OR logic)
{
  path: "/content/manage",
  name: "Content Management",
  component: ContentManagement,
  meta: {
    middleware: [auth, permission(['create categories', 'create items'])]
  },
}
```

## 🔧 Backend (Laravel)

### Verificare în Controllers

```php
// Verifică o permisiune
if ($user->can('create users')) {
    // logic
}

// Verifică orice permisiune
if ($user->hasAnyPermission(['create users', 'edit users'])) {
    // logic
}

// Verifică toate permisiunile
if ($user->hasAllPermissions(['view users', 'edit users', 'delete users'])) {
    // logic
}
```

### Policies

```php
public function create(User $user)
{
    return $user->can('create users');
}

public function update(User $user, User $model)
{
    return $user->can('edit users');
}

public function delete(User $user, User $model)
{
    return $user->can('delete users');
}
```

### Assign Permissions to Roles

```php
// În PermissionsSeeder.php
$role = Role::create(['name' => 'editor']);
$role->givePermissionTo([
    'view categories',
    'create categories',
    'edit categories',
]);

// Sau pentru toate permisiunile
$admin = Role::create(['name' => 'admin']);
$admin->givePermissionTo(Permission::all());
```

### Assign Permissions to Users

```php
// Direct unui user
$user->givePermissionTo('create users');

// Prin rol
$user->assignRole('admin');

// Remove permission
$user->revokePermissionTo('delete users');
```

## 📝 Best Practices

### ✅ DO

```javascript
// Folosește permisiuni în loc de roluri
if (hasPermission('view users')) { }

// Grupează permisiuni logice
if (hasAnyPermission(['create categories', 'edit categories'])) { }

// Folosește directiva în template
<button v-permission="'create users'">Add</button>
```

### ❌ DON'T

```javascript
// Nu verifica roluri hardcodate
if (user.roles[0].name === 'admin') { } // BAD!

// Nu duplica logica de verificare
// Folosește helper-ele centralizate în schimb
const user = JSON.parse(localStorage.getItem('user'));
if (user.permissions.includes('...')) { } // BAD! Use hasPermission()
```

## 🔄 Migration de la Roluri la Permisiuni

### Înainte (BAD)
```javascript
if (user.roles[0].name === 'admin' || user.roles[0].name === 'sysadmin') {
  // show admin menu
}
```

### După (GOOD)
```javascript
if (hasAnyPermission(['view users', 'view roles'])) {
  // show admin menu
}
```

## 🆕 Adăugarea de Permisiuni Noi

1. **Adaugă în PermissionsSeeder.php**:
```php
$permissions = [
    // ... existing
    'view new-feature', 
    'create new-feature', 
    'edit new-feature', 
    'delete new-feature',
];
```

2. **Assignează la roluri**:
```php
$admin->givePermissionTo([
    'view new-feature',
    'create new-feature',
    'edit new-feature',
    'delete new-feature',
]);
```

3. **Rulează seeder**:
```bash
php artisan db:seed --class=PermissionsSeeder
```

4. **Folosește în frontend**:
```vue
<button v-permission="'create new-feature'">Add New Feature</button>
```

## 🧪 Testing

```javascript
// În teste, mock-uiește localStorage
localStorage.setItem('user', JSON.stringify({
  id: 1,
  name: 'Test User',
  email: 'test@example.com',
  permissions: ['view users', 'create users'],
  roles: [{ id: 1, name: 'editor' }]
}));

// Apoi testează
expect(hasPermission('view users')).toBe(true);
expect(hasPermission('delete users')).toBe(false);
```

## 📚 Resurse

- [Spatie Laravel Permission](https://spatie.be/docs/laravel-permission/v5/introduction)
- [Laravel Authorization](https://laravel.com/docs/authorization)

