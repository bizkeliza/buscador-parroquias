<?php
if (!defined('ABSPATH')) exit;

add_action('init', 'bp_register_cpt');
function bp_register_cpt() {
    register_post_type(BP_CPT, [
        'labels' => [
            'name'          => __('Parroquias', 'buscador-parroquias'),
            'singular_name' => __('Parroquia', 'buscador-parroquias'),
            'add_new'       => __('Añadir', 'buscador-parroquias'),
            'add_new_item'  => __('Añadir parroquia', 'buscador-parroquias'),
            'edit_item'     => __('Editar parroquia', 'buscador-parroquias'),
            'search_items'  => __('Buscar parroquias', 'buscador-parroquias'),
            'not_found'     => __('No hay parroquias', 'buscador-parroquias'),
        ],
        'public'          => false,
        'show_ui'         => true,
        'show_in_menu'    => true,
        'show_in_rest'    => false,
        'menu_icon'       => 'dashicons-admin-multisite',
        'supports'        => ['title'],
        'capability_type' => 'post',
    ]);
}

// ── Columna Localidad en el listado ──────────────────────────────────────────

add_filter('manage_bp_parroquia_posts_columns', 'bp_add_localidad_column');
function bp_add_localidad_column($columns) {
    $new = [];
    foreach ($columns as $key => $label) {
        $new[$key] = $label;
        if ($key === 'title') {
            $new['localidad'] = __('Localidad', 'buscador-parroquias');
            $new['up_sector'] = __('UP / Sector', 'buscador-parroquias');
        }
    }
    return $new;
}

add_action('manage_bp_parroquia_posts_custom_column', 'bp_render_localidad_column', 10, 2);
function bp_render_localidad_column($column, $post_id) {
    if ($column === 'localidad') {
        echo esc_html(get_post_meta($post_id, 'localidad', true));
    }
    if ($column === 'up_sector') {
        echo esc_html(get_post_meta($post_id, 'up_sector', true));
    }
}

add_filter('manage_edit-bp_parroquia_sortable_columns', 'bp_sortable_localidad_column');
function bp_sortable_localidad_column($columns) {
    $columns['localidad'] = 'localidad';
    $columns['up_sector'] = 'up_sector';
    return $columns;
}

add_action('pre_get_posts', 'bp_localidad_orderby');
function bp_localidad_orderby($query) {
    if (!is_admin() || !$query->is_main_query()) return;
    if ($query->get('post_type') !== BP_CPT) return;
    $orderby = $query->get('orderby');
    if ($orderby === 'localidad') {
        $query->set('meta_key', 'localidad');
        $query->set('orderby', 'meta_value');
    } elseif ($orderby === 'up_sector') {
        $query->set('meta_key', 'up_sector');
        $query->set('orderby', 'meta_value');
    }
}

// ── Búsqueda por localidad en el buscador del escritorio ─────────────────────

add_filter('posts_join', 'bp_localidad_search_join', 10, 2);
function bp_localidad_search_join($join, $query) {
    global $wpdb;
    if (!is_admin() || !$query->is_main_query() || !$query->is_search()) return $join;
    if ($query->get('post_type') !== BP_CPT) return $join;
    $join .= " LEFT JOIN {$wpdb->postmeta} AS bp_meta_loc
                ON ({$wpdb->posts}.ID = bp_meta_loc.post_id
                AND bp_meta_loc.meta_key = 'localidad')";
    $join .= " LEFT JOIN {$wpdb->postmeta} AS bp_meta_up
                ON ({$wpdb->posts}.ID = bp_meta_up.post_id
                AND bp_meta_up.meta_key = 'up_sector')";
    return $join;
}

add_filter('posts_search', 'bp_localidad_search_where', 10, 2);
function bp_localidad_search_where($search, $query) {
    global $wpdb;
    if (!is_admin() || !$query->is_main_query() || !$query->is_search()) return $search;
    if ($query->get('post_type') !== BP_CPT) return $search;
    $term = '%' . $wpdb->esc_like($query->get('s')) . '%';
    $search .= $wpdb->prepare(' OR bp_meta_loc.meta_value LIKE %s', $term);
    $search .= $wpdb->prepare(' OR bp_meta_up.meta_value LIKE %s', $term);
    return $search;
}

// Evita duplicados cuando el JOIN genera varias filas por post
add_filter('posts_distinct', 'bp_localidad_search_distinct', 10, 2);
function bp_localidad_search_distinct($distinct, $query) {
    if (!is_admin() || !$query->is_main_query() || !$query->is_search()) return $distinct;
    if ($query->get('post_type') !== BP_CPT) return $distinct;
    return 'DISTINCT';
}
