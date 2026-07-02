#!/bin/bash
# setup-reverb-root.sh — Configurare Laravel Reverb (websockets) pe cPanel.
# SE RULEAZĂ CA ROOT:  bash /home/atria/public_html/f1.atria.live/deploy/setup-reverb-root.sh
#
# Pași: sodium ext -> composer require reverb -> chei în .env -> certificat TLS
#       -> serviciu systemd -> firewall 6001 -> rebuild frontend -> verificare.
set -e

APP=/home/atria/public_html/f1.atria.live
BACKEND=$APP/backend
FRONTEND=$APP/frontend
DOMAIN=f1.atria.live
PORT=6002
PHP_BIN=/usr/local/bin/php

echo "== [1/8] Extensia PHP sodium =="
if ! $PHP_BIN -m | grep -qi sodium; then
  yum install -y ea-php82-php-libsodium 2>/dev/null || yum install -y ea-php82-php-sodium
  $PHP_BIN -m | grep -qi sodium && echo "sodium instalat OK" || { echo "EROARE: sodium tot lipsește"; exit 1; }
else
  echo "sodium deja activ"
fi

echo "== [2/8] laravel/reverb (composer, ca atria) =="
sudo -u atria -H bash -c "cd $BACKEND && composer require laravel/reverb pusher/pusher-php-server --no-interaction" \
  || { echo "EROARE composer"; exit 1; }

echo "== [3/8] Chei Reverb în backend/.env =="
set_env() { # set_env FILE KEY VALUE
  if grep -q "^$2=" "$1"; then sed -i "s|^$2=.*|$2=$3|" "$1"; else echo "$2=$3" >> "$1"; fi
}
if ! grep -q "^REVERB_APP_KEY=" "$BACKEND/.env" || [ -z "$(grep '^REVERB_APP_KEY=' "$BACKEND/.env" | cut -d= -f2)" ]; then
  APP_ID=$(( RANDOM * RANDOM ))
  APP_KEY=$(openssl rand -hex 16)
  APP_SECRET=$(openssl rand -hex 24)
else
  APP_ID=$(grep '^REVERB_APP_ID=' "$BACKEND/.env" | cut -d= -f2)
  APP_KEY=$(grep '^REVERB_APP_KEY=' "$BACKEND/.env" | cut -d= -f2)
  APP_SECRET=$(grep '^REVERB_APP_SECRET=' "$BACKEND/.env" | cut -d= -f2)
fi
set_env "$BACKEND/.env" BROADCAST_DRIVER reverb
set_env "$BACKEND/.env" REVERB_APP_ID "$APP_ID"
set_env "$BACKEND/.env" REVERB_APP_KEY "$APP_KEY"
set_env "$BACKEND/.env" REVERB_APP_SECRET "$APP_SECRET"
set_env "$BACKEND/.env" REVERB_HOST 127.0.0.1        # backend publică local
set_env "$BACKEND/.env" REVERB_PORT $PORT
set_env "$BACKEND/.env" REVERB_SCHEME https
set_env "$BACKEND/.env" REVERB_SERVER_HOST 0.0.0.0
set_env "$BACKEND/.env" REVERB_SERVER_PORT $PORT
set_env "$BACKEND/.env" REVERB_SERVER_HOSTNAME $DOMAIN
set_env "$BACKEND/.env" REVERB_ALLOWED_ORIGIN "https://$DOMAIN"
set_env "$BACKEND/.env" REVERB_TLS_CERT /etc/reverb/$DOMAIN.pem
echo "chei setate (app id: $APP_ID)"

