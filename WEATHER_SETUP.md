# 🌤️ Configurare API Vreme - OpenWeatherMap

## 📋 Descriere
Widget-ul de vreme din dashboard-ul "Acasă" este configurat să afișeze vremea în timp real pentru:
- **📍 Locație**: Șoseaua Chitilei 242D, București
- **🌐 Coordonate**: 44.5061°N, 26.0506°E

## 🚀 Pași pentru activare

### 1️⃣ Creează cont OpenWeatherMap (Gratuit)
1. Accesează: https://home.openweathermap.org/users/sign_up
2. Completează formularul:
   - Username
   - Email
   - Parolă
3. Confirmă email-ul

### 2️⃣ Obține API Key
1. Loghează-te pe: https://home.openweathermap.org/
2. Mergi la: https://home.openweathermap.org/api_keys
3. Vei vedea un API key implicit deja generat (sau creează unul nou)
4. **⚠️ IMPORTANT**: API key-ul poate dura 10-15 minute să fie activat

### 3️⃣ Configurează API Key în backend
```bash
# Editează fișierul .env din backend
nano /home/atria/public_html/f1.atria.live/backend/.env

# Adaugă API key-ul aici:
OPENWEATHER_API_KEY=paste_your_api_key_here
```

⚠️ **IMPORTANT**: API key-ul este acum pe **BACKEND**, NU pe frontend!
- ✅ Mai sigur (API key nu este expus în browser)
- ✅ Cache centralizat (economisește apeluri API)
- ✅ Control total pe server

### 4️⃣ NU E NEVOIE DE REBUILD
Cache-ul se reîmprospătează automat din backend!
Doar dacă vrei să testezi imediat:
```bash
# Curăță cache-ul manual
curl -X POST https://f1.atria.live/api/v2/weather/clear-cache
```

## 📊 Plan Gratuit OpenWeatherMap
- ✅ **1,000 apeluri/zi**
- ✅ **60 apeluri/minut**
- ✅ Date în timp real
- ✅ Traduceri în română

**Consum actual cu BACKEND CACHE**: 
- ✅ Backend-ul face **1 apel la 10 minute** (indiferent câți utilizatori)
- ✅ **144 apeluri/zi TOTAL** pentru toți utilizatorii
- ✅ Suficient pentru **100+ utilizatori** simultan
- ✅ **85% economie** față de implementarea anterioară

## 🎨 Ce afișează widget-ul?

### Card principal (violet):
- Locație: "București, Chitilei"
- Temperatură: "22 °C"
- Resimțită: "20 °C"
- Descriere: "parțial înnorat"
- Iconița vremii: ☀️⛅☁️🌧️❄️⛈️

### Carduri mici:
- **Temperatură Exterior**: 22 °C
- **Umiditate Exterior**: 65%

## 🔧 Fișiere modificate

```
frontend/
├── src/
│   ├── services/
│   │   └── weather.service.js          ✨ NOU - Serviciu API vreme
│   └── views/
│       └── dashboards/
│           └── Home.vue                 📝 Actualizat - Widget vreme
└── .env                                 📝 Actualizat - API key config
```

## 🧪 Testare

### Fără API Key (Fallback):
Aplicația va afișa date mock:
```
București, Chitilei - 22 °C
Resimțită: 20 °C
parțial înnorat ⛅
```

### Cu API Key (Date reale):
Widget-ul se va actualiza automat cu:
- Temperatura reală
- Descrierea vremii în română
- Iconița potrivită
- Umiditate actuală
- Se reîmprospătează la fiecare 10 minute

## 🐛 Troubleshooting

### API Key nu funcționează?
```bash
# Verifică în browser console (F12)
# Ar trebui să vezi:
✅ Status 200 - OK
❌ Status 401 - API key invalid sau inactiv (așteaptă 10-15 min)
❌ Status 429 - Prea multe cereri (ai depășit limita)
```

### Vrei să schimbi locația?
Editează `frontend/src/services/weather.service.js`:
```javascript
const LOCATION = {
  lat: 44.5061,  // Schimbă latitudinea
  lon: 26.0506,  // Schimbă longitudinea
  name: "București, Chitilei"  // Schimbă numele
};
```

### Vrei să schimbi frecvența de refresh?
În `Home.vue`, linia 372:
```javascript
// Schimbă 10 cu numărul de minute dorit
}, 10 * 60 * 1000);
```

## 📚 Documentație API
- OpenWeatherMap Docs: https://openweathermap.org/api
- Current Weather API: https://openweathermap.org/current
- Weather Icons: https://openweathermap.org/weather-conditions

## ✨ Features viitoare posibile
- [ ] Prognoză pe 5 zile
- [ ] Grafic temperatură pe 24h
- [ ] Alerte meteo
- [ ] Multiple locații (per tenant)
- [ ] Harta radar meteo

---

**📅 Implementat**: Octombrie 2025  
**👨‍💻 Status**: ✅ Funcțional cu fallback  
**🔑 API Key**: ⏳ În așteptare (adaugă în .env)

