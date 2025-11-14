<?php
/**
 * Template: Pagina Impostazioni
 */

defined('ABSPATH') || exit;

// Salva impostazioni
if (isset($_POST['mep_save_settings']) && check_admin_referer('mep_settings_nonce', 'mep_settings_nonce_field')) {
    update_option('mep_template_post_id', absint($_POST['template_post_id']));
    update_option('mep_auto_featured_image', sanitize_text_field($_POST['auto_featured_image']));
    update_option('mep_gallery_responsive', sanitize_text_field($_POST['gallery_responsive']));
    update_option('mep_min_photos', absint($_POST['min_photos']));
    update_option('mep_auto_publish', sanitize_text_field($_POST['auto_publish']));
    
    echo '<div class="notice notice-success is-dismissible"><p>' . __('Impostazioni salvate!', 'my-event-plugin') . '</p></div>';
}

// Carica impostazioni correnti
$template_post_id = get_option('mep_template_post_id', 0);
$auto_featured_image = get_option('mep_auto_featured_image', 'yes');
$gallery_responsive = get_option('mep_gallery_responsive', 'yes');
$min_photos = get_option('mep_min_photos', 4);
$auto_publish = get_option('mep_auto_publish', 'no');
?>

<div class="wrap mep-settings-wrap">
    <h1 class="mep-page-title">
        <span class="dashicons dashicons-admin-settings"></span>
        <?php _e('Impostazioni Gestore Eventi', 'my-event-plugin'); ?>
    </h1>
    
    <form method="post" action="" class="mep-settings-form">
        <?php wp_nonce_field('mep_settings_nonce', 'mep_settings_nonce_field'); ?>
        
        <!-- Sezione Template -->
        <div class="mep-settings-section">
            <h2><?php _e('Template Post', 'my-event-plugin'); ?></h2>
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="template_post_id"><?php _e('ID Post Template', 'my-event-plugin'); ?></label>
                    </th>
                    <td>
                        <input type="number" 
                               id="template_post_id" 
                               name="template_post_id" 
                               value="<?php echo esc_attr($template_post_id); ?>" 
                               class="regular-text" 
                               min="0" 
                               required>
                        <p class="description">
                            <?php _e('ID del post da usare come template per i nuovi eventi. Tutti i contenuti e metadati verranno copiati.', 'my-event-plugin'); ?>
                            <br>
                            <?php if ($template_post_id > 0 && MEP_Helpers::is_valid_post($template_post_id)): ?>
                                <span style="color: #46b450;">✓ Post trovato: <strong><?php echo get_the_title($template_post_id); ?></strong></span>
                                <br><a href="<?php echo get_edit_post_link($template_post_id); ?>" target="_blank">Modifica Template →</a>
                            <?php else: ?>
                                <span style="color: #dc3232;">⚠ Post non trovato. Inserisci un ID valido.</span>
                            <?php endif; ?>
                        </p>
                    </td>
                </tr>
            </table>
        </div>
        
        <!-- Sezione Foto -->
        <div class="mep-settings-section">
            <h2><?php _e('Gestione Foto', 'my-event-plugin'); ?></h2>
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="min_photos"><?php _e('Minimo Foto Richieste', 'my-event-plugin'); ?></label>
                    </th>
                    <td>
                        <input type="number" 
                               id="min_photos" 
                               name="min_photos" 
                               value="<?php echo esc_attr($min_photos); ?>" 
                               min="1" 
                               max="20">
                        <p class="description">
                            <?php _e('Numero minimo di foto che deve contenere la cartella Google Drive', 'my-event-plugin'); ?>
                        </p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="auto_featured_image"><?php _e('Immagine in Evidenza Automatica', 'my-event-plugin'); ?></label>
                    </th>
                    <td>
                        <label>
                            <input type="radio" 
                                   name="auto_featured_image" 
                                   value="yes" 
                                   <?php checked($auto_featured_image, 'yes'); ?>>
                            <?php _e('Sì, imposta automaticamente la prima foto', 'my-event-plugin'); ?>
                        </label>
                        <br>
                        <label>
                            <input type="radio" 
                                   name="auto_featured_image" 
                                   value="no" 
                                   <?php checked($auto_featured_image, 'no'); ?>>
                            <?php _e('No, imposterò manualmente', 'my-event-plugin'); ?>
                        </label>
                    </td>
                </tr>
            </table>
        </div>
        
        <!-- Sezione Galleria -->
        <div class="mep-settings-section">
            <h2><?php _e('Galleria', 'my-event-plugin'); ?></h2>
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="gallery_responsive"><?php _e('CSS Responsive Personalizzato', 'my-event-plugin'); ?></label>
                    </th>
                    <td>
                        <label>
                            <input type="radio" 
                                   name="gallery_responsive" 
                                   value="yes" 
                                   <?php checked($gallery_responsive, 'yes'); ?>>
                            <?php _e('Sì, applica CSS responsive custom', 'my-event-plugin'); ?>
                        </label>
                        <br>
                        <label>
                            <input type="radio" 
                                   name="gallery_responsive" 
                                   value="no" 
                                   <?php checked($gallery_responsive, 'no'); ?>>
                            <?php _e('No, usa stile default di Use-your-Drive', 'my-event-plugin'); ?>
                        </label>
                        <p class="description">
                            <?php _e('Il CSS personalizzato rende la galleria completamente responsive su mobile', 'my-event-plugin'); ?>
                        </p>
                    </td>
                </tr>
            </table>
        </div>
        
        <!-- Sezione Pubblicazione -->
        <div class="mep-settings-section">
            <h2><?php _e('Pubblicazione', 'my-event-plugin'); ?></h2>
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="auto_publish"><?php _e('Pubblicazione Automatica', 'my-event-plugin'); ?></label>
                    </th>
                    <td>
                        <label>
                            <input type="radio" 
                                   name="auto_publish" 
                                   value="yes" 
                                   <?php checked($auto_publish, 'yes'); ?>>
                            <?php _e('Sì, pubblica subito l\'evento', 'my-event-plugin'); ?>
                        </label>
                        <br>
                        <label>
                            <input type="radio" 
                                   name="auto_publish" 
                                   value="no" 
                                   <?php checked($auto_publish, 'no'); ?>>
                            <?php _e('No, crea come bozza (consigliato)', 'my-event-plugin'); ?>
                        </label>
                        <p class="description">
                            <?php _e('Se impostato su "bozza", potrai revisionare l\'evento prima di pubblicarlo', 'my-event-plugin'); ?>
                        </p>
                    </td>
                </tr>
            </table>
        </div>
        
        <!-- Info Use-your-Drive -->
        <div class="mep-settings-section">
            <h2><?php _e('Informazioni Use-your-Drive', 'my-event-plugin'); ?></h2>
            <?php
            $uyd_check = MEP_Helpers::check_useyourdrive_ready();
            if (is_wp_error($uyd_check)):
            ?>
                <div class="notice notice-error inline">
                    <p><strong><?php _e('Attenzione:', 'my-event-plugin'); ?></strong> <?php echo esc_html($uyd_check->get_error_message()); ?></p>
                </div>
            <?php else: ?>
                <div class="notice notice-success inline">
                    <p>✓ <?php _e('Use-your-Drive è correttamente configurato', 'my-event-plugin'); ?></p>
                </div>
                
                <?php
                $accounts = \TheLion\UseyourDrive\Accounts::instance()->list_accounts();
                if (!empty($accounts)):
                ?>
                    <h3><?php _e('Account Google Drive Connessi:', 'my-event-plugin'); ?></h3>
                    <ul>
                        <?php foreach ($accounts as $account): ?>
                            <li>
                                <strong><?php echo esc_html($account->get_email()); ?></strong>
                                <?php if ($account->get_authorization()->has_access_token()): ?>
                                    <span style="color: #46b450;">✓ Autorizzato</span>
                                <?php else: ?>
                                    <span style="color: #dc3232;">⚠ Non autorizzato</span>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            <?php endif; ?>
            
            <p>
                <a href="<?php echo admin_url('admin.php?page=use_your_drive_settings'); ?>" class="button">
                    <?php _e('Vai alle Impostazioni Use-your-Drive', 'my-event-plugin'); ?>
                </a>
            </p>
        </div>
        
        <p class="submit">
            <input type="submit" 
                   name="mep_save_settings" 
                   class="button button-primary" 
                   value="<?php esc_attr_e('Salva Impostazioni', 'my-event-plugin'); ?>">
        </p>
    </form>
</div>
