<?php
    // страница продукти
    $products = [];
    $search = '';
    $where_search = '';
    $params = [];
    if (isset($_GET['search']) && mb_strlen($_GET['search']) > 0) {
       $search = $_GET['search'];
       $where_search = 'WHERE title LIKE :search';
       $params = [':search' => '%' . $search . '%'];

       setcookie('last_search', $search, time() + 3600, '/', 'localhost', false, false);
    }

    $query = "
        SELECT *
        FROM products
        $where_search
    ";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);

    while ($row = $stmt->fetch()) {
        $fav_query = "SELECT id FROM `favorite_products_users` WHERE user_id = :user_id AND product_id = :product_id";
        $fav_stmt = $pdo->prepare($fav_query);
        $fav_stmt->execute([
            ':user_id' => $_SESSION['user_id'] ?? 0,
            ':product_id' => $row['id']
        ]);
        $fav_product = $fav_stmt->fetch();
        $row['is_favorite'] = $fav_product ? 1 : 0;
        $products[] = $row;
    }
?>

<div class="row">
    <form class="mb-4" method="GET">
        <div class="input-group">
            <input type="hidden" name="page" value="products">
            <input type="text" class="form-control" name="search" placeholder="Търси продукт" value="<?php echo $search ?>">
            <button class="btn btn-primary" type="submit">Търсене</button>
        </div>
    </form>

    <?php
        if (isset($_COOKIE['last_search'])) {
            echo '<p>Последно търсене: ' . $_COOKIE['last_search'] . '</p>';
        }
    ?>
</div>
<div class="d-flex flex-wrap justify-content-evenly">
    <?php
        foreach ($products as $product) {
            $fav_button = '';
            if (isset($_SESSION['user_id'])) {
                if ($product['is_favorite'] == '1') {
                    $fav_button = '
                        <div class="card-footer">
                            <button class="btn btn-sm btn-danger remove-favorite" data-product="' . htmlspecialchars($product['id']) . '">Премахни от любими</button>
                        </div>
                    ';
                } else {
                    $fav_button = '
                        <div class="card-footer">
                            <button class="btn btn-sm btn-primary add-favorite" data-product="' . htmlspecialchars($product['id']) . '">Добави в любими</button>
                        </div>
                    ';
                }
            }

            echo '
                <div class="card mb-4" style="width: 18rem;">
                    <div class="d-flex flex-row justify-content-between">
                        <a class="btn btn-sm btn-warning" href="?page=edit_product&product_id=' . $product['id'] . '">Редактирай</a>
                        <form method="POST" action="./handlers/handle_delete_product.php">
                            <input type="hidden" name="product_id" value="' . $product['id'] . '">
                            <button class="btn btn-sm btn-danger" type="submit">Изтрий</button>
                        </form>
                    </div>
                    <img src="uploads/' . htmlspecialchars($product['image']) . '" class="card-img-top" alt="Product Image">
                    <div class="card-body">
                        <h5 class="card-title">' . htmlspecialchars($product['title']) . '</h5>
                        <p class="card-text">' . htmlspecialchars($product['price']) . '</p>
                    </div>
                    ' . $fav_button . '
                </div>
            ';
        }

    ?>
</div>