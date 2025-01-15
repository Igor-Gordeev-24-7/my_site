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

$userId = $_SESSION['user']['id']; // ID пользователя

try {
    // Начинаем транзакцию
    $pdo->beginTransaction();

    // Получаем товары из корзины пользователя
    $stmt = $pdo->prepare("
        SELECT c.product_id, c.quantity, p.price
        FROM cart c
        JOIN products p ON c.product_id = p.id
        WHERE c.user_id = ?
    ");
    $stmt->execute([$userId]);
    $cartItems = $stmt->fetchAll();

    if (empty($cartItems)) {
        // Если корзина пуста, перенаправляем обратно
        $_SESSION['error'] = "Ваша корзина пуста.";
        header('Location: /cart.php');
        exit();
    }

    // Рассчитываем общую сумму заказа
    $totalAmount = 0;
    foreach ($cartItems as $item) {
        $totalAmount += $item['price'] * $item['quantity'];
    }

    // Создаем заказ
    $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_amount) VALUES (?, ?)");
    $stmt->execute([$userId, $totalAmount]);
    $orderId = $pdo->lastInsertId();

    // Добавляем товары в заказ
    foreach ($cartItems as $item) {
        $stmt = $pdo->prepare("
            INSERT INTO order_items (order_id, product_id, quantity, price)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$orderId, $item['product_id'], $item['quantity'], $item['price']]);
    }

    // Очищаем корзину пользователя
    $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
    $stmt->execute([$userId]);

    // Завершаем транзакцию
    $pdo->commit();

    // Устанавливаем сообщение об успешном оформлении заказа
    $_SESSION['message'] = "Заказ успешно оформлен! Номер вашего заказа: #" . $orderId;

    // Перенаправляем на страницу подтверждения заказа
    header('Location: /order_confirmation.php');
    exit();
} catch (PDOException $e) {
    // Откатываем транзакцию в случае ошибки
    $pdo->rollBack();

    // Логируем ошибку
    error_log("Ошибка при оформлении заказа: " . $e->getMessage());

    // Устанавливаем сообщение об ошибке
    $_SESSION['error'] = "Произошла ошибка при оформлении заказа. Пожалуйста, попробуйте позже.";

    // Перенаправляем обратно в корзину
    header('Location: /cart.php');
    exit();
}
?>