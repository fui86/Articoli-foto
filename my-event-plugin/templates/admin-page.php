<?php
/**
 * Template: Pagina Admin Principale - Form Creazione Evento
 */

defined('ABSPATH') || exit;

// Verifica che Use-your-Drive sia pronto
$uyd_check = MEP_Helpers::check_useyourdrive_ready();
?>

<div class="wrap mep-admin-wrap">
    <h1 class="mep-page-title">
        <span class="dashicons dashicons-calendar-alt"></span>
        <?php _e('Crea Nuovo Evento', 'my-event-plugin'); ?>
    </h1>
    
    <?php if (is_wp_error($uyd_check)): ?>
        <div class="notice notice-error">
            <p><strong><?php _e('Attenzione:', 'my-event-plugin'); ?></strong> <?php echo esc_html($uyd_check->get_error_message()); ?></p>
        </div>
    <?php endif; ?>
    
    <div class="mep-admin-container">
        
        <!-- Sidebar Info -->
        <div class="mep-sidebar">
            <div class="mep-info-box">
                <h3><?php _e('Come Funziona', 'my-event-plugin'); ?></h3>
                <ol>
                    <li><?php _e('Compila il form con i dettagli dell\'evento', 'my-event-plugin'); ?></li>
                    <li><?php _e('Seleziona la cartella Google Drive con le foto', 'my-event-plugin'); ?></li>
                    <li><?php _e('Clicca su "Crea Evento"', 'my-event-plugin'); ?></li>
                    <li><?php _e('Il plugin scaricherà automaticamente le foto e creerà l\'articolo!', 'my-event-plugin'); ?></li>
                </ol>
            </div>
            
            <div class="mep-info-box mep-tips">
                <h3><?php _e('💡 Suggerimenti', 'my-event-plugin'); ?></h3>
                <ul>
                    <li><?php _e('La cartella deve contenere almeno 4 foto', 'my-event-plugin'); ?></li>
                    <li><?php _e('Usa nomi file descrittivi per le foto', 'my-event-plugin'); ?></li>
                    <li><?php _e('Formati supportati: JPG, PNG, GIF, WebP', 'my-event-plugin'); ?></li>
                    <li><?php _e('La prima foto diventerà l\'immagine in evidenza', 'my-event-plugin'); ?></li>
                </ul>
            </div>
        </div>
        
        <!-- Form Principale -->
        <div class="mep-main-content">
            <form id="mep-event-form" class="mep-form" method="post">
                <?php wp_nonce_field('mep_nonce', 'mep_nonce_field'); ?>
                
                <!-- Titolo Evento -->
                <div class="mep-form-row">
                    <label for="event_title" class="mep-label required">
                        <?php _e('Titolo Evento', 'my-event-plugin'); ?>
                    </label>
                    <input type="text" 
                           id="event_title" 
                           name="event_title" 
                           class="mep-input large" 
                           required
                           placeholder="<?php esc_attr_e('Es: Serata Live Music - Sabato 15 Marzo', 'my-event-plugin'); ?>">
                    <p class="mep-description">
                        <?php _e('Il titolo principale che apparirà nell\'articolo', 'my-event-plugin'); ?>
                    </p>
                </div>
                
                <!-- Categoria -->
                <div class="mep-form-row">
                    <label for="event_category" class="mep-label required">
                        <?php _e('Categoria Articolo', 'my-event-plugin'); ?>
                    </label>
                    <?php 
                    wp_dropdown_categories([
                        'name' => 'event_category',
                        'id' => 'event_category',
                        'class' => 'mep-select',
                        'hide_empty' => false,
                        'required' => true,
                        'show_option_none' => __('-- Seleziona Categoria --', 'my-event-plugin'),
                        'option_none_value' => ''
                    ]);
                    ?>
                </div>
                
                <!-- SEO Section -->
                <div class="mep-section">
                    <h2 class="mep-section-title">
                        <span class="dashicons dashicons-chart-line"></span>
                        <?php _e('SEO - Ottimizzazione Motori di Ricerca', 'my-event-plugin'); ?>
                    </h2>
                    
                    <?php if (MEP_Helpers::is_rankmath_active()): ?>
                        <p class="mep-notice mep-notice-success">
                            ✓ <?php _e('Rank Math è attivo. I metadati SEO verranno salvati automaticamente.', 'my-event-plugin'); ?>
                        </p>
                    <?php else: ?>
                        <p class="mep-notice mep-notice-info">
                            <?php _e('Installa Rank Math per gestire al meglio la SEO dei tuoi eventi.', 'my-event-plugin'); ?>
                        </p>
                    <?php endif; ?>
                    
                    <div class="mep-form-row">
                        <label for="seo_focus_keyword" class="mep-label">
                            <?php _e('Focus Keyword', 'my-event-plugin'); ?>
                        </label>
                        <input type="text" 
                               id="seo_focus_keyword" 
                               name="seo_focus_keyword" 
                               class="mep-input"
                               placeholder="<?php esc_attr_e('Es: live music roma', 'my-event-plugin'); ?>">
                        <p class="mep-description">
                            <?php _e('Parola chiave principale per cui vuoi posizionare l\'articolo', 'my-event-plugin'); ?>
                        </p>
                    </div>
                    
                    <div class="mep-form-row">
                        <label for="seo_title" class="mep-label">
                            <?php _e('Titolo SEO', 'my-event-plugin'); ?>
                        </label>
                        <input type="text" 
                               id="seo_title" 
                               name="seo_title" 
                               class="mep-input large" 
                               maxlength="60"
                               placeholder="<?php esc_attr_e('Lascia vuoto per usare il titolo evento', 'my-event-plugin'); ?>">
                        <p class="mep-description">
                            <span class="seo-counter">0/60</span> caratteri • 
                            <?php _e('Questo titolo apparirà nei risultati di Google', 'my-event-plugin'); ?>
                        </p>
                    </div>
                    
                    <div class="mep-form-row">
                        <label for="seo_description" class="mep-label">
                            <?php _e('Meta Description', 'my-event-plugin'); ?>
                        </label>
                        <textarea id="seo_description" 
                                  name="seo_description" 
                                  class="mep-textarea" 
                                  rows="3" 
                                  maxlength="160"
                                  placeholder="<?php esc_attr_e('Descrizione breve che apparirà su Google...', 'my-event-plugin'); ?>"></textarea>
                        <p class="mep-description">
                            <span class="desc-counter">0/160</span> caratteri • 
                            <?php _e('Descrizione che apparirà sotto il titolo nei risultati di ricerca', 'my-event-plugin'); ?>
                        </p>
                    </div>
                </div>
                
                <!-- Contenuto HTML -->
                <div class="mep-form-row">
                    <label for="event_content" class="mep-label">
                        <?php _e('Contenuto Evento (HTML)', 'my-event-plugin'); ?>
                    </label>
                    <textarea id="event_content" 
                              name="event_content" 
                              class="mep-textarea code" 
                              rows="8"
                              placeholder="<?php esc_attr_e('Incolla qui il codice HTML del contenuto dell\'evento...', 'my-event-plugin'); ?>"></textarea>
                    <p class="mep-description">
                        <?php _e('Puoi incollare HTML. Lascia vuoto per usare il contenuto del template.', 'my-event-plugin'); ?>
                    </p>
                </div>
                
                <!-- Folder Google Drive -->
                <div class="mep-section mep-gdrive-section">
                    <div style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color: white; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
                        <h2 style="margin: 0 0 10px 0; color: white; display: flex; align-items: center; gap: 10px; font-size: 20px;">
                            <span class="dashicons dashicons-cloud" style="font-size: 28px; width: 28px; height: 28px;"></span>
                            <?php _e('Passo 1: Seleziona la Cartella con le Foto', 'my-event-plugin'); ?>
                        </h2>
                        <p style="margin: 0; opacity: 0.95; line-height: 1.6;">
                            <?php _e('📁 Naviga nel tuo Google Drive e clicca sulla cartella che contiene le foto dell\'evento.', 'my-event-plugin'); ?><br>
                            <?php _e('✨ Dopo aver selezionato la cartella, vedrai tutte le foto disponibili per l\'importazione.', 'my-event-plugin'); ?>
                        </p>
                    </div>
                    
                    <div class="mep-form-row">
                        
                        <!-- Metodo Alternativo: Input Manuale ID Cartella -->
                        <div style="margin-bottom: 20px; padding: 15px; background: #f9f9f9; border: 1px solid #ddd; border-radius: 4px;">
                            <label style="display: block; font-weight: 600; margin-bottom: 8px;">
                                <span class="dashicons dashicons-admin-links" style="color: #2271b1;"></span>
                                <?php _e('Metodo Alternativo: Incolla l\'ID della Cartella', 'my-event-plugin'); ?>
                            </label>
                            <div style="display: flex; gap: 10px; align-items: flex-start;">
                                <input type="text" 
                                       id="mep-manual-folder-id" 
                                       placeholder="<?php esc_attr_e('Es: 1a2b3c4d5e6f7g8h9i0j', 'my-event-plugin'); ?>"
                                       style="flex: 1; padding: 8px 12px; border: 1px solid #8c8f94; border-radius: 4px;">
                                <button type="button" 
                                        id="mep-load-manual-folder" 
                                        class="button button-secondary">
                                    <?php _e('Carica Foto', 'my-event-plugin'); ?>
                                </button>
                            </div>
                            <p style="margin: 8px 0 0 0; color: #646970; font-size: 12px;">
                                <?php _e('💡 Se il browser qui sotto non funziona, puoi incollare direttamente l\'ID della cartella Google Drive e cliccare "Carica Foto"', 'my-event-plugin'); ?>
                            </p>
                        </div>
                        
                        <div id="mep-folder-validation-message" class="mep-validation-message" style="display:none;"></div>
                        
                        <!-- Use-your-Drive Folder Selector -->
                        <div class="mep-folder-selector-container" id="mep-uyd-browser">
                            <?php
                            // Verifica che Use-your-Drive sia disponibile
                            if (!shortcode_exists('useyourdrive')) {
                                // Plugin non attivo
                                echo '<div style="padding: 20px; background: #f8d7da; border: 1px solid #d63638; border-radius: 4px;">';
                                echo '<p style="margin: 0; color: #721c24;"><strong>❌ Errore:</strong> Use-your-Drive non è attivo!</p>';
                                echo '<p style="margin: 10px 0 0 0;"><a href="' . admin_url('plugins.php') . '" class="button">Attiva Use-your-Drive</a></p>';
                                echo '</div>';
                            } elseif (!class_exists('TheLion\UseyourDrive\Accounts')) {
                                // Classe mancante
                                echo '<div style="padding: 20px; background: #fff3cd; border: 1px solid #ffc107; border-radius: 4px;">';
                                echo '<p style="margin: 0; color: #856404;"><strong>⚠️ Attenzione:</strong> Use-your-Drive è attivo ma non completamente caricato.</p>';
                                echo '<p style="margin: 10px 0 0 0;">Prova a ricaricare la pagina o reinstalla Use-your-Drive.</p>';
                                echo '</div>';
                            } else {
                                // Verifica account Google Drive
                                $accounts = \TheLion\UseyourDrive\Accounts::instance()->list_accounts();
                                if (empty($accounts)) {
                                    // Nessun account configurato
                                    echo '<div style="padding: 20px; background: #fff3cd; border: 1px solid #ffc107; border-radius: 4px;">';
                                    echo '<p style="margin: 0; color: #856404;"><strong>⚠️ Configurazione Richiesta:</strong> Devi collegare un account Google Drive prima di usare questa funzionalità.</p>';
                                    echo '<ol style="margin: 10px 0; padding-left: 20px; color: #856404;">';
                                    echo '<li>Vai nelle <strong>Impostazioni Use-your-Drive</strong></li>';
                                    echo '<li>Clicca su <strong>"Accounts"</strong></li>';
                                    echo '<li>Clicca su <strong>"Add Account"</strong></li>';
                                    echo '<li>Autorizza l\'accesso a Google Drive</li>';
                                    echo '<li>Torna qui e ricarica la pagina</li>';
                                    echo '</ol>';
                                    echo '<p style="margin: 10px 0 0 0;"><a href="' . admin_url('admin.php?page=use_your_drive_settings') . '" class="button button-primary">Vai alle Impostazioni Use-your-Drive</a></p>';
                                    echo '</div>';
                                } else {
                                    // Tutto OK - renderizza lo shortcode
                                    echo '<div style="margin-bottom: 10px; padding: 10px; background: #f0f6fc; border-radius: 4px;">';
                                    echo '<p style="margin: 0; font-size: 13px; color: #1d2327;">';
                                    echo '<span class="dashicons dashicons-info" style="color: #2271b1;"></span> ';
                                    echo '<strong>Come usare:</strong> Naviga nelle cartelle e <strong>clicca sulla cartella</strong> che contiene le foto dell\'evento. Vedrai le foto apparire sotto.';
                                    echo '</p>';
                                    echo '</div>';
                                    
                                    // Verifica che lo shortcode esista
                                    if (!shortcode_exists('useyourdrive')) {
                                        echo '<div style="padding: 15px; background: #ffe5e8; border-left: 4px solid #d63638; border-radius: 4px; margin-top: 10px;">';
                                        echo '<strong>⚠️ Errore:</strong> Lo shortcode [useyourdrive] non è disponibile. ';
                                        echo 'Verifica che Use-your-Drive sia installato e attivo.';
                                        echo '</div>';
                                    } else {
                                        // Shortcode semplificato - lascia che Use-your-Drive gestisca tutto
                                        // Mostra sia cartelle che file per permettere la navigazione
                                        $shortcode = '[useyourdrive mode="files" viewrole="administrator" candownloadzip="0" showsharelink="0" showfiles="1" showfolders="1" include_ext="jpg,jpeg,png,gif,webp" maxheight="400px" showbreadcrumb="1"]';
                                        
                                        // Debug: mostra lo shortcode generato
                                        if (defined('WP_DEBUG') && WP_DEBUG) {
                                            echo '<!-- Shortcode generato: ' . esc_html($shortcode) . ' -->';
                                            echo '<!-- Accounts: ' . print_r($accounts, true) . ' -->';
                                        }
                                        
                                        // Renderizza lo shortcode
                                        $output = do_shortcode($shortcode);
                                        
                                        // Verifica se l'output è vuoto
                                        if (empty(trim($output))) {
                                            echo '<div style="padding: 15px; background: #fff3cd; border-left: 4px solid #dba617; border-radius: 4px; margin-top: 10px;">';
                                            echo '<strong>⚠️ Attenzione:</strong> Use-your-Drive non ha generato alcun output. ';
                                            echo 'Possibili cause:<br>';
                                            echo '<ul style="margin: 10px 0 0 20px; padding: 0;">';
                                            echo '<li>Nessun account Google Drive è autorizzato</li>';
                                            echo '<li>Le autorizzazioni dell\'account sono scadute</li>';
                                            echo '<li>Use-your-Drive ha bisogno di essere riconfigurato</li>';
                                            echo '</ul>';
                                            echo '<p style="margin: 10px 0 0 0;"><a href="' . admin_url('admin.php?page=use_your_drive_settings') . '" class="button button-primary">Configura Use-your-Drive</a></p>';
                                            echo '</div>';
                                        } else {
                                            echo $output;
                                        }
                                    }
                                }
                            }
                            ?>
                        </div>
                        
                        <!-- Campi nascosti popolati dal selector -->
                        <input type="hidden" name="event_folder_id" id="event_folder_id">
                        <input type="hidden" name="event_folder_account" id="event_folder_account">
                        <input type="hidden" name="event_folder_name" id="event_folder_name">
                    </div>
                    
                    <!-- Griglia Selezione Foto Manuale -->
                    <div id="mep-photo-selector-wrapper" style="display:none;">
                        <hr style="margin: 30px 0; border: 0; border-top: 2px solid #2271b1;">
                        
                        <div class="mep-form-row">
                            <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
                                <h3 style="margin: 0 0 10px 0; color: white; display: flex; align-items: center; gap: 10px;">
                                    <span class="dashicons dashicons-images-alt2" style="font-size: 24px; width: 24px; height: 24px;"></span>
                                    <?php _e('Passo 2: Seleziona le Foto da Importare', 'my-event-plugin'); ?>
                                </h3>
                                <p style="margin: 0; opacity: 0.95; line-height: 1.6;">
                                    <?php _e('📸 Clicca sulle miniature per selezionare 4 foto che verranno scaricate e importate nella galleria WordPress.', 'my-event-plugin'); ?><br>
                                    <?php _e('🖼️ Poi scegli quale delle 4 foto usare come immagine di copertina dell\'articolo.', 'my-event-plugin'); ?>
                                </p>
                            </div>
                            
                            <div id="mep-selection-info" class="mep-selection-info">
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <span class="dashicons dashicons-format-gallery" style="color: #2271b1; font-size: 20px;"></span>
                                    <span class="mep-selection-count" style="font-size: 15px;">
                                        <?php _e('Foto selezionate:', 'my-event-plugin'); ?> 
                                        <strong style="color: #2271b1; font-size: 18px;">0/4</strong>
                                    </span>
                                </div>
                                <div id="mep-selection-help" style="font-size: 13px; color: #646970;">
                                    <?php _e('Clicca sulle miniature per selezionarle', 'my-event-plugin'); ?>
                                </div>
                            </div>
                            
                            <!-- Griglia Foto -->
                            <div id="mep-photo-grid" class="mep-photo-grid">
                                <div class="mep-loading-grid">
                                    <span class="mep-spinner"></span>
                                    <p><?php _e('Caricamento foto dalla cartella...', 'my-event-plugin'); ?></p>
                                </div>
                            </div>
                            
                            <!-- Foto Selezionate -->
                            <div id="mep-selected-photos" class="mep-selected-photos" style="display:none;">
                                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 15px;">
                                    <h3 style="margin: 0;"><?php _e('✓ Foto Che Verranno Importate:', 'my-event-plugin'); ?></h3>
                                    <button type="button" id="mep-clear-selection" class="button button-secondary button-small">
                                        <?php _e('Cancella Selezione', 'my-event-plugin'); ?>
                                    </button>
                                </div>
                                <p style="margin: 0 0 15px 0; color: #646970; font-size: 13px;">
                                    <?php _e('Queste 4 foto verranno scaricate da Google Drive e aggiunte alla galleria WordPress. Clicca sulla X per rimuoverle.', 'my-event-plugin'); ?>
                                </p>
                                <div id="mep-selected-photos-list" class="mep-selected-photos-list"></div>
                                
                                <div class="mep-featured-image-selector" style="margin-top: 25px;">
                                    <label class="mep-label required" style="font-size: 15px;">
                                        <span class="dashicons dashicons-format-image"></span>
                                        <?php _e('Passo 3: Scegli la Foto di Copertina', 'my-event-plugin'); ?>
                                    </label>
                                    <p style="margin: 8px 0; color: #646970; font-size: 13px;">
                                        <?php _e('Questa sarà l\'immagine in evidenza che appare nelle anteprime dell\'articolo', 'my-event-plugin'); ?>
                                    </p>
                                    <select id="mep-featured-image-select" name="featured_image_index" class="mep-select" required style="max-width: 300px;">
                                        <option value=""><?php _e('-- Seleziona immagine di copertina --', 'my-event-plugin'); ?></option>
                                        <option value="0"><?php _e('📷 Foto 1 (Prima)', 'my-event-plugin'); ?></option>
                                        <option value="1"><?php _e('📷 Foto 2', 'my-event-plugin'); ?></option>
                                        <option value="2"><?php _e('📷 Foto 3', 'my-event-plugin'); ?></option>
                                        <option value="3"><?php _e('📷 Foto 4 (Ultima)', 'my-event-plugin'); ?></option>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- Campi nascosti con gli ID delle foto selezionate -->
                            <input type="hidden" name="selected_photo_ids" id="selected_photo_ids">
                        </div>
                    </div>
                </div>
                
                <!-- Submit Button -->
                <div class="mep-form-actions">
                    <button type="submit" 
                            class="button button-primary button-hero mep-submit-btn" 
                            id="mep-submit-btn">
                        <span class="dashicons dashicons-yes"></span>
                        <?php _e('Crea Evento', 'my-event-plugin'); ?>
                    </button>
                    
                    <div id="mep-status-message" class="mep-status-message"></div>
                </div>
                
            </form>
        </div>
        
    </div>
</div>
