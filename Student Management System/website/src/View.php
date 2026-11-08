<?php
declare(strict_types=1);
namespace Madar;
defined('MADAR_BOOTED') || exit;

final class View {
    public static function icon(string $name): string {
        $paths = [
            'orbit' => '<ellipse cx="12" cy="12" rx="10" ry="4" transform="rotate(-35 12 12)"/><ellipse cx="12" cy="12" rx="10" ry="4" transform="rotate(35 12 12)"/><circle cx="12" cy="12" r="2"/>',
            'users' => '<circle cx="9" cy="8" r="3"/><path d="M3 21v-3a6 6 0 0 1 12 0v3M16 5a3 3 0 0 1 0 6m2 4a5 5 0 0 1 3 5"/>',
            'plus' => '<path d="M12 5v14M5 12h14"/>',
            'grid' => '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>',
            'edit' => '<path d="m16 3 5 5-12 12-6 1 1-6ZM13 6l5 5"/>',
            'trash' => '<path d="M3 6h18M9 6V3h6v3M5 6l1 15h12l1-15M10 10v7m4-7v7"/>',
            'search' => '<circle cx="10" cy="10" r="6"/><path d="m15 15 6 6"/>',
            'exit' => '<path d="M9 3H3v18h6M10 12h11m-4-4 4 4-4 4"/>',
            'check' => '<path d="m5 12 4 4L20 5"/>',
        ];
        return '<svg class="icon" viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">' . ($paths[$name] ?? $paths['orbit']) . '</svg>';
    }
    public static function start(string $title, string $active = ''): void {
        $user = $_SESSION['user'] ?? null;
        require dirname(__DIR__) . '/templates/top.php';
    }
    public static function end(): void { require dirname(__DIR__) . '/templates/bottom.php'; }
    public static function missing(): never {
        self::start('السجل غير موجود');
        echo '<section class="surface empty-state"><h1>السجل غير موجود</h1><p>قد يكون حُذف أو أن الرابط غير صحيح.</p><a class="btn btn-primary" href="view_students.php">العودة إلى دليل الطلاب</a></section>';
        self::end(); exit;
    }
    public static function avatar(array $student, string $extra = ''): string {
        if ($student['profile_picture'] ?? null) return '<img class="avatar ' . e($extra) . '" src="photo.php?id=' . (int) $student['id'] . '" alt="صورة ' . e($student['name']) . '" width="48" height="48" loading="lazy">';
        return '<span class="avatar initials ' . e($extra) . '" aria-hidden="true">' . e(mb_substr($student['name'] ?: 'م',0,1)) . '</span>';
    }
}
