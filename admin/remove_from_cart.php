<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require '../includes/db.php'; // Подключение к базе данных

// Проверяем, авторизован ли пользователь
if (!isset($_SESSION['user'])) {
    header('Location: /login.php');
    exit();
}

// Проверяем, был ли отправлен cart_id
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cart_id'])) {
    $cartId = (int)$_POST['cart_id']; // ID записи в корзине
    $userId = $_SESSION['user']['id']; // ID пользователя

    try {
        // Удаляем товар из корзины
        $stmt = $pdo->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
        $stmt->execute([$cartId, $userId]);

        // Проверяем, была ли удалена запись
        if ($stmt->rowCount() > 0) {
            // Товар успешно удалён
            $_SESSION['message'] = "Товар успешно удалён из корзины.";
        } else {
            // Товар не найден или не принадлежит пользователю
            $_SESSION['error'] = "Товар не найден в вашей корзине.";
        }
    } catch (PDOException $e) {
        // Логируем ошибку
        error_log("Ошибка при удалении товара из корзины: " . $e->getMessage());
        $_SESSION['error'] = "Произошла ошибка при удалении товара. Пожалуйста, попробуйте позже.";
    }
}

// Перенаправляем пользователя обратно на страницу корзины
header('Location: /cart.php');
exit();
?>