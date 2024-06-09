<?php
// Si se accede directamente, salir del script.
if ( !defined('ABSPATH') ) {
    exit;
}

// Función para eliminar la tabla y la clave maestra al desactivar el plugin si la opción está habilitada
function cotrebu_2fa_uninstall() {
    if ( get_option('cotrebu_2fa_eliminar_datos') === 'yes' ) {
        global $wpdb;
        $tabla = $wpdb->prefix . '2fa_accounts';
        $sql = "DROP TABLE IF EXISTS $tabla;";
        $wpdb->query($sql);

        delete_option('cotrebu_2fa_master_key');
        delete_option('cotrebu_2fa_eliminar_datos');
    }
}
register_uninstall_hook(__FILE__, 'cotrebu_2fa_uninstall');
?>
