<?php
function auth_h($value): string { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
function auth_redirect_after_login(): void {
    $to = $_SESSION['pelanggan_redirect'] ?? 'index.php';
    unset($_SESSION['pelanggan_redirect']);
    if (!is_string($to) || !in_array($to, ['index.php','riwayat_pesanan.php'], true)) $to = 'index.php';
    header('Location: ' . $to); exit;
}
function auth_csrf(): string {
    if (empty($_SESSION['csrf_pelanggan'])) $_SESSION['csrf_pelanggan'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf_pelanggan'];
}
function auth_check_csrf($token): bool {
    return is_string($token) && isset($_SESSION['csrf_pelanggan']) && hash_equals($_SESSION['csrf_pelanggan'], $token);
}
?>
