<?php
session_start();
// Проверка роли пользователя
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: /login.php');
    exit();
}

// Подключение к базе данных
require '../includes/db.php';

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = (float)$_POST['price'];
    $stock = (int)$_POST['stock'];
    $errors = [];

    // Валидация данных
    if (empty($name)) {
        $errors[] = 'Название товара не может быть пустым.';
    }
    if ($price <= 0) {
        $errors[] = 'Цена должна быть больше нуля.';
    }
    if ($stock < 0) {
        $errors[] = 'Количество товара не может быть отрицательным.';
    }

    // Если ошибок нет, сохраняем товар
    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO products (name, description, price, stock) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $description, $price, $stock]);
        $productId = $pdo->lastInsertId();

        // Обработка загрузки фотографий
        if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
            // Абсолютный путь к папке uploads
            $uploadDir = __DIR__ . '/uploads/'; // Папка uploads в директории admin

            // Создаем папку uploads, если она не существует
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            foreach ($_FILES['images']['tmp_name'] as $key => $tmpName) {
                if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                    if (in_array($_FILES['images']['type'][$key], $allowedTypes)) {
                        $imageName = basename($_FILES['images']['name'][$key]);
                        $imagePath = $uploadDir . $imageName; // Абсолютный путь для сохранения файла

                        // Проверяем, существует ли файл с таким именем
                        if (file_exists($imagePath)) {
                            $errors[] = 'Файл с именем ' . $imageName . ' уже существует.';
                            continue;
                        }

                        // Пытаемся переместить файл
                        if (move_uploaded_file($tmpName, $imagePath)) {
                            // Сохраняем только имя файла в базу данных
                            $stmt = $pdo->prepare("INSERT INTO product_images (product_id, image_url) VALUES (?, ?)");
                            $stmt->execute([$productId, $imageName]);
                        } else {
                            $errors[] = 'Не удалось загрузить файл ' . $imageName;
                        }
                    } else {
                        $errors[] = 'Недопустимый тип файла ' . $_FILES['images']['name'][$key];
                    }
                } else {
                    $errors[] = 'Ошибка при загрузке файла ' . $_FILES['images']['name'][$key];
                }
            }
        }

        if (empty($errors)) {
            header('Location: products.php');
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Добавить товар</title>
	<link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>

	<?php require '../includes/prod-els/header.php';?>

	<section class="create-product">
		<div class="create-product__container container">
			<h1 class="create-product__title">Добавить товар</h1>
			<?php if (!empty($errors)): ?>
			<div class="create-product__errors">
				<?php foreach ($errors as $error): ?>
				<p class="create-product__error"><?php echo $error; ?></p>
				<?php endforeach; ?>
			</div>
			<?php endif; ?>
			<form action="create_product.php" method="POST" enctype="multipart/form-data" class="create-product__form">
				<div class="create-product__form-group">
					<label for="name" class="create-product__label">Название:</label>
					<input type="text" name="name" id="name" class="create-product__input" required>
				</div>
				<div class="create-product__form-group">
					<label for="description" class="create-product__label">Описание:</label>
					<textarea name="description" id="description" rows="5" class="create-product__textarea"></textarea>
				</div>
				<div class="create-product__form-group">
					<label for="price" class="create-product__label">Цена:</label>
					<input type="number" name="price" id="price" step="0.01" class="create-product__input" required>
				</div>
				<div class="create-product__form-group">
					<label for="stock" class="create-product__label">Наличие:</label>
					<input type="number" name="stock" id="stock" class="create-product__input" required>
				</div>
				<div class="create-product__form-group">
					<label for="images" class="create-product__label">Фотографии:</label>
					<input type="file" name="images[]" id="images" class="create-product__file" multiple>
				</div>
				<button type="submit" class="create-product__btn btn">Добавить</button>
			</form>
		</div>
	</section>

</body>

</html>