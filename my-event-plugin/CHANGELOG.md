# Changelog - My Event Plugin

## [1.0.1] - 2024-01-17

### 🔧 Fixed
- **Errore di connessione Use-your-Drive**: Semplificato shortcode e rimosso parametro `dir="drive"` problematico
- **File non visibili**: Cambiato parametro `showfiles="0"` a `showfiles="1"` per mostrare sia file che cartelle
- **Click handler**: Migliorato l'intercettazione del click sulle cartelle con più debug

### ✨ Added
- **Metodo Alternativo**: Aggiunto campo input per incollare manualmente l'ID della cartella Google Drive
- **Supporto Enter Key**: Premendo Enter nell'input manuale si carica automaticamente la cartella
- **Debug Migliorato**: Aggiunto controllo se lo shortcode esiste e se genera output
- **Messaggi di Errore Dettagliati**: Messaggi più specifici quando Use-your-Drive non funziona
- **File TROUBLESHOOTING.md**: Guida completa per risolvere i problemi comuni
- **Console Logging**: Più informazioni nella console JavaScript per debug

### 🎨 Improved
- **UI Passi Guidati**: Box colorati con gradienti per Passo 1, 2, 3
- **Feedback Visivo**: Contatore dinamico con cambio colori (blu → giallo → verde)
- **Animazioni**: Transizioni smooth per selezione foto e scroll automatico
- **Pulsante "Cancella Selezione"**: Per ricominciare la selezione foto
- **Auto-scroll**: Quando selezioni 4 foto, scroll automatico alla sezione successiva

### 📚 Documentation
- Aggiunto `TROUBLESHOOTING.md` con guida per ottenere ID cartella Google Drive
- Documentato come usare il metodo alternativo di input manuale

---

## Come Usare il Metodo Alternativo

Se il browser Use-your-Drive non funziona:

1. Vai su [Google Drive](https://drive.google.com)
2. Apri la cartella con le foto
3. Copia l'ID dall'URL: `https://drive.google.com/drive/folders/ID_QUI`
4. Incolla l'ID nel campo "Metodo Alternativo"
5. Clicca "Carica Foto"

---

## [1.0.0] - 2024-01-XX

### Initial Release
- Creazione automatica eventi da Google Drive
- Selezione manuale di 4 foto tramite miniature
- Scelta foto di copertina
- Integrazione Use-your-Drive
- Supporto SEO Rank Math
- Template clonazione post
