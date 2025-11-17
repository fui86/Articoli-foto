# 🔧 Troubleshooting - My Event Plugin

## 🔒 Errore: "Use-your-Drive non riesce ad accedere alla cartella"

### ⚠️ PROBLEMA PIÙ COMUNE

**Errore**: `Call to a member function get_id() on null in Cache.php`

**Causa**: L'account Use-your-Drive non ha i permessi per accedere alla cartella.

### ✅ Soluzione Rapida

1. **Trova l'email dell'account Use-your-Drive**:
   - Vai in **Use-your-Drive** > **Settings** > **Accounts**
   - Copia l'email dell'account autorizzato (es: `tuoaccount@gmail.com`)

2. **Condividi la cartella con quell'account**:
   - Apri [Google Drive](https://drive.google.com)
   - Trova la cartella con le foto
   - Click destro > **Condividi**
   - Aggiungi l'email Use-your-Drive
   - Imposta permessi: **Visualizzatore** (basta questo)
   - Clicca **Invia**

3. **Riprova nel plugin**:
   - Torna in **Eventi Auto**
   - Ricarica la pagina (Ctrl+Shift+R)
   - Clicca sulla cartella
   - Ora dovrebbe funzionare! 🎉

### 🔍 Verifica Rapida

La cartella è:
- [ ] Nel tuo "Il mio Drive" dell'account Use-your-Drive? → Dovrebbe funzionare
- [ ] Condivisa con te da qualcun altro? → **Devi ricondividerla con Use-your-Drive**
- [ ] In un Team Drive? → **Verifica che Use-your-Drive abbia accesso al Team Drive**

---

## ❌ Errore: "Errore di connessione" quando seleziono una cartella

### Cause Possibili

1. **Use-your-Drive non è configurato correttamente**
2. **L'account Google Drive non è autorizzato**
3. **La cartella selezionata non contiene foto**
4. **Permessi insufficienti sulla cartella Google Drive** ← PIÙ COMUNE

### Soluzioni

#### 1. Verifica Configurazione Use-your-Drive

1. Vai in **WordPress Admin** > **Use-your-Drive** > **Settings**
2. Vai nella tab **Accounts**
3. Verifica che ci sia almeno un account con lo stato **"✓ Authorized"**
4. Se non c'è, clicca **"Add Account"** e autorizza l'accesso a Google Drive

#### 2. Usa il Metodo Alternativo: Input Manuale

Se il browser di Use-your-Drive non funziona, puoi inserire manualmente l'ID della cartella:

**Come ottenere l'ID della cartella Google Drive:**

1. Apri [Google Drive](https://drive.google.com) nel browser
2. Naviga nella cartella che contiene le foto dell'evento
3. Guarda l'URL nella barra degli indirizzi:
   ```
   https://drive.google.com/drive/folders/1a2b3c4d5e6f7g8h9i0j
   ```
4. L'ID è la parte dopo `/folders/`: `1a2b3c4d5e6f7g8h9i0j`
5. Copia questo ID
6. Nel plugin, incolla l'ID nel campo **"Metodo Alternativo: Incolla l'ID della Cartella"**
7. Clicca **"Carica Foto"**

#### 3. Controlla i Permessi della Cartella

Assicurati che:
- L'account Google autorizzato abbia accesso alla cartella
- La cartella non sia in "Solo Visualizzazione"
- La cartella contenga almeno 4 file immagine (JPG, PNG, GIF, WebP)

---

## 🔍 "Non vedo i file quando navigo nelle cartelle"

### Soluzione

1. **Usa l'Input Manuale**: Invece di navigare, incolla direttamente l'ID della cartella (vedi sopra)
2. **Ricarica la pagina**: A volte Use-your-Drive ha bisogno di un refresh
3. **Controlla Cache**: Svuota la cache del browser (Ctrl+Shift+R)

---

## 📁 "Nessuna foto trovata nella cartella"

### Verifica che:

1. La cartella contiene file immagine (non cartelle vuote)
2. I file hanno estensioni supportate: `.jpg`, `.jpeg`, `.png`, `.gif`, `.webp`
3. I file NON sono in sottocartelle (il plugin legge solo la cartella principale)

---

## 🚫 "Post template non trovato"

### Soluzione

1. Vai in **Eventi Auto** > **Impostazioni**
2. Seleziona un post esistente come template
3. Se non hai post, crea prima un post normale in WordPress
4. Usa quel post come template

---

## 🔐 "Account Google Drive non autorizzato"

### Soluzione

1. Vai in **Use-your-Drive** > **Settings** > **Accounts**
2. Trova l'account con lo stato **"Not Authorized"**
3. Clicca **"Authorize"**
4. Segui il processo di autorizzazione OAuth di Google
5. Torna su WordPress e verifica che ora appaia **"✓ Authorized"**

---

## 🐛 Debug Mode

Per ottenere più informazioni sugli errori:

1. Attiva il debug di WordPress in `wp-config.php`:
   ```php
   define('WP_DEBUG', true);
   define('WP_DEBUG_LOG', true);
   define('WP_DEBUG_DISPLAY', false);
   ```

2. Controlla i log in `/wp-content/debug.log`

3. Apri la **Console JavaScript** del browser (F12) e cerca errori in rosso

---

## 💬 Supporto

Se nessuna di queste soluzioni funziona:

1. Controlla i log di WordPress (`/wp-content/debug.log`)
2. Controlla la console JavaScript del browser (F12)
3. Verifica che Use-your-Drive sia aggiornato all'ultima versione
4. Prova a riautorizzare l'account Google Drive

---

## ✅ Test Rapido

Per verificare che tutto funzioni:

1. Vai in **Eventi Auto**
2. Nel campo **"Metodo Alternativo"**, incolla questo ID di test: `1a2b3c4d5e6f7g8h9i0j`
3. Clicca **"Carica Foto"**
4. Se vedi un errore specifico nella console, quello ti indica il problema esatto

---

**Ultimo aggiornamento**: 2024-01-17
