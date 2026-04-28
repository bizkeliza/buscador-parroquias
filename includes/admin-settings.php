<?php
if (!defined('ABSPATH')) exit;

function bp_get_settings() {
    $defaults = [
        'org_name'        => '',
        'logo_url'        => '',
        'logo_link'       => '',
        'header_title'    => 'Guía de parroquias y horarios de misa',
        'header_subtitle' => 'Localiza iglesias, horarios de misa y datos de contacto.',
        'footer_org'      => '',
        'footer_line1'    => '',
        'footer_address'  => '',
        'footer_city'     => '',
        'footer_email'    => '',
        'radio_km'        => 2,
    ];
    return wp_parse_args(get_option(BP_OPTION_KEY, []), $defaults);
}

add_action('admin_menu', 'bp_admin_menu');
function bp_admin_menu() {
    add_submenu_page(
        'edit.php?post_type=' . BP_CPT,
        __('Configuración', 'buscador-parroquias'),
        __('Configuración', 'buscador-parroquias'),
        'manage_options',
        'bp-settings',
        'bp_settings_page'
    );
    add_submenu_page(
        'edit.php?post_type=' . BP_CPT,
        __('Importar Excel', 'buscador-parroquias'),
        __('↑ Importar Excel', 'buscador-parroquias'),
        'manage_options',
        'bp-importar',
        'bp_admin_import_page'
    );
}

add_action('admin_init', 'bp_register_settings');
function bp_register_settings() {
    register_setting(BP_OPTION_KEY, BP_OPTION_KEY, [
        'sanitize_callback' => 'bp_sanitize_settings',
    ]);
}

function bp_sanitize_settings($input) {
    $clean = [];
    $text_fields = [
        'org_name', 'logo_url', 'logo_link', 'header_title', 'header_subtitle',
        'footer_org', 'footer_line1', 'footer_address', 'footer_city', 'footer_email',
    ];
    foreach ($text_fields as $field) {
        $clean[$field] = sanitize_text_field($input[$field] ?? '');
    }
    $clean['radio_km'] = max(1, min(100, intval($input['radio_km'] ?? 2)));
    return $clean;
}

function bp_settings_page() {
    if (!current_user_can('manage_options')) return;
    $s = bp_get_settings();
    ?>
    <div class="wrap">
        <h1><?php _e('Configuración — Buscador de Parroquias', 'buscador-parroquias'); ?></h1>

        <?php settings_errors(BP_OPTION_KEY); ?>

        <form method="post" action="options.php">
            <?php settings_fields(BP_OPTION_KEY); ?>

            <h2><?php _e('Identidad', 'buscador-parroquias'); ?></h2>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><?php _e('Nombre de la organización', 'buscador-parroquias'); ?></th>
                    <td>
                        <input type="text" name="<?php echo BP_OPTION_KEY; ?>[org_name]"
                               value="<?php echo esc_attr($s['org_name']); ?>" class="regular-text">
                        <p class="description"><?php _e('Ej: Diócesis de Bilbao — aparece en la cabecera del buscador.', 'buscador-parroquias'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('URL del logotipo', 'buscador-parroquias'); ?></th>
                    <td>
                        <input type="url" name="<?php echo BP_OPTION_KEY; ?>[logo_url]"
                               value="<?php echo esc_attr($s['logo_url']); ?>" class="regular-text"
                               placeholder="https://...">
                        <p class="description"><?php _e('Deja en blanco para ocultar el logo.', 'buscador-parroquias'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Enlace del logotipo', 'buscador-parroquias'); ?></th>
                    <td>
                        <input type="url" name="<?php echo BP_OPTION_KEY; ?>[logo_link]"
                               value="<?php echo esc_attr($s['logo_link']); ?>" class="regular-text"
                               placeholder="https://...">
                    </td>
                </tr>
            </table>

            <h2><?php _e('Textos del buscador', 'buscador-parroquias'); ?></h2>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><?php _e('Título de la cabecera', 'buscador-parroquias'); ?></th>
                    <td>
                        <input type="text" name="<?php echo BP_OPTION_KEY; ?>[header_title]"
                               value="<?php echo esc_attr($s['header_title']); ?>" class="large-text">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Subtítulo de la cabecera', 'buscador-parroquias'); ?></th>
                    <td>
                        <input type="text" name="<?php echo BP_OPTION_KEY; ?>[header_subtitle]"
                               value="<?php echo esc_attr($s['header_subtitle']); ?>" class="large-text">
                    </td>
                </tr>
            </table>

            <h2><?php _e('Pie de página del buscador', 'buscador-parroquias'); ?></h2>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><?php _e('Organización', 'buscador-parroquias'); ?></th>
                    <td><input type="text" name="<?php echo BP_OPTION_KEY; ?>[footer_org]"
                               value="<?php echo esc_attr($s['footer_org']); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Descripción', 'buscador-parroquias'); ?></th>
                    <td><input type="text" name="<?php echo BP_OPTION_KEY; ?>[footer_line1]"
                               value="<?php echo esc_attr($s['footer_line1']); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Dirección', 'buscador-parroquias'); ?></th>
                    <td><input type="text" name="<?php echo BP_OPTION_KEY; ?>[footer_address]"
                               value="<?php echo esc_attr($s['footer_address']); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Ciudad / CP', 'buscador-parroquias'); ?></th>
                    <td><input type="text" name="<?php echo BP_OPTION_KEY; ?>[footer_city]"
                               value="<?php echo esc_attr($s['footer_city']); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Email de contacto', 'buscador-parroquias'); ?></th>
                    <td><input type="email" name="<?php echo BP_OPTION_KEY; ?>[footer_email]"
                               value="<?php echo esc_attr($s['footer_email']); ?>" class="regular-text"></td>
                </tr>
            </table>

            <h2><?php _e('Búsqueda por ubicación', 'buscador-parroquias'); ?></h2>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><?php _e('Radio "Cerca de mí" (km)', 'buscador-parroquias'); ?></th>
                    <td>
                        <input type="number" min="1" max="100"
                               name="<?php echo BP_OPTION_KEY; ?>[radio_km]"
                               value="<?php echo esc_attr($s['radio_km']); ?>" class="small-text"> km
                        <p class="description"><?php _e('Distancia máxima para mostrar parroquias cercanas al usuario.', 'buscador-parroquias'); ?></p>
                    </td>
                </tr>
            </table>

            <hr>
            <p>
                <strong><?php _e('Shortcode:', 'buscador-parroquias'); ?></strong>
                <code>[buscador_parroquias]</code>
            </p>

            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}
