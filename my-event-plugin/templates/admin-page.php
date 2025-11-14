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
                    <h2 class="mep-section-title">
                        <span class="dashicons dashicons-cloud"></span>
                        <?php _e('Foto da Google Drive', 'my-event-plugin'); ?>
                    </h2>
                    
                    <div class="mep-form-row">
                        <label class="mep-label required">
                            <?php _e('Seleziona Cartella con le Foto', 'my-event-plugin'); ?>
                        </label>
                        <p class="mep-description">
                            <?php _e('Naviga il tuo Google Drive e seleziona la cartella contenente le foto dell\'evento', 'my-event-plugin'); ?>
                        </p>
                        
                        <div id="mep-folder-validation-message" class="mep-validation-message" style="display:none;"></div>
                        
                        <!-- Use-your-Drive Folder Selector -->
                        <div class="mep-folder-selector-container">
                            <?php
                            if (class_exists('TheLion\UseyourDrive\AdminLayout')) {
                                \TheLion\UseyourDrive\AdminLayout::render_folder_selectbox([
                                    'key' => 'event_folder',
                                    'title' => '',
                                    'description' => '',
                                    'shortcode_attr' => [
                                        'mode' => 'files',
                                        'filelayout' => 'list',
                                        'maxheight' => '400px',
                                        'showfiles' => '1',
                                        'hoverthumbs' => '1',
                                        'filesize' => '1',
                                        'filedate' => '1',
                                        'search' => '1',
                                        'dir' => 'drive',
                                    ]
                                ]);
                            } else {
                                echo '<p class="mep-error">' . __('Use-your-Drive non è disponibile.', 'my-event-plugin') . '</p>';
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
                        <hr style="margin: 30px 0; border: 0; border-top: 1px solid #dcdcde;">
                        
                        <div class="mep-form-row">
                            <label class="mep-label required">
                                <span class="dashicons dashicons-images-alt2"></span>
                                <?php _e('Seleziona 4 Foto per l\'Articolo', 'my-event-plugin'); ?>
                            </label>
                            <p class="mep-description">
                                <?php _e('Clicca su 4 foto dalla griglia sottostante. Poi scegli quale usare come immagine di copertina.', 'my-event-plugin'); ?>
                            </p>
                            
                            <div id="mep-selection-info" class="mep-selection-info">
                                <span class="mep-selection-count"><?php _e('Foto selezionate:', 'my-event-plugin'); ?> <strong>0/4</strong></span>
                            </div>
                            
                            <!-- Griglia Foto -->
                            <div id="mep-photo-grid" class="mep-photo-grid">
                                <div class="mep-loading-grid">
                                    <span class="mep-spinner"></span>
                                    <p><?php _e('Caricamento foto...', 'my-event-plugin'); ?></p>
                                </div>
                            </div>
                            
                            <!-- Foto Selezionate -->
                            <div id="mep-selected-photos" class="mep-selected-photos" style="display:none;">
                                <h3><?php _e('Foto Selezionate:', 'my-event-plugin'); ?></h3>
                                <div id="mep-selected-photos-list" class="mep-selected-photos-list"></div>
                                
                                <div class="mep-featured-image-selector" style="margin-top: 20px;">
                                    <label class="mep-label required">
                                        <span class="dashicons dashicons-format-image"></span>
                                        <?php _e('Quale foto usare come copertina?', 'my-event-plugin'); ?>
                                    </label>
                                    <select id="mep-featured-image-select" name="featured_image_index" class="mep-select" required>
                                        <option value=""><?php _e('-- Seleziona immagine di copertina --', 'my-event-plugin'); ?></option>
                                        <option value="0"><?php _e('Foto 1', 'my-event-plugin'); ?></option>
                                        <option value="1"><?php _e('Foto 2', 'my-event-plugin'); ?></option>
                                        <option value="2"><?php _e('Foto 3', 'my-event-plugin'); ?></option>
                                        <option value="3"><?php _e('Foto 4', 'my-event-plugin'); ?></option>
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
