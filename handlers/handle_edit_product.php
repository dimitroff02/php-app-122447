<?php

require_once('../functions.php');
require_once('../db.php');

$product_id = intval($_POST['product_id'] ?? 0);
$title = $_POST['title'] ?? '';
$price = $_POST['price'] ?? '';

if ($product_id <= 0 || mb_strlen($title) == 0 || mb_strlen($price) == 0) {
    $_SESSION['flash']['message']['type'] = 'danger';
    $_SESSION['flash']['message']['text'] = 'Моля попълнете всички полета.';
    header('Location: ../index.php?page=edit_product&product_id=' . $product_id);
    exit;
} else {
    $img_upload = false;
    if (isset($_FILES['image']) && $_FILES['image']['error'] == '0') {
        $img_name = time() . "_" . $_FILES['image']['name'];
        $upload_dir = '../uploads/';

        // проверка дали директорията съществува
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0775, true);
        }

        // качване на файла
        if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $img_name)) {
            $img_upload = true;
        } else {
            $_SESSION['flash']['message']['type'] = 'danger';
            $_SESSION['flash']['message']['text'] = 'Възникна грешка при качването на файла.';
            header('Location: ../index.php?page=edit_product&product_id=' . $product_id);
            exit;
        }
    }

    // запис в базата данни
    $query = '';
    if ($img_upload) {
        $query = "
            UPDATE products
            SET title = :title, price = :price, image = :image
            WHERE id = :id
        ";
    } else {
        $query = "
            UPDATE products
            SET title = :title, price = :price
            WHERE id = :id
        ";
    }
    $stmt = $pdo->prepare($query);
    $params = [
        ':title' => $title,
        ':price' => $price,
        ':id' => $product_id
    ];

    if ($img_upload) {
        $params[':image'] = $img_name;
    }

    if ($stmt->execute($params)) {
        $_SESSION['flash']['message']['type'] = 'success';
        $_SESSION['flash']['message']['text'] = 'Продуктът е редактиран успешно.';
    } else {
        $_SESSION['flash']['message']['type'] = 'danger';
        $_SESSION['flash']['message']['text'] = 'Възникна грешка при редактиране на продукта.';
    }
    header('Location: ../index.php?page=edit_product&product_id=' . $product_id);
    exit;
}

?>