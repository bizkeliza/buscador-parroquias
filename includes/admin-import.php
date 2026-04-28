<?php
if (!defined('ABSPATH')) exit;

// Encolar SheetJS solo en la página de importación
add_action('admin_enqueue_scripts', 'bp_admin_enqueue');
function bp_admin_enqueue($hook) {
    if (strpos($hook, 'bp-importar') === false) return;

    wp_enqueue_script(
        'bp-xlsx-admin',
        'https://cdn.sheetjs.com/xlsx-0.20.3/package/dist/xlsx.full.min.js',
        [],
        '0.20.3',
        true
    );

    // Pasar nonce y ajaxurl al JS de importación
    wp_add_inline_script('bp-xlsx-admin', 'var BP_IMPORT = ' . wp_json_encode([
        'nonce'   => wp_create_nonce('bp_import_nonce'),
        'ajaxurl' => admin_url('admin-ajax.php'),
    ]) . ';');

    wp_add_inline_script('bp-xlsx-admin', file_get_contents(BP_PLUGIN_DIR . 'assets/js/bp-import.js'), 'after');
}

// Página de importación
function bp_admin_import_page() {
    if (!current_user_can('manage_options')) return;
    ?>
    <div class="wrap">
        <h1><?php _e('Importar Parroquias desde Excel', 'buscador-parroquias'); ?></h1>

        <p><?php _e('Sube un archivo <strong>.xlsx</strong> con los datos de las parroquias. El plugin detecta automáticamente las variantes de nombre de columna.', 'buscador-parroquias'); ?></p>

        <details style="margin-bottom:16px;">
            <summary style="cursor:pointer;color:#2271b1;"><?php _e('Ver columnas esperadas', 'buscador-parroquias'); ?></summary>
            <table class="widefat" style="max-width:640px;margin-top:8px;">
                <thead><tr><th>Columna en el Excel</th><th>Campo en WordPress</th></tr></thead>
                <tbody>
                    <tr><td>Parroquia /Parrokia · PARROQUIA · Parroquia</td><td><code>parroquia</code></td></tr>
                    <tr><td>Localidad/Herria · LOCALIDAD · Localidad</td><td><code>localidad</code></td></tr>
                    <tr><td>Helbidea/dirección · Dirección · DIRECCION</td><td><code>direccion</code></td></tr>
                    <tr><td>C.P. · CP · CÓDIGO POSTAL</td><td><code>cp</code></td></tr>
                    <tr><td>TELÉFONO · Teléfono · TELEFONO</td><td><code>telefono</code></td></tr>
                    <tr><td>EMAIL · E-MAIL · Email</td><td><code>email</code></td></tr>
                    <tr><td>PÁRROCO · PARROCO · Párroco</td><td><code>parroco</code></td></tr>
                    <tr><td>UP / SECTOR · UP/SECTOR · UP · SECTOR</td><td><code>up_sector</code></td></tr>
                    <tr><td>Horario misas invierno · HORARIO MISAS INVIERNO</td><td><code>horario_misas_invierno</code></td></tr>
                    <tr><td>Horario misas verano · HORARIO MISAS VERANO</td><td><code>horario_misas_verano</code></td></tr>
                    <tr><td>LATITUD · Latitud</td><td><code>latitud</code></td></tr>
                    <tr><td>LONGITUD · Longitud</td><td><code>longitud</code></td></tr>
                </tbody>
            </table>
        </details>

        <div style="background:#fff;padding:20px 24px;border:1px solid #c3c4c7;border-radius:4px;max-width:640px;">
            <table class="form-table" style="margin:0;">
                <tr>
                    <th style="width:200px;padding-left:0;"><?php _e('Archivo Excel (.xlsx)', 'buscador-parroquias'); ?></th>
                    <td><input type="file" id="bp-import-file" accept=".xlsx"></td>
                </tr>
                <tr>
                    <th style="padding-left:0;"><?php _e('Limpiar antes', 'buscador-parroquias'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" id="bp-import-delete">
                            <?php _e('Eliminar todas las parroquias existentes antes de importar', 'buscador-parroquias'); ?>
                        </label>
                    </td>
                </tr>
            </table>
            <br>
            <button id="bp-import-btn" class="button button-primary" disabled>
                <?php _e('Iniciar importación', 'buscador-parroquias'); ?>
            </button>
        </div>

        <div id="bp-import-log"
             style="display:none;margin-top:18px;max-width:640px;background:#f6f7f7;
                    border:1px solid #ddd;border-radius:4px;padding:12px 16px;
                    max-height:320px;overflow-y:auto;font-family:monospace;font-size:13px;">
        </div>
    </div>
    <?php
}

// ── AJAX: importar lote ────────────────────────────────────────────────────────
add_action('wp_ajax_bp_import_batch', 'bp_ajax_import_batch');
function bp_ajax_import_batch() {
    if (!current_user_can('manage_options')) wp_send_json_error('No autorizado', 403);
    check_ajax_referer('bp_import_nonce', 'nonce');

    $filas = json_decode(wp_unslash($_POST['data'] ?? '[]'), true);
    if (!is_array($filas)) wp_send_json_error('Datos inválidos');

    $campos  = ['parroquia','localidad','direccion','cp','telefono','email',
                 'parroco','up_sector','horario_misas_invierno','horario_misas_verano',
                 'latitud','longitud'];
    $creadas = $errores = 0;

    foreach ($filas as $fila) {
        if (!is_array($fila)) { $errores++; continue; }

        $titulo  = sanitize_text_field($fila['parroquia'] ?: ($fila['localidad'] ?: 'Sin nombre'));
        $post_id = wp_insert_post([
            'post_type'   => BP_CPT,
            'post_title'  => $titulo,
            'post_status' => 'publish',
        ], true);

        if (is_wp_error($post_id)) { $errores++; continue; }

        foreach ($campos as $campo) {
            update_post_meta($post_id, $campo, sanitize_text_field($fila[$campo] ?? ''));
        }
        $creadas++;
    }

    wp_send_json_success(['creadas' => $creadas, 'errores' => $errores]);
}

// ── AJAX: eliminar todas ───────────────────────────────────────────────────────
add_action('wp_ajax_bp_delete_all', 'bp_ajax_delete_all');
function bp_ajax_delete_all() {
    if (!current_user_can('manage_options')) wp_send_json_error('No autorizado', 403);
    check_ajax_referer('bp_import_nonce', 'nonce');

    $ids = get_posts([
        'post_type'      => BP_CPT,
        'posts_per_page' => -1,
        'post_status'    => 'any',
        'fields'         => 'ids',
    ]);

    foreach ($ids as $id) wp_delete_post($id, true);
    wp_send_json_success(count($ids));
}
