<?php
/**
 * Classe GDrive Integration - Gestisce l'integrazione con Google Drive via Use-your-Drive
 */

defined('ABSPATH') || exit;

class MEP_GDrive_Integration {
    
    /**
     * Ottieni lista di ID immagini da una cartella Google Drive
     * 
     * @param string $folder_id ID cartella Google Drive
     * @return array Array di file IDs
     */
    public static function get_image_ids_from_folder($folder_id) {
        if (empty($folder_id)) {
            return [];
        }
        
        try {
            $folder = \TheLion\UseyourDrive\Client::instance()->get_folder($folder_id);
            
            if (empty($folder['contents'])) {
                MEP_Helpers::log_info("Cartella {$folder_id} vuota o non accessibile");
                return [];
            }
            
            $image_mimetypes = [
                'image/jpeg',
                'image/jpg',
                'image/png',
                'image/gif',
                'image/webp',
                'image/bmp'
            ];
            
            $image_ids = [];
            foreach ($folder['contents'] as $cached_node) {
                // Verifica che sia un file (non una cartella)
                if (!$cached_node->get_entry()->is_file()) {
                    continue;
                }
                
                $mimetype = $cached_node->get_entry()->get_mimetype();
                
                if (in_array($mimetype, $image_mimetypes)) {
                    $image_ids[] = $cached_node->get_id();
                }
            }
            
            MEP_Helpers::log_info("Trovate " . count($image_ids) . " immagini nella cartella {$folder_id}");
            
            return $image_ids;
            
        } catch (Exception $e) {
            MEP_Helpers::log_error("Errore nel recupero immagini dalla cartella {$folder_id}", $e->getMessage());
            return [];
        }
    }
    
    /**
     * Ottieni lista foto con thumbnails per la griglia di selezione
     * 
     * @param string $folder_id ID cartella Google Drive
     * @return array|WP_Error Array di foto con info e thumbnail
     */
    public static function get_photos_list_with_thumbnails($folder_id) {
        if (empty($folder_id)) {
            return new WP_Error('empty_folder_id', __('ID cartella vuoto', 'my-event-plugin'));
        }
        
        try {
            // Log per debug
            MEP_Helpers::log_info("Tentativo recupero foto da cartella: {$folder_id}");
            
            // Verifica che il client sia disponibile
            if (!class_exists('\TheLion\UseyourDrive\Client')) {
                return new WP_Error('client_not_found', __('Client Use-your-Drive non disponibile', 'my-event-plugin'));
            }
            
            $client = \TheLion\UseyourDrive\Client::instance();
            $folder = $client->get_folder($folder_id);
            
            if (empty($folder)) {
                MEP_Helpers::log_error("Cartella vuota o non trovata", $folder_id);
                return new WP_Error('folder_not_found', __('Cartella non trovata', 'my-event-plugin'));
            }
            
            if (empty($folder['contents'])) {
                MEP_Helpers::log_info("Cartella {$folder_id} non contiene file");
                return []; // Ritorna array vuoto invece di errore
            }
            
            $image_mimetypes = [
                'image/jpeg',
                'image/jpg',
                'image/png',
                'image/gif',
                'image/webp',
                'image/bmp'
            ];
            
            $photos = [];
            
            foreach ($folder['contents'] as $cached_node) {
                try {
                    // Verifica che sia un file (non una cartella)
                    $entry = $cached_node->get_entry();
                    
                    if (!$entry->is_file()) {
                        continue;
                    }
                    
                    $mimetype = $entry->get_mimetype();
                    
                    if (in_array($mimetype, $image_mimetypes)) {
                        // Ottieni thumbnail URL - metodo più sicuro
                        $thumbnail_url = '';
                        
                        // Prova diversi metodi per ottenere il thumbnail
                        if (method_exists($entry, 'get_thumbnail_with_size')) {
                            $thumbnail_url = $entry->get_thumbnail_with_size('medium');
                        }
                        
                        if (empty($thumbnail_url) && method_exists($entry, 'get_thumbnail')) {
                            $thumbnail_url = $entry->get_thumbnail();
                        }
                        
                        if (empty($thumbnail_url) && method_exists($entry, 'get_icon')) {
                            $thumbnail_url = $entry->get_icon();
                        }
                        
                        // Fallback: usa l'URL diretto del file
                        if (empty($thumbnail_url) && method_exists($entry, 'get_preview_link')) {
                            $thumbnail_url = $entry->get_preview_link();
                        }
                        
                        $photos[] = [
                            'id' => $cached_node->get_id(),
                            'name' => $entry->get_name(),
                            'thumbnail' => $thumbnail_url ?: 'https://via.placeholder.com/200x200?text=No+Preview',
                            'size' => method_exists($entry, 'get_size') ? $entry->get_size() : 0,
                            'mimetype' => $mimetype
                        ];
                    }
                } catch (Exception $inner_e) {
                    // Log ma continua con le altre foto
                    MEP_Helpers::log_error("Errore processamento singola foto", $inner_e->getMessage());
                    continue;
                }
            }
            
            MEP_Helpers::log_info("Trovate " . count($photos) . " foto nella cartella {$folder_id}");
            
            return $photos;
            
        } catch (Exception $e) {
            MEP_Helpers::log_error("Errore nel recupero foto dalla cartella {$folder_id}", $e->getMessage());
            MEP_Helpers::log_error("Stack trace", $e->getTraceAsString());
            return new WP_Error('api_error', sprintf(
                __('Errore API Use-your-Drive: %s', 'my-event-plugin'),
                $e->getMessage()
            ));
        }
    }
    
