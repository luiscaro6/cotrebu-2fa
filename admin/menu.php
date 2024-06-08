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
        // Guardar el secreto en la base de datos
        global $wpdb;
        $tabla = $wpdb->prefix . '2fa_accounts';
        $wpdb->insert(
            $tabla,
            array(
                'nombre' => $nombre,
                'secreto' => $secreto,
            )
        );
        echo '<div class="updated"><p>Secreto guardado correctamente.</p></div>';
    }
    global $wpdb;
    $tabla = $wpdb->prefix . '2fa_accounts';
    $secretos = $wpdb->get_results("SELECT * FROM $tabla", ARRAY_A);
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
                <?php foreach ($secretos as $secreto) : ?>
                    <tr>
                        <td><?php echo esc_html($secreto['nombre']); ?></td>
                        <td><?php echo esc_html($secreto['secreto']); ?></td>
                        <td><?php echo cotrebu_2fa_generate_code($secreto['secreto']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php
}
?>
