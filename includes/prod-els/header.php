<?php
// Получаем базовый путь к корню проекта
$baseDir = dirname(__DIR__); // Поднимаемся на один уровень вверх от текущей директории

// Создаем ссылки
$homeUrl = '/index.php';
$logoutUrl = '/logout.php';
$ordersUrl = '/orders.php';
$cartUrl = '/cart.php';
$adminProductsUrl = '/admin/products.php';

// Если нужно, можно добавить базовый URL (например, для подпапок)
$baseUrl = ''; // Оставьте пустым, если проект находится в корне сервера
?>

<header class="header">
	<div class="header__wrapper wrapper">
		<a href="<?php echo $baseUrl . $homeUrl; ?>" class="header__logo">ЭЛЕКТРО-МАРКЕТ</a>
		<div class="header__nav">
			<!-- Ссылка на главную страницу -->
			<a href="<?php echo $baseUrl . $homeUrl; ?>" class="header__nav-item">Главная</a>

			<!-- Имя пользователя -->
			<a href="#" class="header__nav-item"><?php echo htmlspecialchars($_SESSION['user']['username']); ?></a>

			<!-- Проверка роли пользователя -->
			<?php if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin'): ?>
			<a href="<?php echo $baseUrl . $adminProductsUrl; ?>" class="header__nav-item">Админка</a>
			<?php endif; ?>

			<!-- Ссылка на заказы -->
			<a href="<?php echo $baseUrl . $ordersUrl; ?>" class="header__nav-item">Заказы</a>

			<!-- Ссылка на корзину с количеством товаров -->
			<a href="<?php echo $baseUrl . $cartUrl; ?>" class="header__nav-item">
				Корзина
				<span id="cart-counter">
					<?php
                    // Получаем количество товаров в корзине
                    if (isset($_SESSION['user'])) {
                        $userId = $_SESSION['user']['id'];
                        $stmt = $pdo->prepare("SELECT SUM(quantity) AS total FROM cart WHERE user_id = ?");
                        $stmt->execute([$userId]);
                        $result = $stmt->fetch();
                        echo $result['total'] ?? 0;
                    } else {
                        echo 0;
                    }
                    ?>
				</span>
			</a>

			<!-- Ссылка на выход -->
			<a href="<?php echo $baseUrl . $logoutUrl; ?>" class="header__nav-item">Выйти</a>
		</div>
	</div>
</header>