<?php
if (!defined('ABSPATH')) exit;

add_action('wp_enqueue_scripts', 'bp_enqueue_frontend');
function bp_enqueue_frontend() {
    if (!is_singular()) return; // cargar solo en páginas/entradas individuales

    wp_enqueue_style(
        'buscador-parroquias',
        BP_PLUGIN_URL . 'assets/css/buscador-parroquias.css',
        [],
        BP_VERSION
    );

    wp_enqueue_script(
        'buscador-parroquias',
        BP_PLUGIN_URL . 'assets/js/buscador-parroquias.js',
        [],
        BP_VERSION,
        true
    );

    $s = bp_get_settings();
    wp_localize_script('buscador-parroquias', 'BP_CONFIG', [
        'restUrl'         => esc_url_raw(rest_url(BP_REST_NS . '/parroquias')),
        'radioKm'         => (int) $s['radio_km'],
        'orgName'         => $s['org_name'],
        'logoUrl'         => $s['logo_url'],
        'logoLink'        => $s['logo_link'],
        'headerTitle'     => $s['header_title'],
        'headerSubtitle'  => $s['header_subtitle'],
        'footerOrg'       => $s['footer_org'],
        'footerLine1'     => $s['footer_line1'],
        'footerAddress'   => $s['footer_address'],
        'footerCity'      => $s['footer_city'],
        'footerEmail'     => $s['footer_email'],
    ]);
}

add_shortcode('buscador_parroquias', 'bp_shortcode');
function bp_shortcode() {
    return '<div id="bp-app"></div>';
}
