<?php
declare(strict_types=1);
require __DIR__ . '/src/bootstrap.php';
if (!is_post()) { http_response_code(405); header('Allow: POST'); exit('استخدم زر تسجيل الخروج داخل الموقع.'); }
check_csrf();
$_SESSION = [];
$cookie = session_get_cookie_params();
setcookie(session_name(), '', ['expires' => time()-3600, 'path' => $cookie['path'], 'secure' => $cookie['secure'], 'httponly' => true, 'samesite' => 'Lax']);
session_destroy(); go('login.html');
