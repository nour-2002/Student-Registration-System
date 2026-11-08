<?php
declare(strict_types=1);

define('MADAR_BOOTED', true);
spl_autoload_register(static function (string $class): void {
    $prefix = 'Madar\\';
    if (str_starts_with($class, $prefix)) {
        $path = __DIR__ . '/' . substr($class, strlen($prefix)) . '.php';
        if (is_file($path)) require $path;
    }
});

ini_set('display_errors', '0');
ini_set('session.use_strict_mode', '1');
session_name('madar_session');
session_set_cookie_params([
    'lifetime' => 0, 'path' => '/', 'httponly' => true, 'samesite' => 'Lax',
    'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
]);
session_start();
header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: same-origin');
header("Content-Security-Policy: default-src 'self'; style-src 'self'; font-src 'self'; img-src 'self' data: blob:; script-src 'self'; connect-src 'self'; form-action 'self'; base-uri 'none'; frame-ancestors 'none'");

set_exception_handler(static function (Throwable $error): void {
    error_log('Madar: ' . $error->getMessage());
    http_response_code(500);
    $message = 'تعذر إتمام العملية الآن. تحقق من إعداد قاعدة البيانات وصلاحيات التخزين ثم أعد المحاولة.';
    if (str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['message' => $message], JSON_UNESCAPED_UNICODE);
    } else {
        echo '<!doctype html><html lang="ar" dir="rtl"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>تعذر إتمام العملية</title><link rel="stylesheet" href="assets/app.css?v=2"><main class="standalone"><h1>تعذر إتمام العملية</h1><p>' . $message . '</p><a href="index.html">العودة للرئيسية</a></main></html>';
    }
});

function e(mixed $value): string { return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function csrf(): string { return $_SESSION['csrf'] ??= bin2hex(random_bytes(32)); }
function go(string $path): never { header('Location: ' . $path, true, 303); exit; }
function is_post(): bool { return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST'; }
function check_csrf(): void {
    $token = $_POST['csrf'] ?? '';
    if (!is_string($token) || !hash_equals(csrf(), $token)) {
        http_response_code(419);
        if (str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['message' => 'انتهت صلاحية النموذج. حدّث الصفحة ثم حاول مجددًا.'], JSON_UNESCAPED_UNICODE);
        } else {
            Madar\View::start('انتهت صلاحية النموذج');
            echo '<section class="surface"><h1>انتهت صلاحية النموذج</h1><p>حدّث الصفحة لبدء نموذج جديد.</p><a href="index.html">العودة للرئيسية</a></section>';
            Madar\View::end();
        }
        exit;
    }
}
function user_required(bool $json = false): array {
    if (!isset($_SESSION['user'])) {
        if ($json) {
            http_response_code(401);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['message' => 'سجل الدخول لمتابعة العمل.'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        go('login.html');
    }
    return $_SESSION['user'];
}
function flash(string $message): void { $_SESSION['notice'] = $message; }
function posted(string $key): string { return is_string($_POST[$key] ?? null) ? trim($_POST[$key]) : ''; }
function record_id(): int {
    $id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if (!$id) { http_response_code(404); Madar\View::missing(); }
    return $id;
}
