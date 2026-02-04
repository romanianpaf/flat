# Design Reference - Soft UI Dashboard PRO

Acest document servește ca referință de design pentru proiectul F1 Atria Live, bazat pe template-ul **Soft UI Dashboard PRO Laravel** de la Creative Tim.

---

## Framework Migration Notice

| Aspect | Template Original | Proiect Curent |
|--------|------------------|----------------|
| Vue | 3.2.0 | **3.5.22** |
| Build Tool | Vue CLI 4.5.15 | **Vite 6.3.2** |
| Vue Router | 4.0.14 | 4.0.14 |
| Vuex | 4.0.2 | 4.0.2 |
| Bootstrap | 5.1.3 | 5.1.3 |
| FontAwesome | 6.0.0 | 6.5.0 |

**IMPORTANT**: Proiectul a fost migrat de la Vue CLI la Vite. Toate componentele din template-ul original trebuie verificate pentru compatibilitate înainte de a fi copiate în proiect.

---

## Sursa de Adevăr

Folderul `vue-soft-ui-dashboard-pro-laravel-master/` conține codul original cumpărat.

**REGULI:**
1. Acest folder **NU SE MODIFICĂ NICIODATĂ**
2. Componentele, iconițele și plugin-urile se **COPIAZĂ** în proiect, nu se referă direct
3. Înainte de copiere, se verifică compatibilitatea cu Vue 3.5+ și Vite

---

## Iconițe

### 1. Componente SVG pentru Sidenav

Componentele SVG din `components/Icon/` se folosesc pentru iconițele din meniul lateral (sidenav).

**Componente disponibile:**
| Componentă | Utilizare |
|------------|-----------|
| `Shop.vue` | Dashboards |
| `Settings.vue` | Setări, Configurări |
| `CustomerSupport.vue` | Support, User Voice |
| `Office.vue` | Office, Birouri |
| `Document.vue` | Documente |
| `Spaceship.vue` | Launch, Start |
| `CreditCard.vue` | Plăți, Facturi |
| `Basket.vue` | Coș, Comenzi |
| `Box3d.vue` | Produse, 3D |
| `Vue.vue` | Vue/API Examples |
| `Air.vue` | Aer, Ventilație |
| `Humidity.vue` | Umiditate |
| `Temperature.vue` | Temperatură |
| `Lights.vue` | Lumini |
| `Switches.vue` | Comutatoare |
| `Wifi.vue` | Conectivitate |
| `GettingStarted.vue` | Getting Started |

**Locație în template**: `vue-soft-ui-dashboard-pro-laravel-master/vue-soft-ui-dashboard-laravel/src/components/Icon/`

**Locație în proiect**: `frontend/src/components/Icon/`

**Utilizare în SidenavList.vue:**
```vue
<template #icon>
  <Shop />
</template>
```

### 2. Mini-Icons în Submeniuri

Pentru elementele de submeniu se folosesc **litere simple**, NU emoji-uri.

**Corect:**
```vue
<sidenav-item :to="{ name: 'Default' }" mini-icon="D" text="Default" />
<sidenav-item :to="{ name: 'Automotive' }" mini-icon="A" text="Automotive" />
```

**GREȘIT:**
```vue
<sidenav-item :to="{ name: 'Acasă' }" mini-icon="🏠" text="Acasă" />
```

### 3. Nucleo Icons

Nucleo Icons (prefix `ni ni-`) se folosesc în interiorul aplicației pentru iconițe în carduri, butoane, statistici etc.

**Importate în main.js:**
```javascript
import "./assets/css/nucleo-icons.css";
import "./assets/css/nucleo-svg.css";
```

**Utilizare:**
```html
<i class="ni ni-bell-55"></i>
<i class="ni ni-cart text-secondary"></i>
<i class="ni ni-check-bold text-success"></i>
```

**Lista completă Nucleo Icons:**

