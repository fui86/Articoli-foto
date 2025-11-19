# 🚀 Changelog - Integrazione Google Drive API Diretta

**Data:** 17 Novembre 2025  
**Versione:** 1.1.0

## 🎯 Obiettivo

Sostituire l'utilizzo del plugin Use-your-Drive per la **selezione e import delle foto** con un'integrazione diretta con Google Drive API v3, mantenendo Use-your-Drive solo per la **galleria finale nell'articolo**.

---

## ✨ Cosa è cambiato

### 1. **Nuova Classe: `MEP_Google_Drive_API`** (includes/class-google-drive-api.php)

Wrapper completo per Google Drive API v3 che gestisce:

- ✅ **Autenticazione**: Riutilizza il token OAuth di Use-your-Drive
- ✅ **Lista file**: Ottiene file da una cartella con filtro per immagini
- ✅ **Thumbnails**: Genera URL thumbnail ad alta qualità
- ✅ **Download e Import**: Scarica file e li importa nella Media Library WordPress
- ✅ **Verifica accesso**: Controlla permessi della cartella

**Metodi principali:**
```php
MEP_Google_Drive_API::get_access_token()
MEP_Google_Drive_API::list_files_in_folder($folder_id, $mime_type_filter)
MEP_Google_Drive_API::get_thumbnail_url($file_id, $size)
MEP_Google_Drive_API::download_and_import_file($file_id, $file_name)
MEP_Google_Drive_API::import_files($file_ids, $file_names)
MEP_Google_Drive_API::verify_folder_access($folder_id)
```

---

### 2. **Modificata Classe: `MEP_GDrive_Integration`** (includes/class-gdrive-integration.php)

#### Metodo: `get_photos_list_with_thumbnails()`
**PRIMA:** Usava `TheLion\UseyourDrive\Client::get_folder()` che causava errori di cache e permessi.

**DOPO:** Usa `MEP_Google_Drive_API::list_files_in_folder()` + `MEP_Google_Drive_API::verify_folder_access()`.

**Vantaggi:**
- ✅ Bypassa la cache interna di Use-your-Drive
- ✅ Errori più chiari e gestibili
- ✅ Nessun problema con `get_id() on null`
- ✅ Thumbnail di alta qualità

#### Metodo: `import_specific_photos()`
**PRIMA:** Usava `TheLion\UseyourDrive\API::import()` che poteva fallire silenziosamente.

**DOPO:** Usa `MEP_Google_Drive_API::import_files()` con download diretto.

**Vantaggi:**
- ✅ Import più affidabile
- ✅ Controllo completo sul processo
- ✅ Metadata personalizzati (`_imported_from_gdrive`, `_gdrive_file_id`)

---

### 3. **Aggiornato: `my-event-plugin.php`**

Aggiunto caricamento della nuova classe API:
```php
require_once MEP_PLUGIN_DIR . 'includes/class-google-drive-api.php';
```

---

## 🎭 Cosa rimane invariato

### ✅ Use-your-Drive è ancora usato per:
1. **Shortcode galleria** nell'articolo finale (`create_gallery_shortcode()`)
2. **Browser cartelle** nella UI admin (opzionale, per navigazione visuale)
3. **Token OAuth** (riutilizzato dalla nuova API)

---

## 🐛 Problemi risolti

| Problema | Causa | Soluzione |
|----------|-------|-----------|
| ❌ `Call to a member function get_id() on null` | Cache corrotta di Use-your-Drive | ✅ API diretta bypassa la cache |
| ❌ "Errore di connessione" generico | Errori PHP non catturati | ✅ Gestione errori con `Throwable` |
| ❌ Impossibile vedere file in cartelle | Permessi account Use-your-Drive | ✅ API diretta usa token OAuth diretto |
| ❌ Lightbox si apriva cliccando thumbnail | Use-your-Drive lightbox integrato | ✅ API diretta mostra solo miniature selezionabili |

---

## 📊 Flusso di lavoro aggiornato

### **1. Selezione cartella** (Opzionale: con Use-your-Drive browser)
```
User clicca cartella → AJAX get_folder_photos → MEP_Google_Drive_API::list_files_in_folder()
```

### **2. Caricamento foto**
```
MEP_Google_Drive_API::list_files_in_folder($folder_id)
    ↓
Ritorna array: [
    {id, name, mimeType, size, thumbnailLink}
]
    ↓
Thumbnail mostrate nella griglia UI
```

### **3. Import foto selezionate**
```
User seleziona 4 foto → Clicca "Crea Evento"
    ↓
MEP_GDrive_Integration::import_specific_photos($photo_ids, $photo_names)
    ↓
MEP_Google_Drive_API::import_files()
    ↓
Per ogni foto:
    - Download da Google Drive API
    - Salva in wp-content/uploads/
    - wp_insert_attachment()
    - wp_generate_attachment_metadata()
    ↓
Ritorna array di attachment_ids
```

### **4. Creazione articolo**
```
MEP_Post_Creator::create_event_post()
    ↓
- Clona template post
- Imposta featured image (foto scelta)
- Crea gallery shortcode Use-your-Drive ← ANCORA QUI!
- Inserisce shortcode nell'articolo
- Salva metadati SEO
```

---

## 🔐 Sicurezza e Permessi

### Token OAuth
- ✅ Ottiene token da Use-your-Drive (già autorizzato)
- ✅ Non richiede nuova autorizzazione
- ✅ Token cached per la richiesta corrente

### Validazione
- ✅ Verifica esistenza cartella prima di listare file
- ✅ Controlla permessi di accesso
- ✅ Filtra solo file immagine (MIME type `image/`)
- ✅ Sanitizzazione nomi file prima del salvataggio

---

## 📝 Note per lo sviluppatore

### Dipendenze richieste
- WordPress 5.8+
- PHP 7.4+ (per supporto `Throwable`)
- Use-your-Drive (per token OAuth e galleria finale)

### Test raccomandati
1. ✅ Testare con cartella Google Drive condivisa
2. ✅ Testare con cartella senza permessi (deve dare errore chiaro)
3. ✅ Testare import di 4 foto di dimensioni diverse
4. ✅ Verificare che lo shortcode galleria funzioni ancora

### Debug
Log disponibili in `wp-content/debug.log`:
- `📁 Tentativo di accesso alla cartella...`
- `✅ Trovate X foto nella cartella...`
- `📥 Inizio import di X foto...`
- `✅ Import completato...`

---

## 🎉 Risultato finale

### PRIMA
```
User → Use-your-Drive Client → Cache → ❌ get_id() on null
```

### DOPO
```
User → Google Drive API v3 → ✅ Lista file → ✅ Download → ✅ Import WordPress
                                                ↓
                                    Use-your-Drive: Solo galleria finale
```

---

## 🚀 Prossimi passi (opzionali)

- [ ] Aggiungere paginazione per cartelle con 1000+ foto
- [ ] Cache locale dei thumbnail per velocizzare ricaricamenti
- [ ] Supporto per video oltre alle immagini
- [ ] Bulk import di più eventi da più cartelle
- [ ] Dashboard per monitorare import in corso

---

**Autore:** AI Assistant (Claude Sonnet 4.5)  
**Revisione:** v1.0
