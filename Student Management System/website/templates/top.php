<?php defined('MADAR_BOOTED') || exit; ?>
<!doctype html>
<html lang="ar" dir="rtl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?= e($title) ?> | نظام إدارة الطلاب</title><link rel="stylesheet" href="assets/bootstrap.rtl.min.css"><link rel="stylesheet" href="assets/app.css?v=2"><script defer src="assets/app.js?v=2"></script></head>
<body class="<?= $user ? 'workspace' : 'auth-body' ?>"><a class="skip" href="#main">انتقل إلى المحتوى</a>
<?php if ($user): ?>
<aside class="sidebar"><a class="brand" href="index.html"><span>نظام إدارة الطلاب</span></a>
<nav aria-label="القائمة الرئيسية"><a href="dashboard.php" <?= $active === 'dashboard' ? 'aria-current="page"' : '' ?>><?= Madar\View::icon('grid') ?>نظرة عامة</a><a href="view_students.php" <?= $active === 'students' ? 'aria-current="page"' : '' ?>><?= Madar\View::icon('users') ?>دليل الطلاب</a><a href="add_student.php" <?= $active === 'add' ? 'aria-current="page"' : '' ?>><?= Madar\View::icon('plus') ?>تسجيل طالب</a></nav>
<div class="sidebar-bottom"><div class="account"><span class="account-dot"><?= e(mb_substr($user['username'],0,1)) ?></span><div><strong><?= e($user['username']) ?></strong><small>حساب مسجل</small></div></div><form method="post" action="logout.php"><input type="hidden" name="csrf" value="<?= e(csrf()) ?>"><button class="logout" type="submit"><?= Madar\View::icon('exit') ?>تسجيل الخروج</button></form></div></aside>
<?php else: ?><header class="auth-header"><a class="brand" href="index.html"><span>نظام إدارة الطلاب</span></a><a href="index.html">العودة إلى الرئيسية</a></header><?php endif; ?>
<main id="main" class="<?= $user ? 'work-main' : 'auth-main' ?>">
<?php if (isset($_SESSION['notice'])): ?><div class="alert notice" role="status"><?= Madar\View::icon('check') ?><?= e($_SESSION['notice']) ?></div><?php unset($_SESSION['notice']); endif; ?>
