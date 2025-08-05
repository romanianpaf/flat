#!/usr/bin/env node

import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

// Funcție pentru copierea recursivă a directoarelor
function copyDir(src, dest) {
  // Creează directorul destinație dacă nu există
  if (!fs.existsSync(dest)) {
    fs.mkdirSync(dest, { recursive: true });
  }

  // Citește conținutul directorului sursă
  const entries = fs.readdirSync(src, { withFileTypes: true });

  for (const entry of entries) {
    const srcPath = path.join(src, entry.name);
    const destPath = path.join(dest, entry.name);

    if (entry.isDirectory()) {
      // Copiază recursiv directoarele
      copyDir(srcPath, destPath);
    } else {
      // Copiază fișierele
      fs.copyFileSync(srcPath, destPath);
      console.log(`Copied: ${srcPath} -> ${destPath}`);
    }
  }
}

// Extensii de fișiere pentru imagini
const imageExtensions = ['.jpg', '.jpeg', '.png', '.gif', '.svg', '.webp', '.ico'];

// Funcție pentru a verifica dacă un fișier este imagine
function isImageFile(filename) {
  const ext = path.extname(filename).toLowerCase();
  return imageExtensions.includes(ext);
}

// Funcție pentru copierea doar a imaginilor
function copyImages(src, dest) {
  if (!fs.existsSync(src)) {
    console.log(`Source directory doesn't exist: ${src}`);
    return;
  }

  if (!fs.existsSync(dest)) {
    fs.mkdirSync(dest, { recursive: true });
  }

  const entries = fs.readdirSync(src, { withFileTypes: true });

  for (const entry of entries) {
    const srcPath = path.join(src, entry.name);
    const destPath = path.join(dest, entry.name);

    if (entry.isDirectory()) {
      // Pentru directoare, creează subdirectorul și copiază recursiv
      copyImages(srcPath, destPath);
    } else if (isImageFile(entry.name)) {
      // Copiază doar imaginile
      fs.copyFileSync(srcPath, destPath);
      console.log(`Copied image: ${srcPath} -> ${destPath}`);
    }
  }
}

// Calea către directoarele sursă și destinație
const srcAssetsDir = path.join(__dirname, '..', 'src', 'assets');
const publicAssetsDir = path.join(__dirname, '..', 'public', 'assets');

console.log('🖼️  Copying assets from src/assets to public/assets...');

try {
  copyImages(srcAssetsDir, publicAssetsDir);
  console.log('✅ Assets copied successfully!');
} catch (error) {
  console.error('❌ Error copying assets:', error);
  process.exit(1);
} 