| Clasă | Utilizare |
|-------|-----------|
| `ni-active-40` | Activ |
| `ni-air-baloon` | Balon |
| `ni-album-2` | Album |
| `ni-align-center` | Aliniere centru |
| `ni-align-left-2` | Aliniere stânga |
| `ni-ambulance` | Ambulanță |
| `ni-app` | Aplicație |
| `ni-archive-2` | Arhivă |
| `ni-atom` | Atom |
| `ni-badge` | Insignă |
| `ni-bag-17` | Geantă |
| `ni-basket` | Coș |
| `ni-bell-55` | Clopoțel/Notificare |
| `ni-bold-down` | Săgeată jos bold |
| `ni-bold-left` | Săgeată stânga bold |
| `ni-bold-right` | Săgeată dreapta bold |
| `ni-bold-up` | Săgeată sus bold |
| `ni-bold` | Bold |
| `ni-book-bookmark` | Carte bookmark |
| `ni-books` | Cărți |
| `ni-box-2` | Cutie |
| `ni-briefcase-24` | Servietă |
| `ni-building` | Clădire |
| `ni-bulb-61` | Bec |
| `ni-bullet-list-67` | Listă |
| `ni-bus-front-12` | Autobuz |
| `ni-button-pause` | Pauză |
| `ni-button-play` | Play |
| `ni-button-power` | Power |
| `ni-calendar-grid-58` | Calendar |
| `ni-camera-compact` | Cameră |
| `ni-caps-small` | Caps |
| `ni-cart` | Coș cumpărături |
| `ni-chart-bar-32` | Grafic bare |
| `ni-chart-pie-35` | Grafic pie |
| `ni-chat-round` | Chat |
| `ni-check-bold` | Bifă |
| `ni-circle-08` | Cerc/Avatar |
| `ni-cloud-download-95` | Download cloud |
| `ni-cloud-upload-96` | Upload cloud |
| `ni-compass-04` | Busolă |
| `ni-controller` | Controller |
| `ni-credit-card` | Card credit |
| `ni-curved-next` | Următorul |
| `ni-delivery-fast` | Livrare rapidă |
| `ni-diamond` | Diamant |
| `ni-email-83` | Email |
| `ni-fat-add` | Adăugare |
| `ni-fat-delete` | Ștergere |
| `ni-fat-remove` | Eliminare |
| `ni-favourite-28` | Favorit |
| `ni-folder-17` | Folder |
| `ni-glasses-2` | Ochelari |
| `ni-hat-3` | Pălărie |
| `ni-headphones` | Căști |
| `ni-html5` | HTML5 |
| `ni-istanbul` | Istanbul |
| `ni-key-25` | Cheie |
| `ni-laptop` | Laptop |
| `ni-like-2` | Like |
| `ni-lock-circle-open` | Deblocat |
| `ni-map-big` | Hartă |
| `ni-mobile-button` | Mobil |
| `ni-money-coins` | Bani/Monede |
| `ni-note-03` | Notă |
| `ni-notification-70` | Notificare |
| `ni-palette` | Paletă |
| `ni-paper-diploma` | Diplomă |
| `ni-pin-3` | Pin |
| `ni-planet` | Planetă |
| `ni-ruler-pencil` | Riglă+Creion |
| `ni-satisfied` | Satisfăcut |
| `ni-scissors` | Foarfece |
| `ni-send` | Trimite |
| `ni-settings-gear-65` | Setări gear |
| `ni-settings` | Setări |
| `ni-single-02` | Persoană |
| `ni-single-copy-04` | Copie |
| `ni-sound-wave` | Sunet |
| `ni-spaceship` | Navă spațială |
| `ni-square-pin` | Pin pătrat |
| `ni-support-16` | Support |
| `ni-tablet-button` | Tabletă |
| `ni-tag` | Tag |
| `ni-tie-bow` | Papion |
| `ni-time-alarm` | Alarmă |
| `ni-trophy` | Trofeu |
| `ni-tv-2` | TV |
| `ni-umbrella-13` | Umbrelă |
| `ni-user-run` | User alergând |
| `ni-vector` | Vector |
| `ni-watch-time` | Ceas |
| `ni-world` | Lume |
| `ni-zoom-split-in` | Zoom in |
| `ni-collection` | Colecție |
| `ni-image` | Imagine |
| `ni-shop` | Magazin |
| `ni-ungroup` | Degrupare |
| `ni-world-2` | Lume 2 |
| `ni-ui-04` | UI |

