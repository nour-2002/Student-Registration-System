<?php
declare(strict_types=1);
namespace Madar;
defined('MADAR_BOOTED') || exit;

final class PhotoStore {
    public const LIMIT = 2097152;
    public static function directory(): string { return dirname(__DIR__) . '/storage/photos'; }
    public static function accept(?array $file): ?string {
        if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return null;
        if (!isset($file['error']) || is_array($file['error']) || $file['error'] !== UPLOAD_ERR_OK) throw new \InvalidArgumentException('لم يكتمل رفع الصورة. اختر الملف من جديد.');
        if ($file['size'] > self::LIMIT || $file['size'] < 1) throw new \InvalidArgumentException('يجب ألا يتجاوز حجم الصورة 2 ميغابايت.');
        $temporary = $file['tmp_name'];
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($temporary);
        $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        $size = @getimagesize($temporary);
        if (!isset($allowed[$mime]) || !$size || ($size['mime'] ?? '') !== $mime) throw new \InvalidArgumentException('اختر صورة حقيقية بصيغة JPG أو PNG أو WebP.');
        if ($size[0] > 6000 || $size[1] > 6000 || $size[0] * $size[1] > 20000000) throw new \InvalidArgumentException('أبعاد الصورة كبيرة. استخدم صورة أصغر من 6000 بكسل و20 مليون بكسل إجمالًا.');
        if (!is_writable(self::directory())) throw new \RuntimeException('Photo directory is not writable');
        $name = bin2hex(random_bytes(20)) . '.' . $allowed[$mime];
        if (!move_uploaded_file($temporary, self::directory() . '/' . $name)) throw new \RuntimeException('Photo upload failed');
        return $name;
    }
    public static function remove(?string $name): void {
        if ($name && preg_match('/^[a-f0-9]{40}\.(jpg|png|webp)$/D', $name)) {
            $path = self::directory() . '/' . $name;
            if (is_file($path) && !unlink($path)) error_log('Madar: could not remove obsolete photo');
        }
    }
}
