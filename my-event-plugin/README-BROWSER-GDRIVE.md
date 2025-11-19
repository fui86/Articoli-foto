# 🚀 Browser Google Drive Integrato - Guida Completa

**Data:** 17 Novembre 2025  
**Versione:** 1.2.0

## 🎯 Cos'è?

Un **browser Google Drive navigabile** integrato direttamente nel plugin WordPress, che permette di:
- ✅ Navigare nelle cartelle del tuo Google Drive senza uscire da WordPress
- ✅ Vedere tutte le cartelle in tempo reale
- ✅ Selezionare una cartella e caricare automaticamente le foto
- ✅ **Zero problemi di cache** (usa Google Drive API diretta)
- ✅ **Zero lightbox** (selezione foto diretta)

---

## 🎨 Come Funziona

### 1. **Apri la pagina "Crea Nuovo Evento"**
```
WordPress Admin → Gestione Eventi → Crea Nuovo Evento
```

### 2. **Vedi il browser Google Drive**
Nella sezione "Passo 1" troverai un browser simile a questo:

```
┌─────────────────────────────────────────┐
│ 🏠 My Drive                             │
├─────────────────────────────────────────┤
│                                          │
│  📁 Eventi 2025    📁 Feste            │
│  📁 Matrimoni      📁 Concerti         │
│                                          │
├─────────────────────────────────────────┤
│ [✓ Seleziona Questa Cartella]          │
└─────────────────────────────────────────┘
```

### 3. **Naviga nelle cartelle**
- **Clicca su una cartella** → Si apre e mostra le sottocartelle
- **Clicca su "My Drive"** nel breadcrumb → Torna alla radice
- **Clicca "Seleziona Questa Cartella"** → Carica le foto dalla cartella corrente

### 4. **Seleziona le foto**
Dopo aver selezionato una cartella, vedrai una griglia con tutte le foto:
- Clicca su 4 foto per selezionarle
- Scegli quale usare come foto di copertina
- Clicca "Crea Evento"

---

## 🔧 Architettura Tecnica

### Backend (PHP)

#### 1. **Classe `MEP_Google_Drive_API`** (includes/class-google-drive-api.php)

**Metodo chiave:** `list_folders_and_files()`

```php
MEP_Google_Drive_API::list_folders_and_files($folder_id, $include_files, $mime_type_filter)
```

**Cosa fa:**
- Ottiene token OAuth da Use-your-Drive
- Fa 2 chiamate all'API Google Drive:
  - Una per le **cartelle** (`mimeType='application/vnd.google-apps.folder'`)
  - Una per i **file** (filtro `image/`)
- Ritorna:
  ```php
  [
      'folders' => [...],  // Array di cartelle
      'files' => [...],    // Array di file immagine
      'folder_id' => '...' // ID cartella corrente
  ]
  ```

**Altri metodi:**
- `get_folder_info($folder_id)` → Info cartella per breadcrumb (nome, parent, ecc.)
- `verify_folder_access($folder_id)` → Verifica permessi

#### 2. **AJAX Handler** (my-event-plugin.php)

```php
add_action('wp_ajax_mep_browse_gdrive_folder', [$this, 'handle_browse_gdrive_folder']);
```

**Flusso:**
1. Riceve `folder_id` da JavaScript
2. Chiama `MEP_Google_Drive_API::list_folders_and_files()`
3. Trasforma i file in formato UI-friendly con thumbnail
4. Ritorna JSON:
   ```json
   {
       "success": true,
       "data": {
           "folders": [...],
           "photos": [...],
           "folder_id": "...",
           "folder_info": {...}
       }
   }
   ```

---

### Frontend (JavaScript)

#### 1. **Oggetto `GDriveBrowser`** (assets/js/admin-script.js)

**Proprietà:**
- `currentFolderId` → ID cartella corrente (default: 'root')
- `folderHistory` → Storico navigazione (per back button futuro)

**Metodi:**

```javascript
GDriveBrowser.init()
```
- Carica la cartella root all'avvio
- Bind degli eventi

```javascript
GDriveBrowser.loadFolder(folderId)
```
- Mostra spinner
- Chiama AJAX `mep_browse_gdrive_folder`
- Renderizza cartelle
- Aggiorna breadcrumb
- Mostra pulsante "Seleziona"

```javascript
GDriveBrowser.renderFolders(data)
```
- Crea griglia di cartelle con icone
- Ogni cartella è cliccabile
- Hover effect (scale + shadow)

```javascript
GDriveBrowser.updateBreadcrumb(data)
```
- Mostra percorso corrente: `🏠 My Drive › Cartella Corrente`
- Breadcrumb cliccabile per tornare indietro

```javascript
GDriveBrowser.selectCurrentFolder()
```
- Popola campo hidden `#event_folder_id`
- Chiama `loadFolderPhotos()` per caricare le foto

---

### UI (HTML + CSS)

#### Struttura HTML (templates/admin-page.php)

```html
<div id="mep-gdrive-browser">
    <!-- Breadcrumb -->
    <div id="mep-gdrive-breadcrumb">
        🏠 My Drive
    </div>
    
    <!-- Lista Cartelle -->
    <div id="mep-gdrive-folders-list">
        <!-- Reso da JavaScript -->
    </div>
    
    <!-- Pulsante Selezione -->
    <div id="mep-current-folder-actions">
        <button id="mep-select-current-folder">
            ✓ Seleziona Questa Cartella
        </button>
    </div>
</div>
```

#### Stile Cartelle

Ogni cartella è un `div.mep-gdrive-folder-item` con:
- Gradiente viola (`#667eea` → `#764ba2`)
- Icona dashicon `category`
- Hover effect (translateY + scale)
- Grid layout (auto-fill, minmax 200px)

