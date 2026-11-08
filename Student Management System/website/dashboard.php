<?php
declare(strict_types=1);
require __DIR__ . '/src/bootstrap.php';
$user = user_required(); $repository = new Madar\StudentRepository(); $stats = $repository->stats(); $latest = $repository->page('', 'newest', 1)['rows'];
Madar\View::start('نظرة عامة', 'dashboard');
?>
<header class="page-heading"><div><h1>أهلًا <?= e($user['username']) ?></h1></div><a class="btn btn-primary" href="add_student.php"><?= Madar\View::icon('plus') ?>تسجيل طالب</a></header>
<section class="overview-strip" aria-label="إحصاءات السجلات"><div><span>إجمالي الطلاب</span><strong><?= (int)$stats['total'] ?></strong></div><div><span>أضيفوا اليوم</span><strong><?= (int)$stats['today'] ?></strong></div><div><span>سجلات بصور</span><strong><?= (int)$stats['photos'] ?></strong></div></section>
<section class="surface"><div class="section-heading"><div><h2>آخر السجلات</h2></div><a href="view_students.php">فتح الدليل</a></div>
<?php if (!$latest): ?><div class="empty-state"><?= Madar\View::icon('users') ?><h3>ابدأ بأول طالب</h3><p>أضف بيانات الطالب، وستظهر هنا وفي دليل الطلاب.</p><a class="btn btn-primary" href="add_student.php">تسجيل طالب</a></div><?php else: ?><div class="recent-list"><?php foreach (array_slice($latest,0,5) as $student): ?><a href="update_student.php?id=<?= (int)$student['id'] ?>"><?= Madar\View::avatar($student) ?><div><strong><?= e($student['name']) ?></strong><small dir="ltr"><?= e($student['email']) ?></small></div><span class="recent-date"><?= e(substr($student['created_at'],0,10)) ?></span><?= Madar\View::icon('edit') ?></a><?php endforeach; ?></div><?php endif; ?></section>
<?php Madar\View::end(); ?>
