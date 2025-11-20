# 🐛 Guida Debug - Errore 500

## Come Vedere l'Errore Esatto

Quando vedi "Errore 500" nella console, significa che c'è un errore PHP lato server. Ecco come scoprire quale:

### 📝 Passo 1: Attiva il Debug di WordPress

Apri il file `wp-config.php` nella root di WordPress e aggiungi/modifica queste righe:

```php
// Prima della riga che dice "That's all, stop editing!"

define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
@ini_set('display_errors', 0);
```

### 📋 Passo 2: Riproduci l'Errore

1. Vai in **Eventi Auto**
2. Clicca su una cartella (o usa il metodo rapido con ID)
3. Aspetta che appaia l'errore

### 📖 Passo 3: Leggi il Log

Il log si trova in: `/wp-content/debug.log`

Cerca le righe che iniziano con `[My Event Plugin]` o cerca l'errore più recente.

**Esempio di cosa potresti vedere:**

```
[17-Jan-2024 10:30:15 UTC] PHP Fatal error: Call to undefined method...
[17-Jan-2024 10:30:15 UTC] [My Event Plugin] AJAX: handle_get_folder_photos chiamato
[17-Jan-2024 10:30:15 UTC] [My Event Plugin] AJAX: Recupero foto dalla cartella: 1a2b3c...
[17-Jan-2024 10:30:16 UTC] [My Event Plugin] ERROR: Errore nel recupero foto...
```

---

## 🔍 Errori Comuni e Soluzioni

### Errore: "Call to undefined method TheLion\UseyourDrive\Client::instance()"

**Causa**: Use-your-Drive non è caricato correttamente.

**Soluzione**:
1. Vai in **Plugin** > **Plugin installati**
2. Disattiva e riattiva **Use-your-Drive**
3. Verifica che la versione sia compatibile (>= 3.0)

---

### Errore: "Cartella non trovata o vuota"

**Causa**: L'ID della cartella è sbagliato o l'account non ha accesso.

**Soluzione**:
1. Verifica che l'ID sia corretto
2. Apri [Google Drive](https://drive.google.com) e controlla che la cartella esista
3. Verifica che l'account Use-your-Drive abbia accesso alla cartella

**Come verificare l'ID**:
```
URL: https://drive.google.com/drive/folders/1a2b3c4d5e6f7g8h9i0j
                                            └──────┬──────┘
                                                   ID
```

---

### Errore: "Use-your-Drive Client non disponibile"

**Causa**: Use-your-Drive non è installato o è disattivato.

**Soluzione**:
1. Vai in **Plugin** > **Plugin installati**
2. Verifica che **Use-your-Drive** sia attivo
3. Se non c'è, installalo da [WP Cloud Plugins](https://www.wpcloudplugins.com/)

---

### Errore: "Nessun account Google Drive è connesso"

**Causa**: Non hai autorizzato un account Google Drive.

**Soluzione**:
1. Vai in **Use-your-Drive** > **Settings** > **Accounts**
2. Clicca **"Add Account"**
3. Segui il processo di autorizzazione OAuth
4. Verifica che appaia **"✓ Authorized"**

---

### Errore: "get_thumbnail_with_size(): Call to a member function on null"

**Causa**: Un file nella cartella non è accessibile o è danneggiato.

**Soluzione**:
1. Apri la cartella su Google Drive
2. Controlla che tutti i file siano accessibili
3. Prova a rimuovere file recenti o sospetti
4. Usa una cartella diversa per test

---

## 🎯 Debug Avanzato

### Vedere la Risposta AJAX Completa

1. Apri la **Console** del browser (F12)
2. Vai nella tab **Network**
3. Clicca su una cartella
4. Cerca la richiesta `admin-ajax.php` con `action=mep_get_folder_photos`
5. Clicca sulla richiesta
6. Guarda la tab **Response**

**Esempio risposta errore:**
```json
{
  "success": false,
  "data": {
    "message": "Errore API Use-your-Drive: Access denied",
    "code": "api_error",
    "folder_id": "1a2b3c4d..."
  }
}
```

---

## 📞 Contattare il Supporto

Se l'errore persiste, invia:

1. **Il contenuto del debug.log** (ultime 50 righe)
2. **La risposta AJAX** dalla tab Network
3. **La versione di Use-your-Drive** (vai in Plugin > Plugin installati)
4. **La versione di WordPress** (vai in Dashboard)

---

## ✅ Checklist Verifica

Prima di chiedere supporto, verifica:

- [ ] WP_DEBUG è attivo
- [ ] Use-your-Drive è installato e attivo
- [ ] Almeno un account Google Drive è autorizzato
- [ ] L'account ha accesso alla cartella
- [ ] La cartella contiene file immagine (JPG, PNG, GIF, WebP)
- [ ] Ho guardato il file `debug.log`
- [ ] Ho guardato la risposta nella tab Network della console

---

## 🚀 Test Rapido

Prova questo test per verificare che Use-your-Drive funzioni:

```php
// Aggiungi temporaneamente in wp-config.php (POI RIMUOVI!)
// DOPO le righe di WP_DEBUG

add_action('admin_init', function() {
    if (isset($_GET['test_uyd'])) {
        echo '<pre>';
        echo "Use-your-Drive Test:\n\n";
        
        // Test 1: Classe esiste
        echo "1. Classe Client: ";
        echo class_exists('TheLion\UseyourDrive\Client') ? "✓ OK\n" : "✗ MANCANTE\n";
        
        // Test 2: Instance
        echo "2. Client instance: ";
        try {
            $client = \TheLion\UseyourDrive\Client::instance();
            echo $client ? "✓ OK\n" : "✗ NULL\n";
        } catch (Exception $e) {
            echo "✗ ERRORE: " . $e->getMessage() . "\n";
        }
        
        // Test 3: Accounts
        echo "3. Accounts: ";
        $accounts = \TheLion\UseyourDrive\Accounts::instance()->list_accounts();
        echo count($accounts) . " trovati\n";
        
        foreach ($accounts as $acc) {
            echo "   - " . $acc->get_email();
            echo $acc->get_authorization()->has_access_token() ? " ✓ Autorizzato\n" : " ✗ Non autorizzato\n";
        }
        
        echo '</pre>';
        die();
    }
});
```

Poi vai su: `tuosito.com/wp-admin/?test_uyd`

**Rimuovi il codice dopo il test!**

---

**Ultimo aggiornamento**: 2024-01-17
