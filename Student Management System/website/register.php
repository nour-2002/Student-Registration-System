<?php
declare(strict_types=1);
require __DIR__ . '/src/bootstrap.php';
if (isset($_SESSION['user'])) go('dashboard.php');
$mode = 'register'; $errors = []; $values = [];
if (is_post()) {
    check_csrf();
    $values = ['username' => posted('username'), 'email' => posted('email')];
    $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
    if (!preg_match('/^[\p{L}\p{N}_.-]{3,100}$/uD', $values['username']) || str_contains($values['username'], '@')) $errors[] = 'اسم المستخدم يجب أن يتكون من 3 إلى 100 حرف أو رقم أو شرطة أو نقطة.';
    if (!filter_var($values['email'], FILTER_VALIDATE_EMAIL) || mb_strlen($values['email']) > 100) $errors[] = 'البريد الإلكتروني غير صحيح أو أطول من 100 حرف.';
    if (mb_strlen($password) < 8 || strlen($password) > 72 || str_contains($password, "\0")) $errors[] = 'كلمة المرور يجب أن تكون 8 أحرف على الأقل ولا تتجاوز 72 بايت.';
    if ($password !== ($_POST['confirmation'] ?? null)) $errors[] = 'تأكيد كلمة المرور لا يطابق كلمة المرور.';
    if (!$errors) {
        try {
            $query = Madar\Database::connection()->prepare('INSERT INTO users (username,email,password) VALUES (?,?,?)');
            $query->execute([$values['username'],$values['email'],password_hash($password, PASSWORD_DEFAULT)]);
            flash('تم إنشاء حسابك. سجل الدخول للبدء.'); go('login.php');
        } catch (PDOException $error) {
            if ($error->getCode() === '23000') $errors[] = 'اسم المستخدم أو البريد مستخدم بالفعل. اختر بيانات أخرى.';
            else throw $error;
        }
    }
    http_response_code(422);
}
Madar\View::start('إنشاء حساب'); require __DIR__ . '/templates/auth.php'; Madar\View::end();
