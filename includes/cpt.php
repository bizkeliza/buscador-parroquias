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
