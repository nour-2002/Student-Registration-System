<?php
declare(strict_types=1);
require __DIR__ . '/src/bootstrap.php'; user_required(true);
$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$student = $id ? (new Madar\StudentRepository())->find($id) : null;
$name = $student['profile_picture'] ?? '';
if (!preg_match('/^[a-f0-9]{40}\.(jpg|png|webp)$/D', $name) || !is_file(Madar\PhotoStore::directory().'/'.$name)) { http_response_code(404); exit; }
$mime = ['jpg'=>'image/jpeg','png'=>'image/png','webp'=>'image/webp'][pathinfo($name,PATHINFO_EXTENSION)];
header('Content-Type: '.$mime); header('Content-Length: '.filesize(Madar\PhotoStore::directory().'/'.$name));
session_write_close(); readfile(Madar\PhotoStore::directory().'/'.$name);
