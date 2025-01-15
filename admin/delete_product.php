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

// Получение ID товара
$id = $_GET['id'] ?? null;

if ($id) {
    // Удаление фотографий товара
    $stmt = $pdo->prepare("SELECT image_url FROM product_images WHERE product_id = ?");
    $stmt->execute([$id]);
    $images = $stmt->fetchAll();

    foreach ($images as $image) {
        if (file_exists($image['image_url'])) {
            unlink($image['image_url']);
        }
    }

    // Удаление товара
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$id]);
}

header('Location: products.php');
exit();
?>