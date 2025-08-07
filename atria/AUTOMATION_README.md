# Sistem de Automatizări F1 Atria

## Descriere

Sistemul de automatizări F1 Atria permite controlul și monitorizarea sistemelor automate, inclusiv controlul accesului la piscină prin zăvor electromagnetic și configurarea unui proxy local.

## Funcționalități Principale

### 🔐 Control Acces Piscină
- **Zăvor Electromagnetic**: Control direct al zăvorului de la piscină
- **MQTT Integration**: Comunicare cu plăcuța ESP32 prin protocol MQTT
- **Logging Complet**: Înregistrarea tuturor acțiunilor de acces
- **Control Manual**: Butoane pentru blocare/dezblocare directă

### 🌐 Proxy Local
- **Configurare MiniPC**: Setări pentru proxy local
- **Cache Management**: Gestionarea cache-ului local
- **Connection Pooling**: Optimizarea conexiunilor
- **Status Monitoring**: Monitorizarea statusului proxy-ului

### 📊 Monitorizare și Statistici
- **Dashboard Real-time**: Statistici în timp real
- **Access Logs**: Istoric complet al accesurilor
- **MQTT Status**: Statusul conexiunilor MQTT
- **Performance Metrics**: Metrici de performanță

## Configurare Sistem

### Accesuri și Roluri
Sistemul este accesibil pentru:
- **Sysadmin**: Acces complet la toate funcționalitățile
- **CEX**: Acces la controlul accesului și monitorizare
- **Tehnic**: Acces la configurare și mentenanță

### Configurare MQTT pentru Piscină

#### Parametri ESP32
```json
{
  "device_id": "ESP32_POOL_001",
  "lock_relay_pin": 26,
  "mqtt_broker": "192.168.1.100",
  "mqtt_port": 1883,
  "mqtt_username": "pool_user",
  "mqtt_password": "pool_password_123",
  "mqtt_topic_control": "pool/lock/control",
  "mqtt_topic_status": "pool/lock/status"
}
```

#### Configurare Zăvor
```json
{
  "unlock_duration": 5000,
  "auto_lock_delay": 30000,
  "max_unlock_attempts": 3,
  "lock_type": "electromagnetic",
  "voltage": 12,
  "current": 0.5
}
```

### Configurare Proxy Local

#### Parametri MiniPC
```json
{
  "proxy_port": 8080,
  "proxy_type": "http",
  "max_connections": 100,
  "cache_enabled": true,
  "cache_size": "1GB"
}
```

## API Endpoints

### Automatizări
- `GET /api/automations` - Lista automatizărilor
- `POST /api/automations` - Creare automatizare
- `GET /api/automations/{id}` - Detalii automatizare
- `PUT /api/automations/{id}` - Actualizare automatizare
- `DELETE /api/automations/{id}` - Ștergere automatizare

### Control Piscină
- `POST /api/automations/pool-access/control` - Control zăvor
- `GET /api/automations/pool-access/logs` - Loguri acces
- `POST /api/automations/mqtt/test` - Test conexiune MQTT
- `GET /api/automations/statistics` - Statistici

## Mesaje MQTT

### Control Zăvor
```json
{
  "action": "unlock|lock",
  "device_id": "ESP32_POOL_001",
  "pin": 26,
  "timestamp": "2025-08-06T11:42:53.300416Z"
}
```

### Răspuns ESP32
```json
{
  "status": "success|failed",
  "message": "Zăvorul a fost deblocat",
  "timestamp": "2025-08-06T11:42:53.301019Z"
}
```

## Interfață Web

### Pagina Principală
- **Dashboard**: Statistici generale
- **Control Rapid**: Butoane pentru blocare/dezblocare
- **Lista Automatizări**: Toate automatizările configurate
- **Status Real-time**: Statusul fiecărei automatizări

### Funcționalități
- **Control Direct**: Butoane pentru controlul zăvorului
- **Test MQTT**: Testarea conexiunilor MQTT
- **Configurare**: Editarea parametrilor automatizărilor
- **Monitorizare**: Vizualizarea logurilor și statisticilor

## Securitate

### Autentificare
- **JWT Tokens**: Autentificare securizată
- **Role-based Access**: Controlul accesului bazat pe roluri
- **Session Management**: Gestionarea sesiunilor