echo "== [4/8] Certificat TLS pentru Reverb =="
mkdir -p /etc/reverb
CERT_SRC=/var/cpanel/ssl/apache_tls/$DOMAIN/combined
[ -f "$CERT_SRC" ] || CERT_SRC=/var/cpanel/ssl/apache_tls/atria.live/combined
[ -f "$CERT_SRC" ] || { echo "EROARE: nu găsesc certificatul în /var/cpanel/ssl/apache_tls/"; ls /var/cpanel/ssl/apache_tls/; exit 1; }
cp -f "$CERT_SRC" /etc/reverb/$DOMAIN.pem
chown root:atria /etc/reverb/$DOMAIN.pem
chmod 640 /etc/reverb/$DOMAIN.pem
echo "certificat copiat din $CERT_SRC"

# Sincronizare zilnică (AutoSSL reînnoiește certificatul) + restart la schimbare
cat > /etc/cron.daily/reverb-cert-sync <<EOF
#!/bin/bash
SRC=$CERT_SRC
DST=/etc/reverb/$DOMAIN.pem
if [ -f "\$SRC" ] && ! cmp -s "\$SRC" "\$DST"; then
  cp -f "\$SRC" "\$DST"; chown root:atria "\$DST"; chmod 640 "\$DST"
  systemctl restart reverb
fi
EOF
chmod +x /etc/cron.daily/reverb-cert-sync

echo "== [5/8] Serviciu systemd =="
cat > /etc/systemd/system/reverb.service <<EOF
[Unit]
Description=Laravel Reverb websocket server (Atria)
After=network.target mysqld.service

[Service]
User=atria
Group=atria
Restart=always
RestartSec=5
WorkingDirectory=$BACKEND
ExecStart=$PHP_BIN artisan reverb:start --host=0.0.0.0 --port=$PORT
StandardOutput=append:/var/log/reverb.log
StandardError=append:/var/log/reverb.log

[Install]
WantedBy=multi-user.target
EOF
touch /var/log/reverb.log && chown atria:atria /var/log/reverb.log
systemctl daemon-reload

echo "== [6/8] Firewall: deschid portul $PORT =="
if [ -f /etc/csf/csf.conf ]; then
  grep -E "^TCP_IN" /etc/csf/csf.conf | grep -q "\b$PORT\b" || sed -i "s/^TCP_IN = \"/TCP_IN = \"$PORT,/" /etc/csf/csf.conf
  csf -r >/dev/null 2>&1 && echo "csf: port $PORT deschis"
elif command -v firewall-cmd >/dev/null; then
  firewall-cmd --permanent --add-port=$PORT/tcp && firewall-cmd --reload && echo "firewalld: port $PORT deschis"
else
  echo "ATENȚIE: nu am recunoscut firewall-ul (nici csf, nici firewalld) — deschide manual portul $PORT/tcp"
fi

echo "== [7/8] Config frontend + rebuild =="
set_env "$FRONTEND/.env" VITE_REVERB_APP_KEY "$APP_KEY"
set_env "$FRONTEND/.env" VITE_REVERB_HOST $DOMAIN
set_env "$FRONTEND/.env" VITE_REVERB_PORT $PORT
set_env "$FRONTEND/.env" VITE_REVERB_SCHEME https
chown atria:atria "$FRONTEND/.env"
sudo -u atria -H bash -c "cd $BACKEND && $PHP_BIN artisan config:clear && $PHP_BIN artisan optimize:clear" >/dev/null
sudo -u atria -H bash -c "cd $APP && bash rebuild.sh" | tail -3

echo "== [8/8] Pornire serviciu + verificare =="
systemctl enable --now reverb
sleep 3
systemctl is-active reverb && echo "serviciu ACTIV"
ss -ltn | grep -q ":$PORT " && echo "ascultă pe portul $PORT"
HTTP_CODE=$(curl -sk -o /dev/null -w "%{http_code}" "https://$DOMAIN:$PORT/app/$APP_KEY" --connect-timeout 5 || true)
echo "test handshake extern: HTTP $HTTP_CODE (426/4xx = OK, serverul răspunde; 000 = firewall încă blochează)"
echo ""
echo "GATA. Log: tail -f /var/log/reverb.log"
