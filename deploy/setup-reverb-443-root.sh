#!/bin/bash
# setup-reverb-443-root.sh — Mută websocket-ul Reverb în spatele portului 443
# prin proxy LiteSpeed (RewriteRule [P]), în loc de portul dedicat 6002.
# SE RULEAZĂ CA ROOT:  bash /home/atria/public_html/f1.atria.live/deploy/setup-reverb-443-root.sh
#
# După: browserul folosește wss://DOMAIN/app/... (443); Reverb ascultă doar pe
# 127.0.0.1:6002 fără TLS; portul 6002 se închide din firewall.
set -e

APP=/home/atria/public_html/f1.atria.live
BACKEND=$APP/backend
FRONTEND=$APP/frontend
DOMAIN=f1.atria.live
CPUSER=atria
PORT=6002
PHP_BIN=/opt/cpanel/ea-php82/root/usr/bin/php

set_env() { # set_env FILE KEY VALUE
  if grep -q "^$2=" "$1"; then sed -i "s|^$2=.*|$2=$3|" "$1"; else echo "$2=$3" >> "$1"; fi
}

echo "== [1/6] Include Apache/LiteSpeed: proxy websocket /app -> 127.0.0.1:$PORT =="
INC_DIR=/etc/apache2/conf.d/userdata/ssl/2_4/$CPUSER/$DOMAIN
mkdir -p "$INC_DIR"
cat > "$INC_DIR/websocket.conf" <<EOF
# Proxy websocket Reverb prin 443 (LiteSpeed suportă [P] către ws://)
RewriteEngine On
RewriteCond %{HTTP:Upgrade} =websocket [NC]
RewriteRule ^/app/(.*)\$ ws://127.0.0.1:$PORT/app/\$1 [P,NE,L]
EOF
/scripts/ensure_vhost_includes --user=$CPUSER 2>&1 | tail -2 || true
/scripts/rebuildhttpdconf >/dev/null
if grep -q "userdata/ssl/2_4/$CPUSER/$DOMAIN" /etc/apache2/conf/httpd.conf; then
  echo "include prezent în httpd.conf ✓"
else
  echo "ATENȚIE: include-ul NU apare în httpd.conf — verifică output-ul ensure_vhost_includes de mai sus"
fi
/scripts/restartsrv_httpd >/dev/null 2>&1 || systemctl restart lsws
echo "webserver restartat"

echo "== [2/6] Reverb: fără TLS, doar pe loopback =="
set_env "$BACKEND/.env" REVERB_SCHEME http
set_env "$BACKEND/.env" REVERB_SERVER_HOST 127.0.0.1
set_env "$BACKEND/.env" REVERB_TLS_CERT ""
sed -i "s|--host=0.0.0.0|--host=127.0.0.1|" /etc/systemd/system/reverb.service
systemctl daemon-reload
sudo -u $CPUSER -H bash -c "cd $BACKEND && $PHP_BIN artisan config:clear" >/dev/null
systemctl restart reverb
sleep 2
systemctl is-active reverb >/dev/null && echo "reverb ACTIV pe 127.0.0.1:$PORT (fără TLS)"

echo "== [3/6] Frontend: portul 443 =="
set_env "$FRONTEND/.env" VITE_REVERB_PORT 443
chown $CPUSER:$CPUSER "$FRONTEND/.env"
sudo -u $CPUSER -H bash -lc "cd $APP && bash rebuild.sh" | tail -2

echo "== [4/6] Închid portul $PORT din firewall (nu mai e nevoie) =="
if [ -f /etc/csf/csf.conf ]; then
  sed -i "s/^TCP_IN = \"$PORT,/TCP_IN = \"/; s/,$PORT,/,/; s/,$PORT\"/\"/" /etc/csf/csf.conf
  csf -r >/dev/null 2>&1 || true
  echo "csf: $PORT scos din TCP_IN"
elif command -v firewall-cmd >/dev/null; then
  firewall-cmd --permanent --remove-port=$PORT/tcp >/dev/null 2>&1 || true
  firewall-cmd --reload >/dev/null 2>&1 || true
  echo "firewalld: $PORT închis"
fi

echo "== [5/6] Curățenie: cert-sync nu mai e necesar =="
rm -f /etc/cron.daily/reverb-cert-sync
echo "cron de sincronizare certificat șters"

echo "== [6/6] Verificare handshake prin 443 =="
KEY=$(grep '^REVERB_APP_KEY=' "$BACKEND/.env" | cut -d= -f2)
RESP=$(curl -sk --max-time 5 -o /dev/null -w "%{http_code}" "https://$DOMAIN/app/$KEY?protocol=7" \
  -H "Connection: Upgrade" -H "Upgrade: websocket" \
  -H "Sec-WebSocket-Version: 13" -H "Sec-WebSocket-Key: dGhlIHNhbXBsZSBub25jZQ==" || true)
echo "handshake wss prin 443: HTTP $RESP (101 = perfect; 000 = conexiunea a rămas deschisă = tot OK)"
echo ""
echo "GATA. Browserele folosesc acum wss://$DOMAIN/app (port 443)."