### Logging
- **Access Logs**: Toate acțiunile sunt logate
- **MQTT Logs**: Comunicarea MQTT este înregistrată
- **Error Logs**: Erorile sunt logate cu detalii
- **Audit Trail**: Istoric complet pentru audit

### Validare
- **Input Validation**: Validarea strictă a datelor
- **MQTT Security**: Credențiale MQTT securizate
- **Rate Limiting**: Protecție împotriva atacurilor

## Implementare ESP32

### Cod Exemplu
```cpp
#include <WiFi.h>
#include <PubSubClient.h>

// Configurare MQTT
const char* mqtt_server = "192.168.1.100";
const int mqtt_port = 1883;
const char* mqtt_username = "pool_user";
const char* mqtt_password = "pool_password_123";

// Topics
const char* topic_control = "pool/lock/control";
const char* topic_status = "pool/lock/status";

// Pin zăvor
const int lock_pin = 26;

WiFiClient espClient;
PubSubClient client(espClient);

void setup() {
  pinMode(lock_pin, OUTPUT);
  digitalWrite(lock_pin, HIGH); // Zăvor blocat
  
  // Conectare WiFi și MQTT
  setupWiFi();
  client.setServer(mqtt_server, mqtt_port);
  client.setCallback(callback);
}

void callback(char* topic, byte* payload, unsigned int length) {
  if (strcmp(topic, topic_control) == 0) {
    String message = "";
    for (int i = 0; i < length; i++) {
      message += (char)payload[i];
    }
    
    // Procesare comanda
    if (message.indexOf("\"action\":\"unlock\"") != -1) {
      unlockDoor();
    } else if (message.indexOf("\"action\":\"lock\"") != -1) {
      lockDoor();
    }
  }
}

void unlockDoor() {
  digitalWrite(lock_pin, LOW);
  delay(5000); // 5 secunde
  digitalWrite(lock_pin, HIGH);
  
  // Trimite status
  String status = "{\"status\":\"success\",\"message\":\"Zăvorul a fost deblocat\"}";
  client.publish(topic_status, status.c_str());
}

void lockDoor() {
  digitalWrite(lock_pin, HIGH);
  
  // Trimite status
  String status = "{\"status\":\"success\",\"message\":\"Zăvorul a fost blocat\"}";
  client.publish(topic_status, status.c_str());
}
```

## Monitorizare și Mentenanță

### Loguri Importante
- **Access Logs**: `pool_access_logs` tabel
- **System Logs**: `logs` tabel cu relații polimorfe
- **MQTT Logs**: Mesaje și răspunsuri MQTT

### Metrici de Performanță
- **Response Time**: Timpul de răspuns al zăvorului
- **Success Rate**: Rata de succes a operațiunilor
- **MQTT Connectivity**: Statusul conexiunilor MQTT
- **Error Rate**: Rata de erori

### Backup și Recuperare
- **Database Backup**: Backup automat al bazei de date
- **Configuration Backup**: Backup al configurațiilor
- **Log Retention**: Păstrarea logurilor pentru audit

## Dezvoltare Viitoare

### Funcționalități Planificate
- [ ] **Mobile App**: Aplicație mobilă pentru control
- [ ] **WebSocket**: Actualizări în timp real
- [ ] **Scheduling**: Programare automată a accesului
- [ ] **Notifications**: Notificări pentru evenimente
- [ ] **Analytics**: Analiză avansată a datelor

### Îmbunătățiri Tehnice
- [ ] **MQTT TLS**: Securitate MQTT îmbunătățită
- [ ] **Load Balancing**: Distribuirea sarcinii
- [ ] **Caching**: Cache pentru performanță
- [ ] **API Versioning**: Versiuni API
- [ ] **Rate Limiting**: Protecție îmbunătățită

## Suport și Contact

Pentru suport tehnic sau întrebări despre implementare:
- **Email**: support@f1.atria.live
- **Documentație**: https://docs.f1.atria.live
- **GitHub**: https://github.com/f1-atria/automation

---

**Versiune**: 1.0.0  
**Ultima actualizare**: 6 August 2025  
**Status**: Producție 