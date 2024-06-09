<?php
// Función para encriptar el secreto
function cotrebu_2fa_encrypt($data) {
    $key = get_option('cotrebu_2fa_master_key');
    $method = 'aes-256-cbc';
    $iv = openssl_random_pseudo_bytes(16);
    $encrypted = openssl_encrypt($data, $method, $key, 0, $iv);
    return base64_encode($encrypted . '::' . $iv);
}

// Función para desencriptar el secreto
function cotrebu_2fa_decrypt($data) {
    $key = get_option('cotrebu_2fa_master_key');
    $method = 'aes-256-cbc';
    list($encrypted_data, $iv) = explode('::', base64_decode($data), 2);
    return openssl_decrypt($encrypted_data, $method, $key, 0, $iv);
}
?>
