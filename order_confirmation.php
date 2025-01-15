<?php
session_start();
require './includes/db.php'; // Подключение к базе данных

// Проверяем, авторизован ли пользователь
if (!isset($_SESSION['user'])) {
    header('Location: /login.php');
    exit();
}

// Отображение сообщений об успешном оформлении заказа
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
} else {
    // Если сообщения нет, перенаправляем обратно
    header('Location: /cart.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="ru">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Подтверждение заказа</title>
	<link rel="stylesheet" href="./assets/css/style.css">
</head>

<body>
	<?php require './includes/prod-els/header.php'; ?>

	<main class="main">
		<section class="order-confirmation">
			<div class="order-confirmation__container container">
				<h1 class="order-confirmation__title">Подтверждение заказа</h1>
				<p class="order-confirmation__message"><?= htmlspecialchars($message) ?></p>
				<a href="/" class="order-confirmation__button button">Вернуться на главную</a>
			</div>
		</section>
	</main>
</body>

</html>