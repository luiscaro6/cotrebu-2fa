<?php
// Si se accede directamente, salir del script.
if ( !defined('ABSPATH') ) {
    exit;
}

// Función para añadir el menú del plugin al escritorio
function cotrebu_2fa_menu() {
    add_menu_page(
        'Cotrebu 2FA',          // Título de la página
        'Cotrebu 2FA',          // Título del menú
        'manage_options',       // Capacidad requerida
        'cotrebu-2fa',          // Slug del menú
        'cotrebu_2fa_pagina',   // Función que muestra el contenido de la página
        'dashicons-shield',     // Icono del menú
        20                      // Posición en el menú
    );
}

// Hook para añadir el menú al escritorio
add_action('admin_menu', 'cotrebu_2fa_menu');

// Función para mostrar el contenido de la página del plugin
function cotrebu_2fa_pagina() {
    // Procesar el formulario de guardado de secreto si se ha enviado
    if ( isset($_POST['cotrebu_nombre']) && !empty($_POST['cotrebu_nombre']) && isset($_POST['cotrebu_secreto']) && !empty($_POST['cotrebu_secreto']) ) {
        $nombre = sanitize_text_field($_POST['cotrebu_nombre']);
        $secreto = sanitize_text_field($_POST['cotrebu_secreto']);
        
        // Encriptar el secreto
        $secreto_encriptado = cotrebu_2fa_encrypt($secreto);

        // Guardar el secreto en la base de datos
        global $wpdb;
        $tabla = $wpdb->prefix . '2fa_accounts';
        $wpdb->insert(
            $tabla,
            array(
                'nombre' => $nombre,
                'secreto' => $secreto_encriptado,
            )
        );
        echo '<div class="updated"><p>Secreto guardado correctamente.</p></div>';
    }

    // Procesar el formulario de configuración
    if ( isset($_POST['cotrebu_eliminar_datos']) ) {
        update_option('cotrebu_2fa_eliminar_datos', sanitize_text_field($_POST['cotrebu_eliminar_datos']));
    } else {
        update_option('cotrebu_2fa_eliminar_datos', 'no');
    }

    // Obtener los secretos de la base de datos
    global $wpdb;
    $tabla = $wpdb->prefix . '2fa_accounts';
    $secretos = $wpdb->get_results("SELECT * FROM $tabla", ARRAY_A);

    // Obtener la configuración actual
    $eliminar_datos = get_option('cotrebu_2fa_eliminar_datos', 'no');
    ?>
    <div class="wrap">
        <h1>Cotrebu 2FA</h1>
        <p>Bienvenido a la configuración del plugin de autenticación de dos factores para Cotrebu.</p>
        <form method="post" action="">
            <h2>Añadir Nuevo Secreto</h2>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Nombre</th>
                    <td><input type="text" name="cotrebu_nombre" value="" class="regular-text" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Secreto</th>
                    <td><input type="text" name="cotrebu_secreto" value="" class="regular-text" /></td>
                </tr>
            </table>
            <?php submit_button('Guardar Secreto'); ?>
        </form>

        <h2>Configuración del Plugin</h2>
        <form method="post" action="">
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Eliminar datos al desactivar el plugin</th>
                    <td><input type="checkbox" name="cotrebu_eliminar_datos" value="yes" <?php checked($eliminar_datos, 'yes'); ?> /> Sí</td>
                </tr>
            </table>
            <?php submit_button('Guardar Configuración'); ?>
        </form>

        <h2>Secretos Guardados</h2>
        <table class="wp-list-table widefat striped">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Secreto</th>
                    <th>Código Actual</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($secretos as $secreto) : 
                    $secreto_desencriptado = cotrebu_2fa_decrypt($secreto['secreto']);
                ?>
                    <tr>
                        <td><?php echo esc_html($secreto['nombre']); ?></td>
                        <td><?php echo esc_html($secreto_desencriptado); ?></td>
                        <td><?php echo cotrebu_2fa_generate_code($secreto_desencriptado); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
}
?>
