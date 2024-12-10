<?php

require_once('../functions.php');
require_once('../db.php');

// debug($_POST);
// debug($_FILES);
// exit;

$title = $_POST['title'] ?? '';
$price = $_POST['price'] ?? '';

if (mb_strlen($title) == 0 || mb_strlen($price) == 0) {
    $_SESSION['flash']['message']['type'] = 'danger';
    $_SESSION['flash']['message']['text'] = 'Моля попълнете всички полета.';
    header('Location: ../index.php?page=add_product');
    exit;
} else {
    if (isset($_FILES['image']) && $_FILES['image']['error'] == '0') {
        $img_name = time() . "_" . $_FILES['image']['name'];
        $upload_dir = '../uploads/';

        // проверка дали директорията съществува
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0775, true);
        }

        // качване на файла
        if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $img_name)) {
            // запис в базата данни
            $query = "INSERT INTO products (title, image, price) VALUES (:title, :image, :price)";
            $stmt = $pdo->prepare($query);
            $params = [
                ':title' => $title,
                ':image' => $img_name,
                ':price' => $price
            ];
            if ($stmt->execute($params)) {
                $_SESSION['flash']['message']['type'] = 'success';
                $_SESSION['flash']['message']['text'] = 'Продуктът е добавен успешно.';
                header('Location: ../index.php?page=products');
                exit;
            } else {
                $_SESSION['flash']['message']['type'] = 'danger';
                $_SESSION['flash']['message']['text'] = 'Възникна грешка при добавяне на продукта.';
                header('Location: ../index.php?page=add_product');
                exit;
            }
        } else {
            $_SESSION['flash']['message']['type'] = 'danger';
            $_SESSION['flash']['message']['text'] = 'Възникна грешка при качването на файла.';
            header('Location: ../index.php?page=add_product');
            exit;
        }
    } else {
        $_SESSION['flash']['message']['type'] = 'danger';
        $_SESSION['flash']['message']['text'] = 'Моля качете изображение.';
        header('Location: ../index.php?page=add_product');
        exit;
    }
}

?>