    /**
     * Importa foto specifiche da Google Drive nella Media Library WordPress
     * 
     * @param array $photo_ids Array di ID foto da importare
     * @return array|WP_Error Array di attachment IDs o WP_Error
     */
    public static function import_specific_photos($photo_ids) {
        if (empty($photo_ids) || !is_array($photo_ids)) {
            return new WP_Error('empty_photo_ids', __('Lista foto vuota', 'my-event-plugin'));
        }
        
        MEP_Helpers::log_info("Inizio import di " . count($photo_ids) . " foto selezionate");
        
        // Importa ogni foto usando l'API wrapper di Use-your-Drive
        $attachment_ids = [];
        $errors = [];
        
        foreach ($photo_ids as $index => $image_id) {
            try {
                // ⭐ QUESTA È LA MAGIA: una riga = download + import completo!
                $attachment_id = \TheLion\UseyourDrive\API::import($image_id);
                
                if (is_wp_error($attachment_id)) {
                    $errors[] = $attachment_id->get_error_message();
                    MEP_Helpers::log_error("Errore import foto {$image_id}", $attachment_id->get_error_message());
                    continue;
                }
                
                $attachment_ids[] = $attachment_id;
                MEP_Helpers::log_info("Foto {$image_id} importata con attachment ID {$attachment_id}");
                
            } catch (Exception $e) {
                $errors[] = $e->getMessage();
                MEP_Helpers::log_error("Eccezione durante import foto {$image_id}", $e->getMessage());
            }
        }
        
        // Verifica risultati
        if (empty($attachment_ids)) {
            return new WP_Error(
                'import_failed',
                __('Impossibile importare alcuna foto. Errori: ', 'my-event-plugin') . implode(', ', $errors)
            );
        }
        
        if (!empty($errors)) {
            MEP_Helpers::log_error("Alcuni errori durante l'import", $errors);
        }
        
        MEP_Helpers::log_info("Import completato: " . count($attachment_ids) . " foto importate");
        
        return $attachment_ids;
    }
    
