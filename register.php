<?php
session_start();

// Подключение к базе данных
require 'includes/db.php';

// Обработка данных формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $errors = [];

    // Валидация данных
    if (empty($username)) {
        $errors[] = 'Логин не может быть пустым.';
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Введите корректный email.';
    }
    if (empty($password) || strlen($password) < 6) {
        $errors[] = 'Пароль должен содержать не менее 6 символов.';
    }

    // Проверка, существует ли пользователь с таким email или логином
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? OR username = ?");
        $stmt->execute([$email, $username]);
        $user = $stmt->fetch();

        if ($user) {
            $errors[] = 'Пользователь с таким email или логином уже существует.';
        }
    }

	// Если ошибок нет, сохраняем пользователя в базу данных
	if (empty($errors)) {
		$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
		$stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
		$stmt->execute([$username, $email, $hashedPassword]);

		// Перенаправляем на страницу index.php
		header('Location: index.php');
		exit();
	}
}
?>

<!DOCTYPE html>
<html lang="ru">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Регистрация</title>
	<!-- <link rel="stylesheet" href="assets/css/styles.css">  -->
</head>

<body>
	<div class="container">
		<h1>Регистрация</h1>

		<!-- Вывод ошибок -->
		<?php if (!empty($errors)): ?>
		<div class="errors">
			<?php foreach ($errors as $error): ?>
			<p><?php echo $error; ?></p>
			<?php endforeach; ?>
		</div>
		<?php endif; ?>

		<!-- Форма регистрации -->
		<form action="register.php" method="POST">
			<div class="form-group">
				<label for="username">Логин:</label>
				<input type="text" name="username" id="username"
					value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>" required>
			</div>

			<div class="form-group">
				<label for="email">Email:</label>
				<input type="email" name="email" id="email"
					value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
			</div>

			<div class="form-group">
				<label for="password">Пароль:</label>
				<input type="password" name="password" id="password" required>
			</div>

			<button type="submit" class="btn">Зарегистрироваться</button>
		</form>

		<p>Уже есть аккаунт? <a href="login.php">Войдите</a></p>
	</div>
</body>

</html>