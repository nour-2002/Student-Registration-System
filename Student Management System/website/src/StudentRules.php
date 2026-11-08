<?php
declare(strict_types=1);
namespace Madar;
defined('MADAR_BOOTED') || exit;

final class StudentRules {
    public static function data(array $input): array {
        $result = [];
        foreach (['name','email','phone','address'] as $field) {
            $result[$field] = is_string($input[$field] ?? null) ? trim($input[$field]) : '';
        }
        return $result;
    }
    public static function errors(array $data): array {
        $errors = [];
        $length = mb_strlen($data['name']);
        if ($length < 2 || $length > 100) $errors['name'] = 'اكتب اسمًا من حرفين إلى 100 حرف.';
        if (mb_strlen($data['email']) > 100 || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = 'اكتب بريدًا إلكترونيًا صحيحًا لا يتجاوز 100 حرف.';
        if (!preg_match('/^\+?[0-9]{7,14}$/D', $data['phone'])) $errors['phone'] = 'اكتب من 7 إلى 14 رقمًا، ويمكن أن تبدأ بعلامة +.';
        if (mb_strlen($data['address']) < 3 || mb_strlen($data['address']) > 2000) $errors['address'] = 'اكتب عنوانًا من 3 إلى 2000 حرف.';
        return $errors;
    }
}
