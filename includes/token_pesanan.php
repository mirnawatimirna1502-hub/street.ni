<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (empty($_SESSION['token_pesanan'])) {
    $_SESSION['token_pesanan'] = bin2hex(random_bytes(32));
}
?>
