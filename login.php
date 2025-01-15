<?php
session_start();

// Подключение к базе данных
require 'includes/db.php';

// Обработка данных формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $errors = [];

    // Валидация данных
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Введите корректный email.';
    }
    if (empty($password)) {
        $errors[] = 'Введите пароль.';
    }

    // Если ошибок нет, проверяем данные пользователя
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Сохраняем данные пользователя в сессию
            $_SESSION['user'] = [
                'id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'role' => $user['role'],
            ];

            // Перенаправляем на главную страницу
            header('Location: index.php');
            exit();
        } else {
            $errors[] = 'Неверный email или пароль.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Авторизация</title>
	<link rel="stylesheet" href="./assets/css/style.css">
</head>

<body>

	<main class="main">

		<div class="form">
			<h1 class="form__heading">Авторизация</h1>

			<!-- Вывод ошибок -->
			<?php if (!empty($errors)): ?>
			<div class="errors">
				<?php foreach ($errors as $error): ?>
				<p><?php echo $error; ?></p>
				<?php endforeach; ?>
			</div>
			<?php endif; ?>

			<!-- Форма авторизации -->
			<form class="form__container" action="login.php" method="POST">
				<div class="form__group">
					<label class="form__label" for="email">Email:</label>
					<input class="form__input" type="email" name="email" id="email"
						value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
				</div>

				<div class="form__group">
					<label class="form__label" for="password">Пароль:</label>
					<input class="form__input" type="password" name="password" id="password" required>
				</div>

				<button type="submit" class="form__btn-reg">Войти</button>
			</form>

			<p>Нет аккаунта? <a class="form__btn-redirect" href="register.php">Зарегистрируйтесь</a></p>
		</div>

	</main>

</body>

</html>