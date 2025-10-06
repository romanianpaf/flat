#!/bin/bash
# rebuild.sh - Script pentru recompilare și deploy rapid al aplicației Vue.js
# Utilizare: bash rebuild.sh

echo "🚀 Începe recompilarea aplicației..."

# Navighează în directorul frontend
cd /home/atria/public_html/f1.atria.live/frontend

# Recompilează aplicația Vue.js
echo "📦 Compilare frontend..."
npm run build

if [ $? -ne 0 ]; then
    echo "❌ Eroare la compilare!"
    exit 1
fi

# Navighează în directorul public al backend-ului
cd /home/atria/public_html/f1.atria.live/backend/public

# Șterge fișierele vechi
echo "🧹 Curățare fișiere vechi..."
rm -rf js css img fonts index.html favicon.ico

# Copiază noile fișiere
echo "📂 Copiere fișiere noi..."
cp -r ../../frontend/dist/* .

if [ $? -ne 0 ]; then
    echo "❌ Eroare la copiere!"
    exit 1
fi

echo "✅ Deploy complet cu succes!"
echo "🌐 Vizitează: https://f1.atria.live"

