MQTT + mTLS Architecture — Tenants (Beneficiari)

Scop

Acest document descrie arhitectura finală pentru:
	•	comunicarea VPS (Laravel SaaS) ↔ miniPC on-prem,
	•	onboarding-ul de beneficiari (tenants / asociații),
	•	schema de certificate (mTLS),
	•	convențiile de topicuri MQTT,
	•	modul de definire și execuție a automatizărilor.

Arhitectura este gândită multi-tenant, secure-by-design, enterprise-ready, fără user/parolă MQTT.

⸻

1. Overview arhitectură

Componente
	•	VPS (Cloud)
	•	Laravel SaaS
	•	rulează comenzile (publish MQTT)
	•	NU expune broker MQTT public
	•	miniPC per beneficiar (on-prem)
	•	Ubuntu Server
	•	WireGuard către VPS
	•	Mosquitto MQTT Broker (local)
	•	Dispozitive locale
	•	ESP32, RFID, relee etc.
	•	se conectează doar local (LAN)

Comunicare

Laravel (VPS)
   |
   |  WireGuard (10.10.0.0/24)
   |  + mTLS
   v
Mosquitto (miniPC)
   |
   |  MQTT LAN
   v
ESP / RFID / Locks


⸻

2. Modelul de securitate (mTLS)

Principii
	•	NU se folosesc user/parolă MQTT
	•	Autentificarea se face exclusiv prin certificate
	•	Identitatea clientului = CN din certificat
	•	Fiecare beneficiar are propriul CA

PKI hierarchy

Root CA (global, offline)
└── Tenant CA (per beneficiar)
    ├── mqtt-server.crt   (miniPC)
    ├── mqtt-client-vps.crt (Laravel)
    └── mqtt-client-device.crt (opțional)


⸻

3. Certificate — responsabilități

Root CA
	•	creat o singură dată
	•	păstrat offline
	•	NU ajunge pe servere

Tenant CA
	•	creat per beneficiar
	•	semnat de Root CA
	•	este copiat pe:
	•	miniPC (pentru trust)
	•	VPS (pentru verificare)

Certificat server (miniPC)
	•	SAN: IP WireGuard (ex: 10.10.0.10)
	•	rol: TLS Server Auth

Certificat client (VPS)
	•	CN recomandat: vps-laravel
	•	rol: TLS Client Auth
	•	folosit de Laravel la publish/subscribe

⸻

4. Mosquitto — configurare standard (miniPC)

Listener LAN (dispozitive locale)

listener 1883 192.168.x.x
allow_anonymous false
password_file /etc/mosquitto/passwd

Listener WireGuard + TLS (VPS)

listener 8883 10.10.0.10
protocol mqtt

certfile /etc/mosquitto/certs/mqtt-server.crt
keyfile  /etc/mosquitto/certs/mqtt-server.key
cafile   /etc/mosquitto/certs/tenant-ca-chain.crt

require_certificate true
use_identity_as_username true
allow_anonymous false
tls_version tlsv1.2


⸻

5. Model de date — Beneficiar (Tenant)

Câmpuri necesare

id
name
slug

mqtt_host
mqtt_port

mqtt_ca_path
mqtt_client_cert_path
mqtt_client_key_path

mqtt_topic_prefix
created_at
updated_at

❗ Laravel NU stochează chei private în DB, doar path-uri.

⸻

6. Structura fișierelor pe VPS

/etc/mqtt/
└── tenants/
    └── atria/
        ├── atria-ca-chain.crt
        ├── mqtt-client-vps.crt
        └── mqtt-client-vps.key

Permisiuni:
	•	.key → 600 root:root
	•	.crt → 644 root:root

⸻

7. Automatizări — concept

Automatizarea este doar un publisher MQTT controlat.

Câmpuri minime (DB)

id
tenant_id
name
enabled

trigger_type
action_type

mqtt_topic
mqtt_payload
mqtt_qos
mqtt_retain

cooldown_ms
last_run_at
last_status
last_error


⸻

8. Convenții de topicuri (OBLIGATORIU)

Prefix

{tenant_slug}/

Categorii

Comenzi (NU retained)

{tenant}/cmd/{device}/{action}

Stare (RETAINED)

{tenant}/state/{device}/{property}

Evenimente (NU retained)

{tenant}/evt/{device}/{event}


⸻

9. Flux standard „Open gate”
	1.	Utilizator apasă buton în aplicație
	2.	Laravel publică comanda MQTT
	3.	miniPC / ESP execută
	4.	Dispozitivul publică stare (retained)

⸻

10. Debug & test

VPS

mosquitto_pub -h <ip> -p 8883 --cafile ca.crt --cert client.crt --key client.key -t atria/test -m hello

miniPC

mosquitto_sub -h <ip> -p 8883 --cafile ca.crt --cert client.crt --key client.key -t 'atria/#' -v


⸻

11. Extensibilitate
	•	dispozitive noi → topicuri noi
	•	parcare, lift, interfon → același pattern
	•	schimb miniPC → regenerezi doar cert server

⸻

12. Reguli de aur
	•	❌ NU expune broker MQTT pe internet
	•	❌ NU user/parolă în producție
	•	✅ WireGuard + mTLS
	•	✅ un miniPC per beneficiar
	•	✅ topicuri consistente

Status: arhitectură finală, pregătită pentru implementare în Laravel.