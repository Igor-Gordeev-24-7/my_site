<?php
session_start();
require './includes/db.php'; // Подключение к базе данных

// Проверяем, авторизован ли пользователь
if (!isset($_SESSION['user'])) {
    header('Location: /login.php');
    exit();
}

$userId = $_SESSION['user']['id']; // ID пользователя

// Получаем заказы пользователя
try {
    $stmt = $pdo->prepare("
        SELECT o.id, o.total_amount, o.created_at, o.status, oi.product_id, oi.quantity, oi.price, p.name
        FROM orders o
        JOIN order_items oi ON o.id = oi.order_id
        JOIN products p ON oi.product_id = p.id
        WHERE o.user_id = ?
        ORDER BY o.created_at DESC
    ");
    $stmt->execute([$userId]);
    $orders = $stmt->fetchAll();
} catch (PDOException $e) {
    // Логируем ошибку
    error_log("Ошибка при получении заказов: " . $e->getMessage());
    die("Произошла ошибка при загрузке заказов. Пожалуйста, попробуйте позже.");
}
?>

<!DOCTYPE html>
<html lang="ru">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Мои заказы</title>
	<link rel="stylesheet" href="./assets/css/style.css">
</head>

<body>
	<?php require './includes/prod-els/header.php'; ?>

	<main class="main">
		<section class="orders">
			<div class="orders__container container">
				<h1 class="orders__title">Мои заказы</h1>
				<?php if (empty($orders)): ?>
				<p class="orders__empty">У вас пока нет заказов.</p>
				<?php else: ?>
				<table class="orders__table">
					<thead class="orders__table-header">
						<tr class="orders__table-row">
							<th class="orders__table-cell orders__table-cell--header">Номер заказа</th>
							<th class="orders__table-cell orders__table-cell--header">Товары</th>
							<th class="orders__table-cell orders__table-cell--header">Количество</th>
							<th class="orders__table-cell orders__table-cell--header">Цена</th>
							<th class="orders__table-cell orders__table-cell--header">Сумма</th>
							<th class="orders__table-cell orders__table-cell--header">Дата заказа</th>
							<th class="orders__table-cell orders__table-cell--header">Статус</th>
						</tr>
					</thead>
					<tbody class="orders__table-body">
						<?php foreach ($orders as $order): ?>
						<tr class="orders__table-row">
							<td class="orders__table-cell"><?= htmlspecialchars($order['id']) ?></td>
							<td class="orders__table-cell"><?= htmlspecialchars($order['name']) ?></td>
							<td class="orders__table-cell"><?= htmlspecialchars($order['quantity']) ?></td>
							<td class="orders__table-cell"><?= htmlspecialchars($order['price']) ?> руб.</td>
							<td class="orders__table-cell"><?= htmlspecialchars($order['price'] * $order['quantity']) ?>
								руб.</td>
							<td class="orders__table-cell"><?= htmlspecialchars($order['created_at']) ?></td>
							<td class="orders__table-cell"><?= htmlspecialchars($order['status']) ?></td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
				<?php endif; ?>
			</div>
		</section>
	</main>
</body>

</html>