    /**
     * Importa foto da Google Drive nella Media Library WordPress (metodo legacy)
     * 
     * @param string $folder_id ID cartella Google Drive
     * @param int $limit Numero massimo di foto da importare (default 4)
     * @return array|WP_Error Array di attachment IDs o WP_Error
     */
    public static function import_photos_from_folder($folder_id, $limit = 4) {
        if (empty($folder_id)) {
            return new WP_Error('empty_folder_id', __('ID cartella vuoto', 'my-event-plugin'));
        }
        
        // 1. Ottieni lista immagini
        $image_ids = self::get_image_ids_from_folder($folder_id);
        
        if (empty($image_ids)) {
            return new WP_Error(
                'no_images',
                __('Nessuna immagine trovata nella cartella selezionata.', 'my-event-plugin')
            );
        }
        
        // 2. Verifica numero minimo immagini
        $min_photos = get_option('mep_min_photos', 4);
        if (count($image_ids) < $min_photos) {
            return new WP_Error(
                'insufficient_images',
                sprintf(
                    __('La cartella contiene solo %d immagini. Servono almeno %d foto.', 'my-event-plugin'),
                    count($image_ids),
                    $min_photos
                )
            );
        }
        
        // 3. Limita al numero richiesto
        $image_ids = array_slice($image_ids, 0, $limit);
        
        // 4. Usa il nuovo metodo per importare
        return self::import_specific_photos($image_ids);
    }
    
    /**
     * Conta il numero di immagini in una cartella
     * 
     * @param string $folder_id
     * @return int
     */
    public static function count_images_in_folder($folder_id) {
        $image_ids = self::get_image_ids_from_folder($folder_id);
        return count($image_ids);
    }
    
    /**
     * Ottieni dettagli di una cartella
     * 
     * @param string $folder_id
     * @return array|false
     */
    public static function get_folder_details($folder_id) {
        try {
            $folder = \TheLion\UseyourDrive\Client::instance()->get_folder($folder_id);
            
            if (empty($folder)) {
                return false;
            }
            
            $image_count = self::count_images_in_folder($folder_id);
            $total_files = count($folder['contents']);
            
            return [
                'id' => $folder['folder']->get_id(),
                'name' => $folder['folder']->get_name(),
                'path' => $folder['folder']->get_path('root'),
                'total_files' => $total_files,
                'image_count' => $image_count,
                'has_enough_images' => $image_count >= get_option('mep_min_photos', 4)
            ];
            
        } catch (Exception $e) {
            MEP_Helpers::log_error("Errore recupero dettagli cartella {$folder_id}", $e->getMessage());
            return false;
        }
    }
    
    /**
     * Crea shortcode galleria Use-your-Drive
     * 
     * @param string $folder_id
     * @param array $custom_params Parametri personalizzati
     * @return string
     */
    public static function create_gallery_shortcode($folder_id, $custom_params = []) {
        $default_params = [
            'dir' => $folder_id,
            'mode' => 'gallery',
            'maxheight' => '500px',
            'targetheight' => '200',
            'sortfield' => 'name',
            'include_ext' => 'jpg,jpeg,png,gif,webp',
            'showfilenames' => '0',
            'lightbox' => '1',
            'class' => 'mep-gallery-responsive'
        ];
        
        $params = array_merge($default_params, $custom_params);
        
        $shortcode = '[useyourdrive';
        foreach ($params as $key => $value) {
            $shortcode .= ' ' . $key . '="' . esc_attr($value) . '"';
        }
        $shortcode .= ']';
        
        return $shortcode;
    }
    
    /**
     * Verifica se una cartella è accessibile
     * 
     * @param string $folder_id
     * @return bool
     */
    public static function is_folder_accessible($folder_id) {
        try {
            $folder = \TheLion\UseyourDrive\Client::instance()->get_folder($folder_id);
            return !empty($folder);
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Ottieni il primo account Google Drive disponibile
     * 
     * @return object|false
     */
    public static function get_primary_account() {
        try {
            $accounts = \TheLion\UseyourDrive\Accounts::instance()->list_accounts();
            
            if (empty($accounts)) {
                return false;
            }
            
            // Ritorna il primo account con token valido
            foreach ($accounts as $account) {
                if ($account->get_authorization()->has_access_token()) {
                    return $account;
                }
            }
            
            return false;
            
        } catch (Exception $e) {
            MEP_Helpers::log_error("Errore recupero account primario", $e->getMessage());
            return false;
        }
    }
}
