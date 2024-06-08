<?php
/*
Plugin Name: Cotrebu-2fa
Plugin URI: https://intranet.cotrebu.es/
Description: Plugin de autenticación de dos factores para Cotrebu.
Version: 1.0
Author: Luis Caro Caro 
Author URI: https://intranet.cotrebu.es/
License: GPL2
*/

// Si se accede directamente, salir del script.
if ( !defined('ABSPATH') ) {
    exit;
}

// Incluir el archivo de administración para el menú
require_once plugin_dir_path(__FILE__) . 'admin/menu.php';

require_once plugin_dir_path(__FILE__) . 'includes/totp-generator.php';
function cotrebu_2fa_activate() {
    // Crear la tabla necesaria en la base de datos
    global $wpdb;
    $tabla = $wpdb->prefix . '2fa_accounts';
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE IF NOT EXISTS $tabla (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        nombre VARCHAR(255) NOT NULL,
        secreto VARCHAR(255) NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
register_activation_hook(__FILE__, 'cotrebu_2fa_activate');
?>
