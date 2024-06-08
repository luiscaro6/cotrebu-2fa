<?php
// Si se accede directamente, salir del script.
if ( !defined('ABSPATH') ) {
    exit;
}

require_once plugin_dir_path(__FILE__) . '../vendor/autoload.php';

use OTPHP\TOTP;

function cotrebu_2fa_generate_code($secreto) {
    $totp = TOTP::create($secreto);
    $codigo = $totp->now();

    return $codigo;
}
?>
