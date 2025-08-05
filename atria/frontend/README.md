# Frontend - Atria Admin System

## Gestionarea Assets-urilor (Imagini)

### Structura Directoarelor și Git

```
src/
├── assets/              # ✅ În version control
│   ├── images/          # Imagini sursă
│   └── ...              # Alte tipuri de assets
build/                   # ❌ IGNORAT de git (output de producție)
```

### De ce această structură simplificată?

1. **`src/assets/`** - Imagini sursă, în version control
   - Aici plasezi imaginile originale
   - Sunt sincronizate între dezvoltatori
   - Sunt backup-ate în git
   - Vite le procesează automat în build

2. **`build/`** - Output de producție
   - Generate de Vite la build
   - Nu este în version control
   - Se servește direct pe server

### Cum funcționează Vite cu Assets-urile

Vite procesează automat toate fișierele din `src/` și le include în build:

- **Imagini importate din `src/assets/`** → copiate automat în `build/assets/` cu hash
- **CSS din `src/`** → procesat și optimizat
- **JS/TS din `src/`** → transpilat și bundle-uit

**Notă**: Vite nu poate avea `publicDir` și `outDir` același folder, deci folosim import-uri pentru imagini.

### Comenzi Disponibile

```bash
# Build de producție
npm run build

# Development server
npm run dev
```

### Cum să Adaugi Imagini Noi

1. **Plasează imaginea** în `src/assets/images/` (sau alt subdirector)
2. **Import în componenta React**:
   ```jsx
   import myImage from '../assets/images/nume-imagine.jpg';
   ```
3. **Folosește în cod**:
   ```jsx
   <Box bgImage={`url(${myImage})`} />
   ```
4. **Build**: Rulează `npm run build` - Vite le include automat

### Exemplu de Utilizare

```jsx
// Import imaginea
import atriaImage from '../assets/images/atria-faza1-parc.jpeg';

// În componenta React
<Box 
  bgImage={`url(${atriaImage})`}
  bgSize="cover"
/>
```

### Beneficii

- ✅ **Structură simplificată** - fără foldere redundante
- ✅ **Procesare automată** - Vite se ocupă de tot
- ✅ **Optimizare** - imaginile sunt optimizate automat
- ✅ **Version control curat** - doar fișierele sursă sunt în git
- ✅ **Zero configurație** - nu mai ai nevoie de scripturi custom

### Fișiere Ignorate de Git

Următoarele fișiere/directoare sunt ignorate de git:

- `build/` - Output de producție
- `node_modules/` - Dependințe npm
- `*.log` - Fișiere de log
- `.vscode/`, `.idea/` - Configurări IDE

### Notă Importantă

Toate imaginile trebuie plasate în `src/assets/` pentru a fi procesate automat de Vite. Nu mai ai nevoie de foldere intermediare sau scripturi de copiere.
