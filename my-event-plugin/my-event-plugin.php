<?php
/**
 * Plugin Name: Gestore Eventi Automatico
 * Plugin URI: https://tuosito.it
 * Description: Crea automaticamente articoli per eventi con foto da Google Drive usando Use-your-Drive
 * Version: 1.0.0
 * Author: Il Tuo Nome
 * Author URI: https://tuosito.it
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * Requires Plugins: use-your-drive
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: my-event-plugin
 * Domain Path: /languages
 */

defined('ABSPATH') || exit;

// Definisci costanti
define('MEP_VERSION', '1.0.0');
define('MEP_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('MEP_PLUGIN_URL', plugin_dir_url(__FILE__));
define('MEP_PLUGIN_FILE', __FILE__);

/**
 * Classe principale del plugin
 */
class My_Event_Plugin {
    
    /**
     * @var My_Event_Plugin Singleton instance
     */
    private static $instance = null;
    
    /**
     * ID del post template da clonare
     * MODIFICA QUESTO VALORE con l'ID del tuo post template
     */
    private $template_post_id = 0; // ⚠️ INSERISCI QUI L'ID DEL TUO TEMPLATE!
    
    /**
     * Get singleton instance
     */
    public static function instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        // Carica le classi
        $this->load_dependencies();
        
        // Verifica dipendenze
        add_action('admin_init', [$this, 'check_dependencies']);
        
        // Menu admin
        add_action('admin_menu', [$this, 'add_admin_menu']);
        
        // Enqueue assets
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        
        // AJAX handlers
        add_action('wp_ajax_mep_process_event_creation', [$this, 'handle_event_creation']);
        add_action('wp_ajax_mep_validate_folder', [$this, 'handle_folder_validation']);
        add_action('wp_ajax_mep_get_folder_photos', [$this, 'handle_get_folder_photos']);
        add_action('wp_ajax_mep_get_template_preview', [$this, 'handle_get_template_preview']);
        
        // Shortcode per frontend (opzionale)
        add_shortcode('my_event_form', [$this, 'render_frontend_form']);
        
        // Hook per tracciare import
        add_action('useyourdrive_imported_entry', [$this, 'track_imported_file'], 10, 2);
        
        // Enqueue CSS responsive galleria nel frontend
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_styles']);
    }
    
    /**
     * Carica CSS responsive per la galleria nel frontend
     */
    public function enqueue_frontend_styles() {
        // Carica solo se l'opzione è attiva
        if (get_option('mep_gallery_responsive', 'yes') === 'yes') {
            wp_enqueue_style(
                'mep-gallery-responsive',
                MEP_PLUGIN_URL . 'assets/css/gallery-responsive.css',
                [],
                MEP_VERSION
            );
        }
    }
    
    /**
     * Carica le dipendenze
     */
    private function load_dependencies() {
        require_once MEP_PLUGIN_DIR . 'includes/class-helpers.php';
        require_once MEP_PLUGIN_DIR . 'includes/class-post-creator.php';
        require_once MEP_PLUGIN_DIR . 'includes/class-gdrive-integration.php';
    }
    
    /**
     * Verifica che Use-your-Drive sia installato e attivo
     */
    public function check_dependencies() {
        if (!class_exists('TheLion\UseyourDrive\Core')) {
            add_action('admin_notices', [$this, 'dependency_notice']);
            deactivate_plugins(plugin_basename(__FILE__));
            return false;
        }
        
        // Verifica che ci sia almeno un account connesso
        $check = MEP_Helpers::check_useyourdrive_ready();
        if (is_wp_error($check)) {
            add_action('admin_notices', function() use ($check) {
                echo '<div class="notice notice-warning"><p>';
                echo '<strong>Gestore Eventi Automatico:</strong> ' . esc_html($check->get_error_message());
                echo '</p></div>';
            });
        }
        
        return true;
    }
    
    /**
     * Notice per dipendenze mancanti
     */
    public function dependency_notice() {
        echo '<div class="notice notice-error"><p>';
        echo '<strong>Gestore Eventi Automatico</strong> richiede il plugin ';
        echo '<strong><a href="https://www.wpcloudplugins.com/" target="_blank">Use-your-Drive</a></strong> per funzionare.';
        echo '</p></div>';
    }
    
    /**
     * Aggiungi menu nell'admin
     */
    public function add_admin_menu() {
        add_menu_page(
            __('Crea Evento', 'my-event-plugin'),
            __('Eventi Auto', 'my-event-plugin'),
            'publish_posts',
            'my-event-creator',
            [$this, 'render_admin_page'],
            'dashicons-calendar-alt',
            25
        );
        
        // Sottomenu impostazioni
        add_submenu_page(
            'my-event-creator',
            __('Impostazioni', 'my-event-plugin'),
            __('Impostazioni', 'my-event-plugin'),
            'manage_options',
            'my-event-settings',
            [$this, 'render_settings_page']
        );
    }
    
    /**
     * Carica CSS e JS nell'admin
     */
    public function enqueue_admin_assets($hook) {
        // Carica solo nella pagina del plugin
        if (!in_array($hook, ['toplevel_page_my-event-creator', 'eventi-auto_page_my-event-settings'])) {
            return;
        }
        
        // CSS
        wp_enqueue_style(
            'mep-admin-style',
            MEP_PLUGIN_URL . 'assets/css/admin-style.css',
            [],
            MEP_VERSION
        );
        
        // JS
        wp_enqueue_script(
            'mep-admin-script',
            MEP_PLUGIN_URL . 'assets/js/admin-script.js',
            ['jquery'],
            MEP_VERSION,
            true
        );
        
        // Localizza script
        wp_localize_script('mep-admin-script', 'mepData', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('mep_nonce'),
            'strings' => [
                'processing' => __('Creazione in corso...', 'my-event-plugin'),
                'success' => __('Evento creato con successo!', 'my-event-plugin'),
                'error' => __('Errore durante la creazione', 'my-event-plugin'),
                'select_folder' => __('Seleziona una cartella Google Drive!', 'my-event-plugin'),
                'validating' => __('Validazione cartella in corso...', 'my-event-plugin'),
                'folder_valid' => __('Cartella valida!', 'my-event-plugin'),
                'folder_invalid' => __('Cartella non valida', 'my-event-plugin'),
            ]
        ]);
    }
    
    /**
     * Renderizza la pagina admin principale
     */
    public function render_admin_page() {
        if (!current_user_can('publish_posts')) {
            wp_die(__('Non hai i permessi per accedere a questa pagina.'));
        }
        
        require_once MEP_PLUGIN_DIR . 'templates/admin-page.php';
    }
    
    /**
     * Renderizza la pagina impostazioni
     */
    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Non hai i permessi per accedere a questa pagina.'));
        }
        
        require_once MEP_PLUGIN_DIR . 'templates/settings-page.php';
    }
    
    /**
     * Handler AJAX per creare l'evento
     */
    public function handle_event_creation() {
        check_ajax_referer('mep_nonce', 'nonce');
        
        if (!current_user_can('publish_posts')) {
            wp_send_json_error(['message' => __('Permessi insufficienti', 'my-event-plugin')]);
        }
        
        // Valida template ID
        if (empty($this->template_post_id) || $this->template_post_id === 0) {
            wp_send_json_error([
                'message' => __('ID template post non configurato! Vai in Impostazioni.', 'my-event-plugin')
            ]);
        }
        
        try {
            $creator = new MEP_Post_Creator($this->template_post_id);
            $result = $creator->create_event_post($_POST);
            
            if (is_wp_error($result)) {
                wp_send_json_error(['message' => $result->get_error_message()]);
            }
            
            // Il risultato ora è un array con post_id, photo_urls, ecc.
            $post_id = $result['post_id'];
            
            wp_send_json_success([
                'post_id' => $post_id,
                'edit_url' => get_edit_post_link($post_id, 'raw'),
                'view_url' => get_permalink($post_id),
                'photo_urls' => $result['photo_urls'],
                'attachment_ids' => $result['attachment_ids'],
                'featured_index' => $result['featured_index']
            ]);
            
        } catch (Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }
    
    /**
     * Handler AJAX per validare una cartella
     */
    public function handle_folder_validation() {
        check_ajax_referer('mep_nonce', 'nonce');
        
        $folder_id = sanitize_text_field($_POST['folder_id'] ?? '');
        
        if (empty($folder_id)) {
            wp_send_json_error(['message' => __('ID cartella mancante', 'my-event-plugin')]);
        }
        
        $validation = MEP_Helpers::validate_folder_id($folder_id);
        
        if (is_wp_error($validation)) {
            wp_send_json_error(['message' => $validation->get_error_message()]);
        }
        
        // Conta le immagini
        $image_count = MEP_GDrive_Integration::count_images_in_folder($folder_id);
        
        wp_send_json_success([
            'valid' => true,
            'image_count' => $image_count,
            'message' => sprintf(
                __('Cartella valida! Contiene %d immagini.', 'my-event-plugin'),
                $image_count
            )
        ]);
    }
    
    /**
     * Handler AJAX per recuperare le foto da una cartella
     */
    public function handle_get_folder_photos() {
        check_ajax_referer('mep_nonce', 'nonce');
        
        $folder_id = sanitize_text_field($_POST['folder_id'] ?? '');
        
        if (empty($folder_id)) {
            wp_send_json_error(['message' => __('ID cartella mancante', 'my-event-plugin')]);
        }
        
        // Ottieni la lista delle foto con thumbnail
        $photos = MEP_GDrive_Integration::get_photos_list_with_thumbnails($folder_id);
        
        if (is_wp_error($photos)) {
            wp_send_json_error(['message' => $photos->get_error_message()]);
        }
        
        if (empty($photos)) {
            wp_send_json_error(['message' => __('Nessuna foto trovata nella cartella', 'my-event-plugin')]);
        }
        
        wp_send_json_success([
            'photos' => $photos,
            'count' => count($photos)
        ]);
    }
    
    /**
     * Handler AJAX per ottenere anteprima template
     */
    public function handle_get_template_preview() {
        check_ajax_referer('mep_settings_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permessi insufficienti', 'my-event-plugin')]);
        }
        
        $post_id = absint($_POST['post_id'] ?? 0);
        
        if (empty($post_id)) {
            wp_send_json_error(['message' => __('ID post mancante', 'my-event-plugin')]);
        }
        
        $post = get_post($post_id);
        
        if (!$post) {
            wp_send_json_error(['message' => __('Post non trovato', 'my-event-plugin')]);
        }
        
        // Genera HTML anteprima
        $categories = get_the_category($post_id);
        $cat_names = !empty($categories) ? implode(', ', wp_list_pluck($categories, 'name')) : __('Nessuna categoria', 'my-event-plugin');
        
        $status_labels = [
            'publish' => __('Pubblicato', 'my-event-plugin'),
            'draft' => __('Bozza', 'my-event-plugin'),
            'pending' => __('In attesa di revisione', 'my-event-plugin'),
            'private' => __('Privato', 'my-event-plugin')
        ];
        $status = isset($status_labels[$post->post_status]) ? $status_labels[$post->post_status] : $post->post_status;
        
        ob_start();
        ?>
        <h4 style="margin-top: 0; color: #2271b1;">
            <span class="dashicons dashicons-yes-alt" style="color: #00a32a;"></span>
            <?php _e('Template Selezionato:', 'my-event-plugin'); ?>
        </h4>
        <p style="margin: 10px 0;">
            <strong><?php _e('Titolo:', 'my-event-plugin'); ?></strong> 
            <?php echo esc_html($post->post_title); ?>
        </p>
        <p style="margin: 10px 0;">
            <strong><?php _e('ID:', 'my-event-plugin'); ?></strong> 
            <?php echo $post_id; ?>
        </p>
        <p style="margin: 10px 0;">
            <strong><?php _e('Categorie:', 'my-event-plugin'); ?></strong> 
            <?php echo esc_html($cat_names); ?>
        </p>
        <p style="margin: 10px 0;">
            <strong><?php _e('Stato:', 'my-event-plugin'); ?></strong> 
            <?php echo esc_html($status); ?>
        </p>
        <p style="margin: 10px 0;">
            <strong><?php _e('Data pubblicazione:', 'my-event-plugin'); ?></strong> 
            <?php echo date_i18n(get_option('date_format'), strtotime($post->post_date)); ?>
        </p>
        <p style="margin: 10px 0 0 0;">
            <a href="<?php echo get_edit_post_link($post_id); ?>" target="_blank" class="button button-secondary">
                <span class="dashicons dashicons-edit" style="margin-top: 3px;"></span>
                <?php _e('Modifica Template', 'my-event-plugin'); ?>
            </a>
            <a href="<?php echo get_permalink($post_id); ?>" target="_blank" class="button button-secondary">
                <span class="dashicons dashicons-visibility" style="margin-top: 3px;"></span>
                <?php _e('Visualizza', 'my-event-plugin'); ?>
            </a>
        </p>
        <?php
        $html = ob_get_clean();
        
        wp_send_json_success(['html' => $html]);
    }
    
    /**
     * Traccia i file importati dal plugin
     */
    public function track_imported_file($attachment_id, $cached_node) {
        // Aggiungi metadata per identificare che viene dal nostro plugin
        update_post_meta($attachment_id, '_imported_by_event_plugin', true);
        update_post_meta($attachment_id, '_gdrive_file_id', $cached_node->get_id());
        update_post_meta($attachment_id, '_gdrive_file_name', $cached_node->get_name());
        update_post_meta($attachment_id, '_import_date', current_time('mysql'));
    }
    
    /**
     * Shortcode per form frontend (opzionale)
     */
    public function render_frontend_form($atts) {
        if (!is_user_logged_in() || !current_user_can('publish_posts')) {
            return '<p>' . __('Devi essere loggato per usare questa funzione.', 'my-event-plugin') . '</p>';
        }
        
        ob_start();
        require MEP_PLUGIN_DIR . 'templates/frontend-form.php';
        return ob_get_clean();
    }
    
    /**
     * Get template post ID
     */
    public function get_template_post_id() {
        // Prova a prendere dalle opzioni
        $saved_id = get_option('mep_template_post_id', 0);
        return !empty($saved_id) ? $saved_id : $this->template_post_id;
    }
}

/**
 * Inizializza il plugin
 */
function mep_init() {
    return My_Event_Plugin::instance();
}

// Avvia il plugin
add_action('plugins_loaded', 'mep_init');

/**
 * Activation hook
 */
register_activation_hook(__FILE__, function() {
    // Crea opzioni di default
    add_option('mep_version', MEP_VERSION);
    add_option('mep_template_post_id', 0);
    add_option('mep_auto_featured_image', 'yes');
    add_option('mep_gallery_responsive', 'yes');
    add_option('mep_min_photos', 4);
});

/**
 * Deactivation hook
 */
register_deactivation_hook(__FILE__, function() {
    // Cleanup se necessario
});
