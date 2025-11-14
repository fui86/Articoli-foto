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
     * Importa foto da Google Drive nella Media Library WordPress
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
        
        MEP_Helpers::log_info("Inizio import di " . count($image_ids) . " foto");
        
        // 4. Importa ogni foto usando l'API wrapper di Use-your-Drive
        $attachment_ids = [];
        $errors = [];
        
        foreach ($image_ids as $index => $image_id) {
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
        
        // 5. Verifica risultati
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
