<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Проверка роли пользователя
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: /login.php');
    exit();
}

// Подключение к базе данных
require '../includes/db.php';

// Получение ID фотографии
$id = $_GET['id'] ?? null;

if ($id) {
    // Получение пути к файлу
    $stmt = $pdo->prepare("SELECT image_url FROM product_images WHERE id = ?");
    $stmt->execute([$id]);
    $image = $stmt->fetch();

    if ($image && file_exists($image['image_url'])) {
        unlink($image['image_url']);
    }

    // Удаление записи из базы данных
    $stmt = $pdo->prepare("DELETE FROM product_images WHERE id = ?");
    $stmt->execute([$id]);
}

header('Location: ' . $_SERVER['HTTP_REFERER']);
exit();
?>