<?php
// Autoload bundled vendor libraries

// Load jumbojett OpenID Connect library
if (file_exists(__DIR__ . '/jumbojett/openid-connect-php/OpenIDConnectClient.php')) {
    require_once __DIR__ . '/jumbojett/openid-connect-php/OpenIDConnectClient.php';
}