**Mărimi disponibile:**
- `ni-lg` - 1.33x
- `ni-2x` - 2x
- `ni-3x` - 3x
- `ni-4x` - 4x
- `ni-5x` - 5x

**Modificatori:**
- `.spin` - Rotație continuă
- `.rotate-90`, `.rotate-180`, `.rotate-270` - Rotație fixă
- `.flip-x`, `.flip-y` - Oglindire
- `.square`, `.circle` - Background

### 4. NU FOLOSIM

- **Emoji-uri** pentru iconițe în navigare sau UI
- **Font Awesome** pentru iconițele din sidenav (folosim SVG components)
- Font Awesome se poate folosi doar unde Nucleo Icons nu oferă o alternativă

---

## Carduri

Cardurile în Soft UI Dashboard PRO **NU au chenar (border)**, doar umbră (box-shadow).

### Stiluri SCSS

**Variabile (din `_cards.scss`):**
```scss
$card-box-shadow: 0 20px 27px 0 rgba(0,0,0,0.05) !default;
$card-background-blur: rgba(255, 255, 255, 0.8) !default;
$card-header-padding: 1.5rem !default;
$card-body-padding: 1.5rem !default;
$card-footer-padding: 1.5rem !default;
```

**Card plain (fără shadow):**
```scss
$card-plain-bg-color: transparent !default;
$card-plain-box-shadow: none !default;
```

### Clase CSS pentru Carduri

```html
<!-- Card standard -->
<div class="card">
  <div class="card-header">Header</div>
  <div class="card-body">Content</div>
  <div class="card-footer">Footer</div>
</div>

<!-- Card plain (transparent, fără shadow) -->
<div class="card card-plain">
  ...
</div>
```

### Componente Card Disponibile

**Locație în template**: `vue-soft-ui-dashboard-pro-laravel-master/vue-soft-ui-dashboard-laravel/src/examples/Cards/`

| Componentă | Utilizare |
|------------|-----------|
| `MiniStatisticsCard.vue` | Statistici mici cu icon |
| `ComplexStatisticsCard.vue` | Statistici complexe |
| `DefaultInfoCard.vue` | Card informații |
| `DefaultCounterCard.vue` | Card contor |
| `MasterCard.vue` | Card tip card bancar |
| `OrdersListCard.vue` | Listă comenzi |
| `RankingListCard.vue` | Clasament |
| `SwitchCard.vue` | Card cu switch |
| `PlaceHolderCard.vue` | Placeholder |
| `MiniPlayerCard.vue` | Mini player |

---

## Tab-uri (Nav Pills)

### Structura HTML

```html
<div class="nav-wrapper position-relative end-0">
  <ul class="nav nav-pills nav-fill p-1" role="tablist">
    <li class="nav-item">
      <a class="nav-link mb-0 px-0 py-1 active"
         data-bs-toggle="tab"
         href="#tab1"
         role="tab"
         aria-selected="true">Tab 1</a>
    </li>
    <li class="nav-item">
      <a class="nav-link mb-0 px-0 py-1"
         data-bs-toggle="tab"
         href="#tab2"
         role="tab"
         aria-selected="false">Tab 2</a>
    </li>
  </ul>
</div>
```

### Moving Tab Animation

Pentru animația "moving tab" (indicator care se mișcă smooth între tab-uri), se folosește scriptul `nav-pills.js`.

**Locație în template**: `vue-soft-ui-dashboard-pro-laravel-master/vue-soft-ui-dashboard-laravel/src/assets/js/nav-pills.js`

