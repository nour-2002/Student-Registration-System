<?php
declare(strict_types=1);
require __DIR__ . '/src/bootstrap.php';
if (isset($_SESSION['user'])) go('dashboard.php');
$mode = 'login'; $errors = []; $values = [];
if (is_post()) {
    check_csrf();
    $values['identity'] = posted('identity');
    $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
    $attempts = $_SESSION['login_attempts'] ?? ['count' => 0, 'time' => time()];
    if (time() - $attempts['time'] > 300) $attempts = ['count' => 0, 'time' => time()];
    if ($attempts['count'] >= 8) {
        $errors[] = 'محاولات دخول كثيرة في هذه الجلسة. حاول بعد خمس دقائق.';
    } else {
        $query = Madar\Database::connection()->prepare('SELECT id,username,email,password FROM users WHERE username=? OR email=? LIMIT 1');
        $query->execute([$values['identity'],$values['identity']]);
        $user = $query->fetch();
        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user'] = ['id' => (int) $user['id'], 'username' => $user['username'], 'email' => $user['email']];
            unset($_SESSION['csrf'], $_SESSION['login_attempts']);
            flash('تم تسجيل الدخول بنجاح. أهلًا بك.'); go('dashboard.php');
        }
        $attempts['count']++; $_SESSION['login_attempts'] = $attempts;
        $errors[] = 'اسم المستخدم أو البريد أو كلمة المرور غير صحيحة.';
    }
    http_response_code(422);
}
Madar\View::start('تسجيل الدخول'); require __DIR__ . '/templates/auth.php'; Madar\View::end();
