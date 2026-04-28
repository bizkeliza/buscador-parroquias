<?php
if (!defined('ABSPATH')) exit;

add_action('rest_api_init', 'bp_register_rest_routes');
function bp_register_rest_routes() {
    register_rest_route(BP_REST_NS, '/parroquias', [
        'methods'             => 'GET',
        'callback'            => 'bp_rest_get_parroquias',
        'permission_callback' => '__return_true',
    ]);
}

function bp_rest_get_parroquias() {
    $posts = get_posts([
        'post_type'      => BP_CPT,
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'orderby'        => 'title',
        'order'          => 'ASC',
        'no_found_rows'  => true,
    ]);

    $result = [];
    foreach ($posts as $post) {
        $lat = get_post_meta($post->ID, 'latitud',  true);
        $lon = get_post_meta($post->ID, 'longitud', true);

        $result[] = [
            'parroquia'       => get_post_meta($post->ID, 'parroquia',              true),
            'localidad'       => get_post_meta($post->ID, 'localidad',              true),
            'direccion'       => get_post_meta($post->ID, 'direccion',              true),
            'cp'              => get_post_meta($post->ID, 'cp',                     true),
            'telefono'        => get_post_meta($post->ID, 'telefono',               true),
            'email'           => get_post_meta($post->ID, 'email',                  true),
            'parroco'         => get_post_meta($post->ID, 'parroco',                true),
            'upSector'        => get_post_meta($post->ID, 'up_sector',              true),
            'horarioInvierno' => get_post_meta($post->ID, 'horario_misas_invierno', true),
            'horarioVerano'   => get_post_meta($post->ID, 'horario_misas_verano',   true),
            'latitud'         => $lat !== '' ? (float) $lat : null,
            'longitud'        => $lon !== '' ? (float) $lon : null,
        ];
    }

    $response = new WP_REST_Response($result, 200);
    $response->header('Cache-Control', 'public, max-age=3600');
    return $response;
}
