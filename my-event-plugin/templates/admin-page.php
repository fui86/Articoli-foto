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
                    <div style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color: white; padding: 25px; border-radius: 10px; margin-bottom: 25px; box-shadow: 0 4px 15px rgba(17, 153, 142, 0.2);">
                        <h2 style="margin: 0 0 12px 0; color: white; display: flex; align-items: center; gap: 12px; font-size: 22px;">
                            <span class="dashicons dashicons-cloud" style="font-size: 32px; width: 32px; height: 32px;"></span>
                            <?php _e('📁 Passo 1: Seleziona la Cartella Google Drive', 'my-event-plugin'); ?>
                        </h2>
                        <p style="margin: 0; opacity: 0.95; line-height: 1.7; font-size: 15px;">
                            <?php _e('Incolla l\'ID della cartella Google Drive che contiene le foto dell\'evento.', 'my-event-plugin'); ?><br>
                            <?php _e('✨ Il plugin caricherà automaticamente tutte le foto disponibili per la selezione!', 'my-event-plugin'); ?>
                        </p>
                    </div>
                    
                    <div class="mep-form-row">
                        
                        <!-- Input ID Cartella Google Drive -->
                        <div style="margin-bottom: 20px; padding: 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 10px; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);">
                            <label style="display: flex; align-items: center; gap: 10px; font-weight: 600; margin-bottom: 15px; color: white; font-size: 16px;">
                                <span class="dashicons dashicons-admin-links" style="font-size: 24px; width: 24px; height: 24px;"></span>
                                <?php _e('🔗 ID della Cartella Google Drive', 'my-event-plugin'); ?>
                            </label>
                            <div style="display: flex; gap: 12px; align-items: flex-start;">
                                <input type="text" 
                                       id="mep-manual-folder-id" 
                                       placeholder="<?php esc_attr_e('Incolla qui l\'ID: 1a2b3c4d5e6f7g8h9i0j', 'my-event-plugin'); ?>"
                                       style="flex: 1; padding: 12px 16px; border: 3px solid rgba(255,255,255,0.3); border-radius: 8px; font-family: monospace; font-size: 14px; background: white;">
                                <button type="button" 
                                        id="mep-load-manual-folder" 
                                        class="button button-primary button-hero"
                                        style="background: #38ef7d; border-color: #38ef7d; color: #1d2327; font-weight: 600; box-shadow: 0 2px 8px rgba(56, 239, 125, 0.4); padding: 12px 30px; height: auto;">
                                    <span class="dashicons dashicons-download" style="margin-top: 4px;"></span>
                                    <?php _e('Carica Foto', 'my-event-plugin'); ?>
                                </button>
                            </div>
                            <details style="margin-top: 15px; background: rgba(255,255,255,0.15); padding: 12px; border-radius: 6px;">
                                <summary style="cursor: pointer; color: white; font-size: 13px; font-weight: 600; user-select: none;">
                                    ❓ Come ottenere l'ID della cartella?
                                </summary>
                                <ol style="margin: 12px 0 0 0; padding-left: 20px; font-size: 13px; color: rgba(255,255,255,0.95); line-height: 1.8;">
                                    <li>Apri <a href="https://drive.google.com" target="_blank" style="color: #38ef7d; font-weight: 600; text-decoration: underline;">Google Drive</a> nel browser</li>
                                    <li>Naviga nella cartella con le foto dell'evento</li>
                                    <li>Guarda l'URL nella barra degli indirizzi:<br>
                                        <code style="background: rgba(0,0,0,0.3); padding: 4px 8px; border-radius: 4px; display: inline-block; margin-top: 5px; font-size: 11px;">
                                            drive.google.com/drive/folders/<strong style="color: #38ef7d;">1a2b3c4d5e6f7g8h9i0j</strong>
                                        </code>
                                    </li>
                                    <li>Copia la parte finale dell'URL (dopo <code style="background: rgba(0,0,0,0.2); padding: 2px 4px;">/folders/</code>)</li>
                                    <li>Incollala nel campo sopra e clicca <strong>"Carica Foto"</strong></li>
                                </ol>
                                <div style="margin-top: 12px; padding: 10px; background: rgba(255, 193, 7, 0.2); border-left: 3px solid #ffc107; border-radius: 4px;">
                                    <p style="margin: 0; font-size: 12px; color: rgba(255,255,255,0.95);">
                                        <strong>💡 Suggerimento:</strong> Assicurati che l'account Use-your-Drive abbia accesso alla cartella, altrimenti vedrai un errore di permessi.
                                    </p>
                                </div>
                            </details>
                        </div>
                        
                        <div id="mep-folder-validation-message" class="mep-validation-message" style="display:none;"></div>
                        
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
