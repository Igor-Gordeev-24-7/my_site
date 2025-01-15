<?php
session_start();
require '../includes/db.php';

header('Content-Type: application/json'); // Указываем, что возвращаем JSON

// Проверяем, авторизован ли пользователь
if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Пользователь не авторизован']);
    exit();
}

// Проверяем, был ли отправлен запрос на добавление товара
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $userId = $_SESSION['user']['id'];
    $productId = (int)$_POST['product_id'];
    $quantity = (int)$_POST['quantity'];

    // Проверяем, есть ли товар уже в корзине
    $stmt = $pdo->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$userId, $productId]);
    $existingItem = $stmt->fetch();

    if ($existingItem) {
        // Обновляем количество, если товар уже в корзине
        $newQuantity = $existingItem['quantity'] + $quantity;
        $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
        $stmt->execute([$newQuantity, $existingItem['id']]);
    } else {
        // Добавляем новый товар в корзину
        $stmt = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
        $stmt->execute([$userId, $productId, $quantity]);
    }

    // Получаем общее количество товаров в корзине
    $stmt = $pdo->prepare("SELECT SUM(quantity) AS total FROM cart WHERE user_id = ?");
    $stmt->execute([$userId]);
    $result = $stmt->fetch();

    // Возвращаем успешный ответ
    echo json_encode(['success' => true, 'cartCount' => $result['total'] ?? 0]);
} else {
    // Если запрос неверный, возвращаем ошибку
    echo json_encode(['success' => false, 'message' => 'Неверный запрос']);
}
?>