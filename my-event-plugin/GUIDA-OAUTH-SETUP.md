# 🔐 Guida Setup OAuth Google Drive

## 📋 Cosa Serve

Per far funzionare il browser Google Drive integrato, devi:
1. ✅ Creare un progetto Google Cloud
2. ✅ Abilitare Google Drive API
3. ✅ Ottenere credenziali OAuth (Client ID e Secret)
4. ✅ Autorizzare l'accesso

---

## 🚀 Setup Passo-Passo (5 minuti)

### **Step 1: Crea Progetto Google Cloud**

1. Vai su [Google Cloud Console](https://console.cloud.google.com)
2. Clicca **"Select a project"** in alto
3. Clicca **"New Project"**
4. Nome progetto: `My Event Plugin` (o quello che vuoi)
5. Clicca **"Create"**
6. Aspetta 30 secondi che il progetto venga creato

---

### **Step 2: Abilita Google Drive API**

1. Nel menu a sinistra → **"APIs & Services"** → **"Library"**
2. Cerca `Google Drive API`
3. Clicca sul risultato **"Google Drive API"**
4. Clicca il pulsante blu **"ENABLE"**
5. Aspetta che si attivi (pochi secondi)

---

### **Step 3: Crea Credenziali OAuth**

1. Nel menu a sinistra → **"APIs & Services"** → **"Credentials"**
2. Clicca **"+ CREATE CREDENTIALS"** in alto
3. Seleziona **"OAuth client ID"**
4. Se ti chiede di configurare "OAuth consent screen":
   - Clicca **"CONFIGURE CONSENT SCREEN"**
   - Scegli **"External"** (a meno che hai Google Workspace)
   - Clicca **"CREATE"**
   - Inserisci:
     - **App name:** `My Event Plugin`
     - **User support email:** La tua email
     - **Developer contact:** La tua email
   - Clicca **"SAVE AND CONTINUE"** (salta tutto il resto)
   - Clicca **"BACK TO DASHBOARD"**
5. Torna in **"Credentials"** → **"+ CREATE CREDENTIALS"** → **"OAuth client ID"**
6. Scegli tipo applicazione: **"Web application"**
7. Nome: `My Event Plugin Client`
8. Nella sezione **"Authorized redirect URIs"**:
   - Clicca **"+ ADD URI"**
   - **Incolla l'URL che vedi nella pagina Impostazioni del plugin** (es: `https://tuosito.it/wp-admin/admin.php?page=my-event-settings&google_auth=callback`)
9. Clicca **"CREATE"**
10. **IMPORTANTISSIMO:** Copia il **Client ID** e il **Client Secret** che appaiono!

---

### **Step 4: Configura il Plugin**

1. Vai su **WordPress Admin** → **Gestione Eventi** → **Impostazioni**
2. Nella sezione **"🔐 Autorizzazione Google Drive"**:
   - Incolla il **Client ID** nel primo campo
   - Incolla il **Client Secret** nel secondo campo
3. Clicca **"Salva Impostazioni"**
4. Clicca il pulsante verde **"🔗 Autorizza con Google"**
5. Si apre Google → Scegli il tuo account Google
6. Clicca **"Continua"** (ignora l'avviso "app non verificata")
7. Clicca **"Continua"** di nuovo
8. Seleziona la checkbox **"Visualizza file di Google Drive"**
9. Clicca **"Continua"**
10. Vieni reindirizzato su WordPress → Vedi **"✅ Autorizzazione completata!"**

---

## ✅ Verifica che Funziona

1. Vai su **Gestione Eventi** → **Crea Nuovo Evento**
2. Dovresti vedere il browser Google Drive con le tue cartelle!
3. Clicca su una cartella → Si apre
4. Clicca **"Seleziona Questa Cartella"** → Vedi le foto!

---

## 🐛 Troubleshooting

### ❌ "Client ID o Secret mancanti"
**Soluzione:** Hai dimenticato di salvare le impostazioni. Clicca "Salva Impostazioni" dopo aver incollato Client ID e Secret.

### ❌ "redirect_uri_mismatch"
**Soluzione:** La Redirect URI in Google Cloud Console non corrisponde. Vai in Google Cloud Console → Credentials → Modifica OAuth Client → Verifica che la Redirect URI sia ESATTAMENTE uguale a quella mostrata nel plugin (copia-incolla!).

### ❌ "Errore 403: access_denied"
**Soluzione:** 
1. Vai in Google Cloud Console
2. "APIs & Services" → "OAuth consent screen"
3. Clicca "PUBLISH APP" (potrebbe essere in "Testing")
4. Riautorizza nel plugin

### ❌ "Caricamento cartelle" gira all'infinito
**Soluzione:** Controlla la Console JavaScript (F12) per errori. Probabilmente manca l'autorizzazione o il token è scaduto. Vai in Impostazioni e verifica lo stato dell'autorizzazione.

### ❌ "Token scaduto"
**Soluzione:** Il refresh automatico dovrebbe funzionare. Se continua a dare errore:
1. Vai in Impostazioni
2. Clicca "Revoca Autorizzazione"
3. Clicca "Autorizza con Google" di nuovo

---

## 🔒 Sicurezza

### Dove sono salvati i token?
- Nel database WordPress (tabella `wp_options`)
- I token sono accessibili solo agli admin di WordPress
- **MAI** condividere il Client Secret pubblicamente
- Se pensi che il Secret sia compromesso, vai in Google Cloud Console → Credentials → Reset secret

### Posso condividere Client ID?
- Il Client ID è "pubblico" (viene visto nel browser)
- Il Client Secret è PRIVATO (mai condividerlo!)

---

## 📊 Scopes Richiesti

Il plugin richiede solo:
- `https://www.googleapis.com/auth/drive.readonly`

Questo significa:
- ✅ Può LEGGERE file e cartelle
- ❌ NON può modificare, eliminare o creare file
- ✅ Sicuro e minimale

---

## 🎉 Fatto!

Ora il browser Google Drive è completamente funzionante! 🚀

Se hai problemi, attiva il debug WordPress:
```php
// In wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

E controlla `wp-content/debug.log` per errori dettagliati.
