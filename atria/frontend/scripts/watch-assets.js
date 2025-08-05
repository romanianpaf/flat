#!/usr/bin/env node

import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';
import chokidar from 'chokidar';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

// Extensii de fișiere pentru imagini
const imageExtensions = ['.jpg', '.jpeg', '.png', '.gif', '.svg', '.webp', '.ico'];

// Funcție pentru a verifica dacă un fișier este imagine
function isImageFile(filename) {
  const ext = path.extname(filename).toLowerCase();
  return imageExtensions.includes(ext);
}

// Funcție pentru copierea unui fișier
function copyImageFile(srcPath, destPath) {
  try {
    // Creează directorul destinație dacă nu există
    const destDir = path.dirname(destPath);
    if (!fs.existsSync(destDir)) {
      fs.mkdirSync(destDir, { recursive: true });
    }
    
    fs.copyFileSync(srcPath, destPath);
    console.log(`🖼️  Copied: ${path.relative(process.cwd(), srcPath)} -> ${path.relative(process.cwd(), destPath)}`);
  } catch (error) {
    console.error(`❌ Error copying ${srcPath}:`, error.message);
  }
}

// Calea către directoarele sursă și destinație
const srcAssetsDir = path.join(__dirname, '..', 'src', 'assets');
const publicAssetsDir = path.join(__dirname, '..', 'public', 'assets');

console.log('👀 Watching for asset changes...');
console.log(`📁 Source: ${srcAssetsDir}`);
console.log(`📁 Destination: ${publicAssetsDir}`);

// Inițializează watcher-ul
const watcher = chokidar.watch(srcAssetsDir, {
  ignored: /(^|[\/\\])\../, // ignoră fișierele ascunse
  persistent: true
});

// Evenimente pentru fișiere
watcher
  .on('add', (filePath) => {
    if (isImageFile(filePath)) {
      const relativePath = path.relative(srcAssetsDir, filePath);
      const destPath = path.join(publicAssetsDir, relativePath);
      copyImageFile(filePath, destPath);
    }
  })
  .on('change', (filePath) => {
    if (isImageFile(filePath)) {
      const relativePath = path.relative(srcAssetsDir, filePath);
      const destPath = path.join(publicAssetsDir, relativePath);
      copyImageFile(filePath, destPath);
    }
  })
  .on('unlink', (filePath) => {
    if (isImageFile(filePath)) {
      const relativePath = path.relative(srcAssetsDir, filePath);
      const destPath = path.join(publicAssetsDir, relativePath);
      
      try {
        if (fs.existsSync(destPath)) {
          fs.unlinkSync(destPath);
          console.log(`🗑️  Deleted: ${path.relative(process.cwd(), destPath)}`);
        }
      } catch (error) {
        console.error(`❌ Error deleting ${destPath}:`, error.message);
      }
    }
  })
  .on('error', error => {
    console.error('❌ Watcher error:', error);
  });

console.log('✅ Asset watcher started. Press Ctrl+C to stop.');

// Gestionare pentru închiderea curată
process.on('SIGINT', () => {
  console.log('\n🛑 Stopping asset watcher...');
  watcher.close();
  process.exit(0);
}); 