<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require './includes/db.php'; // Подключение к базе данных

// Проверяем, авторизован ли пользователь
if (!isset($_SESSION['user'])) {
    header('Location: /login.php');
    exit();
}

$userId = $_SESSION['user']['id'];

// Получаем товары в корзине пользователя
try {
	$stmt = $pdo->prepare("
    SELECT c.id AS cart_id, c.quantity, p.id AS product_id, p.name, p.price, pi.image_url
    FROM cart c
    JOIN products p ON c.product_id = p.id
    LEFT JOIN product_images pi ON p.id = pi.product_id
    WHERE c.user_id = ?
");
$stmt->execute([$userId]);
$cartItems = $stmt->fetchAll();
} catch (PDOException $e) {
    // Логируем ошибку и выводим сообщение
    error_log("Ошибка при получении данных из корзины: " . $e->getMessage());
    die("Произошла ошибка при загрузке корзины. Пожалуйста, попробуйте позже.");
}

?>

<!DOCTYPE html>
<html lang="ru">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Корзина</title>
	<link rel="stylesheet" href="./assets/css/style.css">
</head>

<body>
	<?php require './includes/prod-els/header.php'; ?>

	<main class="main">
		<section class="cart">
			<div class="cart__container container">
				<h1 class="cart__title">Корзина</h1>
				<?php if (empty($cartItems)): ?>
				<p class="cart__empty">Ваша корзина пуста.</p>
				<?php else: ?>
				<table class="cart__table table">
					<thead class="table__header">
						<tr class="table__row">
							<th class="table__cell">Товар</th>
							<th class="table__cell">Количество</th>
							<th class="table__cell">Цена</th>
							<th class="table__cell">Сумма</th>
							<th class="table__cell">Действия</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($cartItems as $item): ?>
						<?php
							// Путь к изображению (с заглушкой, если изображение отсутствует)
							$imageUrl = '/admin/uploads/image.png'; // Заглушка по умолчанию

							if (!empty($item['image_url'])) {
								// Формируем путь к изображению
								$imageName = basename($item['image_url']); // Убедимся, что используем только имя файла
								$imageUrl = '/admin/uploads/' . $imageName;

								// Проверяем существование файла
								$absoluteImagePath = $_SERVER['DOCUMENT_ROOT'] . $imageUrl;
								if (!file_exists($absoluteImagePath)) {
									// Если файл не найден, используем заглушку
									$imageUrl = '/admin/uploads/image.png';
								}
							}
						?>

						<tr class="table__row">
							<td class="table__cell">
								<img class="table__image" src="<?= htmlspecialchars($imageUrl) ?>"
									alt="<?= htmlspecialchars($item['name']) ?>" width="50">
								<?= htmlspecialchars($item['name']) ?>
							</td>
							<td class="table__cell">
								<form action="admin/update_cart.php" method="POST" class="table__actions">
									<input type="hidden" name="cart_id" value="<?= $item['cart_id'] ?>">
									<input type="number" name="quantity" value="<?= $item['quantity'] ?>" min="1">
									<button type="submit" class="table__button">Обновить</button>
								</form>
							</td>
							<td class="table__cell"><?= htmlspecialchars($item['price']) ?> руб.</td>
							<td class="table__cell"><?= htmlspecialchars($item['price'] * $item['quantity']) ?> руб.
							</td>
							<td class="table__cell">
								<form action="admin/remove_from_cart.php" method="POST" class="table__actions">
									<input type="hidden" name="cart_id" value="<?= $item['cart_id'] ?>">
									<button type="submit" class="table__button table__button--delete">Удалить</button>
								</form>
							</td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
				<form action="admin/checkout.php" method="POST" class="cart__checkout">
					<button type="submit" class="cart__checkout-button">Оформить заказ</button>
				</form>
				<?php endif; ?>
			</div>
		</section>
	</main>
</body>

</html>