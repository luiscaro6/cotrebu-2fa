<?php
/**
 * Plugin Name: Cotrebu 2FA
 * Description: Plugin de autenticación de dos factores para Cotrebu.
 * Version: 1.0
 * Author: Tu Nombre
 * Text Domain: cotrebu-2fa
 */

// Si se accede directamente, salir del script.
if ( !defined('ABSPATH') ) {
    exit;
}

// Incluir los archivos necesarios
require_once plugin_dir_path(__FILE__) . 'admin/menu.php';
require_once plugin_dir_path(__FILE__) . 'includes/totp-generator.php';
require_once plugin_dir_path(__FILE__) . 'includes/encryption.php';

// Crear la tabla y guardar la clave maestra al activar el plugin
function cotrebu_2fa_activate() {
    global $wpdb;
    $tabla = $wpdb->prefix . '2fa_accounts';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $tabla (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        nombre varchar(50) NOT NULL,
        secreto text NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    // Generar y guardar la clave maestra en wp-options
    if ( !get_option('cotrebu_2fa_master_key') ) {
        $master_key = wp_generate_password(64, true, true);
        add_option('cotrebu_2fa_master_key', $master_key);
    }

    // Configuración de eliminación de datos
    if ( !get_option('cotrebu_2fa_eliminar_datos') ) {
        add_option('cotrebu_2fa_eliminar_datos', 'no');
    }
}
register_activation_hook(__FILE__, 'cotrebu_2fa_activate');

// Función para eliminar la tabla y la clave maestra al desactivar el plugin
function cotrebu_2fa_deactivate() {
    if ( get_option('cotrebu_2fa_eliminar_datos') === 'yes' ) {
        global $wpdb;
        $tabla = $wpdb->prefix . '2fa_accounts';
        $sql = "DROP TABLE IF EXISTS $tabla;";
        $wpdb->query($sql);

        delete_option('cotrebu_2fa_master_key');
        delete_option('cotrebu_2fa_eliminar_datos');
    }
}
register_deactivation_hook(__FILE__, 'cotrebu_2fa_deactivate');
?>
