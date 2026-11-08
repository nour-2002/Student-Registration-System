<?php
declare(strict_types=1);
namespace Madar;
defined('MADAR_BOOTED') || exit;

final class StudentEditor {
    public static function show(?int $id = null): void {
        $repository = new StudentRepository();
        $existing = $id ? $repository->find($id) : null;
        if ($id && !$existing) { http_response_code(404); View::missing(); }
        $data = $existing ?? ['name' => '', 'email' => '', 'phone' => '', 'address' => ''];
        $errors = [];
        if (\is_post()) {
            \check_csrf(); $data = StudentRules::data($_POST); $errors = StudentRules::errors($data);
            if (!$errors) {
                $photo = null;
                try {
                    $photo = PhotoStore::accept($_FILES['picture'] ?? null);
                    if ($id) {
                        if (!$repository->update($id, $data, $photo, isset($_POST['remove_photo']))) {
                            PhotoStore::remove($photo); http_response_code(404); View::missing();
                        }
                        \flash('تم حفظ تعديلات الطالب.');
                    } else {
                        $repository->create($data, $photo); \flash('تم تسجيل الطالب بنجاح.');
                    }
                    \go('view_students.php');
                } catch (\InvalidArgumentException $error) { $errors['picture'] = $error->getMessage(); }
                catch (\Throwable $error) { PhotoStore::remove($photo); throw $error; }
            }
            http_response_code(422);
        }
        View::start($id ? 'تعديل بيانات الطالب' : 'تسجيل طالب', $id ? 'students' : 'add');
        require dirname(__DIR__) . '/templates/student-form.php';
        View::end();
    }
}