**Utilizare în componentă Vue:**
```vue
<script>
import setNavPills from "@/assets/js/nav-pills.js";

export default {
  name: "MyComponent",
  mounted() {
    setNavPills();
  },
};
</script>
```

### Variabile SCSS pentru Nav Pills

```scss
$nav-pills-link-border-radius: 0.5rem !default;
$nav-pills-link-box-shadow: 0px 1px 5px 1px #ddd !default;
$nav-pills-link-active-padding: 7px 15px !default;
$nav-pills-link-active-margin: 1px !default;
$nav-pills-link-active-animation: .2s ease !default;
```

### Clase disponibile

- `nav-pills` - Container principal
- `nav-fill` - Tab-uri de lățime egală
- `nav-link` - Link individual
- `nav-link.active` - Tab activ
- `moving-tab` - Indicator animat (generat de JS)

---

## Fișiere de Referință

### Template Original (NU SE MODIFICĂ)

```
vue-soft-ui-dashboard-pro-laravel-master/
└── vue-soft-ui-dashboard-laravel/
    └── src/
        ├── components/
        │   └── Icon/           # Componente SVG pentru sidenav
        ├── examples/
        │   └── Cards/          # Componente Card
        ├── assets/
        │   ├── css/
        │   │   ├── nucleo-icons.css
        │   │   └── nucleo-svg.css
        │   ├── js/
        │   │   └── nav-pills.js
        │   └── scss/
        │       └── soft-ui-dashboard/
        │           └── variables/
        │               └── _cards.scss
        └── views/              # Exemple de utilizare
```

### Proiect Curent

```
frontend/
└── src/
    ├── components/
    │   └── Icon/               # Copiat din template
    ├── examples/
    │   └── Cards/              # Copiat din template
    ├── assets/
    │   ├── css/
    │   │   ├── nucleo-icons.css
    │   │   └── nucleo-svg.css
    │   ├── js/
    │   │   └── nav-pills.js
    │   └── scss/
    │       └── soft-ui-dashboard/
    └── views/
```

---

## Checklist Migrare Componentă

Înainte de a copia o componentă din template:

- [ ] Verifică sintaxa Vue (Options API vs Composition API)
- [ ] Verifică importurile (require vs import)
- [ ] Verifică dacă folosește `this.$store` sau Composition API
- [ ] Verifică dacă funcționează cu Vite (fără require())
- [ ] Testează în browser după copiere
- [ ] Actualizează acest document dacă găsești incompatibilități

---

## Exemple de Utilizare Corectă

### Sidenav cu Icon SVG

```vue
<template>
  <sidenav-collapse collapse-ref="dashboards" nav-text="Panouri">
    <template #icon>
      <Shop />
    </template>
    <template #list>
      <ul class="nav ms-4 ps-3">
        <sidenav-item :to="{ name: 'Acasă' }" mini-icon="A" text="Acasă" />
        <sidenav-item :to="{ name: 'Rapoarte' }" mini-icon="R" text="Rapoarte" />
      </ul>
    </template>
  </sidenav-collapse>
</template>

<script>
import Shop from "@/components/Icon/Shop.vue";
// ...
</script>
```

### Card cu Nucleo Icon

```vue
<template>
  <mini-statistics-card
    title="Utilizatori"
    value="2,300"
    :icon="{
      component: 'ni ni-circle-08',
      background: 'bg-gradient-primary'
    }"
  />
</template>
```

### Tab-uri cu Moving Tab

```vue
<template>
  <div class="nav-wrapper position-relative">
    <ul class="nav nav-pills nav-fill p-1" role="tablist">
      <li class="nav-item">
        <a class="nav-link mb-0 px-0 py-1 active" data-bs-toggle="tab" href="#general">
          General
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link mb-0 px-0 py-1" data-bs-toggle="tab" href="#setari">
          Setări
        </a>
      </li>
    </ul>
  </div>
</template>

<script>
import setNavPills from "@/assets/js/nav-pills.js";

export default {
  mounted() {
    setNavPills();
  }
};
</script>
```

---

*Ultima actualizare: Februarie 2026*
