<?php
function refresh_2fa_code() {
    global $wpdb;

    $valor = sanitize_text_field($_POST['nombre_cuenta']);

    // Nombre completo de la tabla con el prefijo de WordPress
    $tabla = $wpdb->prefix . '2fa_accounts';

    // Realizar la consulta a la base de datos
    $query = $wpdb->prepare("SELECT * FROM $tabla WHERE nombre = %s", $valor);
    $resultado = $wpdb->get_row($query);

    if ($resultado) {
        $secreto = $resultado->secreto;
        $secreto_desencriptado = cotrebu_2fa_decrypt($secreto);
        $codigo = cotrebu_2fa_generate_code($secreto_desencriptado);
        echo $codigo;
    } else {
        echo 'No se encontraron resultados.';
    }

    wp_die();
}

add_action('wp_ajax_refresh_2fa_code', 'refresh_2fa_code');
add_action('wp_ajax_nopriv_refresh_2fa_code', 'refresh_2fa_code');
