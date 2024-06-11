<?php

if ( !defined('ABSPATH') ) {
    exit;
}

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

        // Contenedor para el código 2FA y el temporizador
        $output = '<div id="2fa-container" style="display: flex; align-items: center; border: 1px solid #000; padding: 10px; border-radius: 5px;">';
        $output .= '<div id="2fa-details" style="flex-grow: 1;">';
        $output .= '<div id="2fa-account" style="font-weight: bold;">' . esc_html($valor) . '</div>';
        $output .= '<div id="2fa-code" style="font-size: 2em; margin-top: 10px;">' . $codigo . '</div>';
        $output .= '</div>';
        $output .= '<div id="2fa-timer-container" style="position: relative; width: 50px; height: 50px; margin-left: 20px;">';
        $output .= '<svg id="2fa-timer-svg" viewBox="0 0 36 36" style="transform: rotate(-90deg);"><path id="2fa-timer-path" d="M18 2.0845
        a 15.9155 15.9155 0 0 1 0 31.831
        a 15.9155 15.9155 0 0 1 0 -31.831" style="fill: none; stroke: #3498db; stroke-width: 2.8;"></path></svg>';
        $output .= '<div id="2fa-timer-text" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 1em;">30</div>';
        $output .= '</div>';
        $output .= '</div>';

        // Incluir el script del contador y la solicitud AJAX
        $output .= '
        <script>
            function updateTimer() {
                const now = new Date();
                const seconds = now.getSeconds();
                const remaining = 30 - (seconds % 30);
                document.getElementById("2fa-timer-text").innerText = remaining;
                
                const percent = (remaining / 30) * 100;
                document.getElementById("2fa-timer-path").style.strokeDasharray = percent + ", 100";

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