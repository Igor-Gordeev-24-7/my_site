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
?>

<!DOCTYPE html>
<html lang="ru">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Главная страница</title>
	<link rel="stylesheet" href="assets/css/styles.css">
</head>

<body>
	<div class="container">
		<h1>Добро пожаловать, <?php echo htmlspecialchars($_SESSION['user']['username']); ?>!</h1>
		<p>Вы успешно авторизованы.</p>
		<a href="logout.php">Выйти</a>
	</div>
</body>

</html>