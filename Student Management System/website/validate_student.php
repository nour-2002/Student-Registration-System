<?php
declare(strict_types=1);
require __DIR__ . '/src/bootstrap.php'; user_required(true);
header('Content-Type: application/json; charset=utf-8');
if (!is_post()) { http_response_code(405); header('Allow: POST'); echo json_encode(['message' => 'POST required']); exit; }
check_csrf();
$errors = Madar\StudentRules::errors(Madar\StudentRules::data($_POST));
echo json_encode(['valid' => !$errors, 'errors' => $errors], JSON_UNESCAPED_UNICODE);
