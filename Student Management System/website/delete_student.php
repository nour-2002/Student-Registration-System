<?php
declare(strict_types=1);
require __DIR__ . '/src/bootstrap.php'; user_required();
$id = record_id(); $repository = new Madar\StudentRepository(); $student = $repository->find($id);
if (!$student) { http_response_code(404); Madar\View::missing(); }
if (is_post()) { check_csrf(); if(!$repository->delete($id)) { http_response_code(404); Madar\View::missing(); } flash('تم حذف سجل الطالب وصورته.'); go('view_students.php'); }
Madar\View::start('تأكيد حذف الطالب', 'students');
?>
<header class="page-heading"><div><h1>مراجعة الحذف</h1></div></header><section class="surface delete-panel"><span class="delete-symbol"><?= Madar\View::icon('trash') ?></span><h2>حذف سجل <?= e($student['name']) ?>؟</h2><p>سيُحذف السجل والصورة المرتبطة به. لا يمكن استعادة البيانات من داخل الموقع بعد التأكيد.</p><div class="delete-identity"><?= Madar\View::avatar($student) ?><div><strong><?= e($student['name']) ?></strong><span dir="ltr"><?= e($student['email']) ?></span><span dir="ltr"><?= e($student['phone']) ?></span></div></div><form method="post"><input type="hidden" name="csrf" value="<?= e(csrf()) ?>"><div class="form-actions"><button class="btn btn-danger" type="submit">تأكيد حذف الطالب</button><a class="btn btn-light" href="view_students.php">الاحتفاظ بالسجل</a></div></form></section>
<?php Madar\View::end(); ?>