---

## 📊 Flusso Completo

```
┌─────────────────┐
│   User apre     │
│  pagina admin   │
└────────┬────────┘
         │
         v
┌─────────────────────────────┐
│ JavaScript: GDriveBrowser.  │
│ init() → loadFolder('root') │
└────────┬────────────────────┘
         │
         v
┌──────────────────────────────┐
│  AJAX: mep_browse_gdrive_    │
│  folder (folder_id='root')   │
└────────┬─────────────────────┘
         │
         v
┌──────────────────────────────────┐
│ PHP: MEP_Google_Drive_API::     │
│ list_folders_and_files('root')  │
└────────┬─────────────────────────┘
         │
         v
┌───────────────────────────────┐
│ Google Drive API v3:          │
│ - Lista cartelle in 'root'    │
│ - Lista file immagine         │
└────────┬──────────────────────┘
         │
         v
┌────────────────────────────────┐
│  JSON Response ritorna a JS    │
│  {folders: [...], photos:[...]}│
└────────┬───────────────────────┘
         │
         v
┌────────────────────────────────┐
│ JavaScript: renderFolders()    │
│ → Mostra griglia cartelle      │
│ → Aggiorna breadcrumb          │
└────────┬───────────────────────┘
         │
         v
┌────────────────────────────────┐
│   User clicca su cartella      │
│   "Eventi 2025"                │
└────────┬───────────────────────┘
         │
         v
┌────────────────────────────────┐
│ loadFolder('1a2b3c4d...')      │
│ → Ripete il ciclo              │
└────────┬───────────────────────┘
         │
         v
┌────────────────────────────────┐
│ User clicca "Seleziona"        │
│ selectCurrentFolder()          │
└────────┬───────────────────────┘
         │
         v
┌────────────────────────────────┐
│ loadFolderPhotos()             │
│ → Mostra griglia foto          │
│ → User seleziona 4 foto        │
│ → Crea evento                  │
└────────────────────────────────┘
```

---

## 🎉 Vantaggi rispetto a Use-your-Drive

| Caratteristica | Use-your-Drive | Browser Integrato |
|----------------|----------------|-------------------|
| **Cache** | ❌ Problemi frequenti | ✅ Nessun problema |
| **Errori `get_id()`** | ❌ Frequenti | ✅ Mai |
| **Lightbox** | ❌ Si apriva sempre | ✅ Mai |
| **Navigazione** | ⚠️ Confusa | ✅ Intuitiva |
| **Permessi** | ❌ Errori criptici | ✅ Messaggi chiari |
| **Velocità** | ⚠️ Lenta (cache) | ✅ Veloce (API diretta) |
| **Personalizzazione** | ❌ Limitata | ✅ Totale |

---

## 🚀 Prossime Migliorie (Opzionali)

### 1. **Back Button**
Aggiungere un pulsante "← Indietro" nel breadcrumb:
```javascript
folderHistory: [],  // Stack di folder_id visitati
back: function() {
    if (this.folderHistory.length > 0) {
        const previousFolder = this.folderHistory.pop();
        this.loadFolder(previousFolder);
    }
}
```

### 2. **Search Bar**
Cercare cartelle per nome:
```javascript
searchFolders: function(query) {
    const results = this.allFolders.filter(f => 
        f.name.toLowerCase().includes(query.toLowerCase())
    );
    this.renderFolders({folders: results});
}
```

### 3. **Thumbnail Cartelle**
Mostrare miniatura prima foto della cartella:
```php
MEP_Google_Drive_API::get_folder_thumbnail($folder_id)
```

### 4. **Drag & Drop**
Trascinare cartelle per riordinarle (solo UI, non modifica Google Drive).

### 5. **Favorites**
Salvare cartelle preferite per accesso rapido:
```php
update_user_meta(get_current_user_id(), 'mep_favorite_folders', $folder_ids);
```

---

## 🐛 Troubleshooting

### ❌ "Nessuna cartella trovata"
**Causa:** Account Use-your-Drive non ha accesso a quella cartella.  
**Soluzione:**
1. Vai su Google Drive
2. Condividi la cartella con l'email dell'account Use-your-Drive
3. Ricarica la pagina

### ❌ "Errore di connessione"
**Causa:** Token OAuth scaduto.  
**Soluzione:**
1. Vai in Use-your-Drive → Settings → Accounts
2. Riautorizza l'account
3. Ricarica la pagina

### ❌ Cartelle non si aprono
**Causa:** JavaScript non caricato.  
**Soluzione:**
1. Apri Console (F12)
2. Cerca errori JavaScript
3. Ricarica la pagina con Ctrl+Shift+R

---

## 📝 Note per lo sviluppatore

### Dipendenze
- jQuery (incluso in WordPress)
- Dashicons (icone WordPress)
- Use-your-Drive (solo per token OAuth)

### File modificati
1. `includes/class-google-drive-api.php` → Nuovo metodo `list_folders_and_files()`
2. `my-event-plugin.php` → Nuovo AJAX handler `handle_browse_gdrive_folder()`
3. `assets/js/admin-script.js` → Nuovo oggetto `GDriveBrowser`
4. `templates/admin-page.php` → Nuovo componente UI `#mep-gdrive-browser`

### Performance
- **1 richiesta AJAX** per cartella navigata
- **2 chiamate Google Drive API** (cartelle + file) per richiesta
- **Cache lato client:** Nessuna (ogni navigazione è fresca)
- **Timeout:** 30s per API call

---

**Autore:** AI Assistant (Claude Sonnet 4.5)  
**Versione:** 1.0  
**Licenza:** GPL v2+
