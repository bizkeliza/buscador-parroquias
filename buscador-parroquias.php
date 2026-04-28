<?php
/*
Plugin Name:  Buscador de Parroquias
Plugin URI:   https://github.com/bizkeliza/buscador-parroquias
Description:  Guía de parroquias y horarios de misa configurable para cualquier diócesis. Gestiona los datos mediante Custom Post Type y ACF, e incluye importación desde Excel.
Version:      2.0.0
Author:       Bizkeliza
Author URI:   https://bizkeliza.org
License:      GPL-2.0+
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
Text Domain:  buscador-parroquias
*/

if (!defined('ABSPATH')) exit;

define('BP_VERSION',    '2.0.0');
define('BP_CPT',        'bp_parroquia');
define('BP_REST_NS',    'bp-guia/v1');
define('BP_OPTION_KEY', 'bp_guia_settings');
define('BP_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('BP_PLUGIN_URL', plugin_dir_url(__FILE__));

require BP_PLUGIN_DIR . 'includes/cpt.php';
require BP_PLUGIN_DIR . 'includes/acf-fields.php';
require BP_PLUGIN_DIR . 'includes/rest-api.php';
require BP_PLUGIN_DIR . 'includes/admin-settings.php';
require BP_PLUGIN_DIR . 'includes/admin-import.php';
require BP_PLUGIN_DIR . 'includes/shortcode.php';
