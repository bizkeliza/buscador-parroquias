<?php
if (!defined('ABSPATH')) exit;

/*
 * Registro programático de campos ACF.
 * Los nombres de campo (name) coinciden con los del Excel para facilitar la importación.
 *
 * Columna Excel             → Campo ACF (name)
 * ─────────────────────────────────────────────
 * Parroquia /Parrokia       → parroquia
 * Localidad/Herria          → localidad
 * Helbidea/dirección        → direccion
 * C.P.                      → cp
 * TELÉFONO                  → telefono
 * EMAIL                     → email
 * PÁRROCO                   → parroco
 * UP / SECTOR               → up_sector
 * Horario misas invierno    → horario_misas_invierno
 * Horario misas verano      → horario_misas_verano
 * LATITUD                   → latitud
 * LONGITUD                  → longitud
 */

add_action('acf/init', 'bp_register_acf_fields');
function bp_register_acf_fields() {
    if (!function_exists('acf_add_local_field_group')) return;

    acf_add_local_field_group([
        'key'   => 'group_bp_parroquia',
        'title' => 'Datos de la Parroquia',
        'fields' => [
            [
                'key'   => 'field_bp_parroquia',
                'label' => 'Parroquia',
                'name'  => 'parroquia',
                'type'  => 'text',
            ],
            [
                'key'   => 'field_bp_localidad',
                'label' => 'Localidad',
                'name'  => 'localidad',
                'type'  => 'text',
            ],
            [
                'key'   => 'field_bp_direccion',
                'label' => 'Dirección',
                'name'  => 'direccion',
                'type'  => 'text',
            ],
            [
                'key'   => 'field_bp_cp',
                'label' => 'C.P.',
                'name'  => 'cp',
                'type'  => 'text',
            ],
            [
                'key'   => 'field_bp_telefono',
                'label' => 'Teléfono',
                'name'  => 'telefono',
                'type'  => 'text',
            ],
            [
                'key'   => 'field_bp_email',
                'label' => 'Email',
                'name'  => 'email',
                'type'  => 'email',
            ],
            [
                'key'   => 'field_bp_parroco',
                'label' => 'Párroco',
                'name'  => 'parroco',
                'type'  => 'text',
            ],
            [
                'key'   => 'field_bp_up_sector',
                'label' => 'UP / Sector',
                'name'  => 'up_sector',
                'type'  => 'text',
            ],
            [
                'key'   => 'field_bp_horario_invierno',
                'label' => 'Horario misas invierno',
                'name'  => 'horario_misas_invierno',
                'type'  => 'text',
            ],
            [
                'key'   => 'field_bp_horario_verano',
                'label' => 'Horario misas verano',
                'name'  => 'horario_misas_verano',
                'type'  => 'text',
            ],
            [
                'key'      => 'field_bp_latitud',
                'label'    => 'Latitud',
                'name'     => 'latitud',
                'type'     => 'number',
                'step'     => 'any',
                'prepend'  => '°N',
            ],
            [
                'key'      => 'field_bp_longitud',
                'label'    => 'Longitud',
                'name'     => 'longitud',
                'type'     => 'number',
                'step'     => 'any',
                'prepend'  => '°E',
            ],
        ],
        'location' => [[
            ['param' => 'post_type', 'operator' => '==', 'value' => BP_CPT],
        ]],
        'menu_order'      => 0,
        'position'        => 'normal',
        'style'           => 'default',
        'label_placement' => 'top',
    ]);
}
