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

// Получение списка товаров
$stmt = $pdo->query("SELECT * FROM products");
$products = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Управление товарами</title>
	<link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>

	<?php require '../includes/prod-els/header.php';?>

	<main>
		<section class="products">
			<h1 class="products__title">Управление товарами</h1>
			<a href="create_product.php" class="products__btn btn">Добавить товар</a>
			<table class="products__table">
				<thead class="products__thead">
					<tr class="products__row">
						<th class="products__header">ID</th>
						<th class="products__header">Название</th>
						<th class="products__header">Цена</th>
						<th class="products__header">Наличие</th>
						<th class="products__header">Дата добавления</th>
						<th class="products__header">Действия</th>
					</tr>
				</thead>
				<tbody class="products__tbody">
					<?php foreach ($products as $product): ?>
					<tr class="products__row">
						<td class="products__cell"><?php echo $product['id']; ?></td>
						<td class="products__cell"><?php echo htmlspecialchars($product['name']); ?></td>
						<td class="products__cell"><?php echo $product['price']; ?> руб.</td>
						<td class="products__cell"><?php echo $product['stock']; ?> шт.</td>
						<td class="products__cell"><?php echo $product['created_at']; ?></td>
						<td class="products__cell products__cell--actions">
							<a href="edit_product.php?id=<?php echo $product['id']; ?>"
								class="products__link">Редактировать</a>
							<a href="delete_product.php?id=<?php echo $product['id']; ?>" class="products__link"
								onclick="return confirm('Вы уверены?')">Удалить</a>
						</td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</section>
	</main>

</body>

</html>