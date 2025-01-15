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

// Получение ID товара
$id = $_GET['id'] ?? null;

if (!$id) {
    header('Location: products.php');
    exit();
}

// Получение данных товара
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    header('Location: products.php');
    exit();
}

// Получение фотографий товара
$stmt = $pdo->prepare("SELECT * FROM product_images WHERE product_id = ?");
$stmt->execute([$id]);
$images = $stmt->fetchAll();

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

    // Если ошибок нет, обновляем товар
    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE products SET name = ?, description = ?, price = ?, stock = ? WHERE id = ?");
        $stmt->execute([$name, $description, $price, $stock, $id]);

        // Обработка загрузки новых фотографий
        if (!empty($_FILES['images']['name'][0])) {
            foreach ($_FILES['images']['tmp_name'] as $key => $tmpName) {
                $imageName = basename($_FILES['images']['name'][$key]);
                $imagePath = 'uploads/' . uniqid() . '_' . $imageName;

                if (move_uploaded_file($tmpName, $imagePath)) {
                    $stmt = $pdo->prepare("INSERT INTO product_images (product_id, image_url) VALUES (?, ?)");
                    $stmt->execute([$id, $imagePath]);
                }
            }
        }

        header('Location: products.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="ru">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Редактировать товар</title>
	<link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
	<?php require '../includes/prod-els/header.php';?>

	<main>

		<section class="edit-product">
			<div class="edit-product__container container">
				<h1 class="edit-product__title">Редактировать товар</h1>
				<?php if (!empty($errors)): ?>
				<div class="edit-product__errors">
					<?php foreach ($errors as $error): ?>
					<p class="edit-product__error"><?php echo $error; ?></p>
					<?php endforeach; ?>
				</div>
				<?php endif; ?>
				<form action="edit_product.php?id=<?php echo $id; ?>" method="POST" enctype="multipart/form-data"
					class="edit-product__form">
					<div class="edit-product__form-group">
						<label for="name" class="edit-product__label">Название:</label>
						<input type="text" name="name" id="name" class="edit-product__input"
							value="<?php echo htmlspecialchars($product['name']); ?>" required>
					</div>
					<div class="edit-product__form-group">
						<label for="description" class="edit-product__label">Описание:</label>
						<textarea name="description" id="description" rows="5"
							class="edit-product__textarea"><?php echo htmlspecialchars($product['description']); ?></textarea>
					</div>
					<div class="edit-product__form-group">
						<label for="price" class="edit-product__label">Цена:</label>
						<input type="number" name="price" id="price" step="0.01" class="edit-product__input"
							value="<?php echo $product['price']; ?>" required>
					</div>
					<div class="edit-product__form-group">
						<label for="stock" class="edit-product__label">Наличие:</label>
						<input type="number" name="stock" id="stock" class="edit-product__input"
							value="<?php echo $product['stock']; ?>" required>
					</div>
					<div class="edit-product__form-group">
						<label for="images" class="edit-product__label">Добавить фотографии:</label>
						<input type="file" name="images[]" id="images" class="edit-product__file" multiple>
					</div>
					<div class="edit-product__form-group">
						<label class="edit-product__label">Текущие фотографии:</label>
						<?php foreach ($images as $image): ?>
						<div class="edit-product__image">
							<img src="<?php echo $image['image_url']; ?>" alt="Фото товара" class="edit-product__img"
								width="100">
							<a href="delete_image.php?id=<?php echo $image['id']; ?>" class="edit-product__link"
								onclick="return confirm('Вы уверены?')">Удалить</a>
						</div>
						<?php endforeach; ?>
					</div>
					<button type="submit" class="edit-product__btn btn">Сохранить</button>
				</form>
			</div>
		</section>

	</main>

</body>

</html>