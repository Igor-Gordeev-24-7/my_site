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

// Проверяем, были ли отправлены cart_id и quantity
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cart_id']) && isset($_POST['quantity'])) {
    $cartId = (int)$_POST['cart_id']; // ID записи в корзине
    $quantity = (int)$_POST['quantity']; // Новое количество товара
    $userId = $_SESSION['user']['id']; // ID пользователя

    // Проверяем, что количество больше 0
    if ($quantity <= 0) {
        $_SESSION['error'] = "Количество товара должно быть больше 0.";
        header('Location: /cart.php');
        exit();
    }

    try {
        // Обновляем количество товара в корзине
        $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$quantity, $cartId, $userId]);

        // Проверяем, была ли обновлена запись
        if ($stmt->rowCount() > 0) {
            // Количество успешно обновлено
            $_SESSION['message'] = "Количество товара успешно обновлено.";
        } else {
            // Товар не найден или не принадлежит пользователю
            $_SESSION['error'] = "Товар не найден в вашей корзине.";
        }
    } catch (PDOException $e) {
        // Логируем ошибку
        error_log("Ошибка при обновлении количества товара: " . $e->getMessage());
        $_SESSION['error'] = "Произошла ошибка при обновлении количества товара. Пожалуйста, попробуйте позже.";
    }
}

// Перенаправляем пользователя обратно на страницу корзины
header('Location: /cart.php');
exit();
?>