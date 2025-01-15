<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

// Вывод сообщения об успешной регистрации
if (isset($_SESSION['success_message'])) {
    echo '<div class="success-message">' . $_SESSION['success_message'] . '</div>';
    unset($_SESSION['success_message']);
}

// Подключение к базе данных
require './includes/db.php';

// Получение списка товаров
try {
    $stmt = $pdo->query("SELECT * FROM products");
    $products = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Ошибка при получении данных из базы данных: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="ru">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Главная страница</title>
	<link rel="stylesheet" href="./assets/css/style.css">
	<style>
	.success-message {
		background-color: #d4edda;
		color: #155724;
		padding: 10px;
		margin: 20px;
		border-radius: 5px;
		text-align: center;
	}
	</style>
</head>

<body>

	<?php include './includes/prod-els/header.php'; ?>

	<main class="main">
		<section class="items">
			<div class="items__wrapper wrapper">
				<div class="items__cards">
					<?php if (!empty($products)): ?>
					<?php
                // Путь к заглушке
                $placeholderPath = './admin/uploads/image.png';
                ?>
					<?php foreach ($products as $product): ?>
					<div class="items__card">
						<?php
                        // Получение первой фотографии товара
                        $stmt = $pdo->prepare("SELECT image_url FROM product_images WHERE product_id = ? LIMIT 1");
                        $stmt->execute([$product['id']]);
                        $image = $stmt->fetch();

                        // Формируем путь к изображению
                        $imageUrl = $image ? '/admin/uploads/' . $image['image_url'] : $placeholderPath;

                        // Проверка существования файла
                        if ($image && !file_exists($_SERVER['DOCUMENT_ROOT'] . $imageUrl)) {
                            $imageUrl = $placeholderPath; // Используем заглушку, если файл не найден
                        }
                        ?>
						<img class="items__card-img" src="<?php echo $imageUrl; ?>"
							alt="<?php echo htmlspecialchars($product['name']); ?>">
						<div class="items__cards-info">
							<div class="items__cards-info-box">
								<h2 class="items__cards-name"><?php echo htmlspecialchars($product['name']); ?></h2>
								<p class="items__cards-cost"><?php echo $product['price']; ?> руб.</p>
							</div>
							<p class="items__cards-description">
								<?php
                                // Обрезаем описание до 50 символов и добавляем многоточие, если текст длиннее
                                $description = htmlspecialchars($product['description']);
                                echo mb_substr($description, 0, 50, 'UTF-8') . (mb_strlen($description, 'UTF-8') > 50 ? '...' : '');
                                ?>
							</p>
						</div>
						<!-- Форма для добавления товара в корзину -->
						<form class="add-to-cart-form" data-product-id="<?php echo $product['id']; ?>">
							<input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
							<label for="quantity-<?php echo $product['id']; ?>">Количество:</label>
							<input type="number" name="quantity" id="quantity-<?php echo $product['id']; ?>" value="1"
								min="1">
							<button type="submit" class="add-to-cart-button">Добавить в корзину</button>
						</form>
					</div>
					<?php endforeach; ?>
					<?php else: ?>
					<p>Товары не найдены.</p>
					<?php endif; ?>
				</div>
			</div>
		</section>
	</main>
	<script>
	document.addEventListener("DOMContentLoaded", function() {
		const cartCounter = document.getElementById("cart-counter");

		// Обработка всех форм добавления в корзину
		document.querySelectorAll(".add-to-cart-form").forEach((form) => {
			form.addEventListener("submit", function(event) {
				event.preventDefault(); // Отменяем стандартное поведение формы

				const productId = form.dataset.productId;
				const quantity = form.querySelector('input[name="quantity"]').value;

				// Отправляем данные на сервер
				fetch("./includes/add_to_cart.php", {
						method: "POST",
						headers: {
							"Content-Type": "application/x-www-form-urlencoded",
						},
						body: `product_id=${productId}&quantity=${quantity}`,
					})
					.then((response) => response.json())
					.then((data) => {
						if (data.success) {
							// Обновляем счётчик товаров в корзине
							cartCounter.textContent = data.cartCount;
							alert("Товар добавлен в корзину!");
						} else {
							alert("Ошибка: " + data.message);
						}
					})
					.catch((error) => {
						console.error("Ошибка:", error);
					});
			});
		});
	});
	</script>
</body>

</html>