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
require_once plugin_dir_path(__FILE__) . 'includes/ajax-code.php';


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


function show_2fa_code($atts) {
    global $wpdb; 

    $atts = shortcode_atts(array(
        'nombre_cuenta' => '',
    ), $atts);
    $valor = sanitize_text_field($atts['nombre_cuenta']);

    // Nombre completo de la tabla con el prefijo de WordPress
    $tabla = $wpdb->prefix . '2fa_accounts';

    // Realizar la consulta a la base de datos
    $query = $wpdb->prepare("SELECT * FROM $tabla WHERE nombre = %s", $valor);
    $resultado = $wpdb->get_row($query);

    if ($resultado) {
        $secreto = $resultado->secreto;
        $secreto_desencriptado = cotrebu_2fa_decrypt($secreto);
        $codigo = cotrebu_2fa_generate_code($secreto_desencriptado);

        // Contenedor para el contador y el código 2FA
        $output = '<div id="2fa-container">';
        $output .= '<div id="2fa-code">' . $codigo . '</div>';
        $output .= '<div id="2fa-timer"></div>';
        $output .= '</div>';
        
        // Incluir el script del contador y la solicitud AJAX
        $output .= '
        <script>
            function updateTimer() {
                const now = new Date();
                const seconds = now.getSeconds();
                const remaining = 30 - (seconds % 30);
                document.getElementById("2fa-timer").innerText = "Próximo código en " + remaining + " segundos";
                if (remaining === 30) {
                    fetchNewCode();
                }
            }

            function fetchNewCode() {
                const xhr = new XMLHttpRequest();
                xhr.open("POST", "' . admin_url('admin-ajax.php') . '");
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        document.getElementById("2fa-code").innerText = xhr.responseText;
                    }
                };
                xhr.send("action=refresh_2fa_code&nombre_cuenta=' . $valor . '");
            }

            setInterval(updateTimer, 1000);
            updateTimer();
        </script>';
        
        return $output;
    } else {
        return 'No se encontraron resultados.';
    }
}

add_shortcode('cotrebu_2fa_generator', 'show_2fa_code');






?>
