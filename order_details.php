<?php 
$titl = "Детали заказа";
include "header.php";

$order_id = GETI('id');
if (!$order_id) {
    header("Location: index.php");
    exit();
}

// Получаем информацию о заказе
$order = $db->query("SELECT z.*, u.name as user_name 
                    FROM zakaz z 
                    JOIN users u ON z.zakazchik = u.klient_ID 
                    WHERE z.zakaz_ID = $order_id")->fetch_assoc();

if (!$order) {
    header("Location: index.php");
    exit();
}

// Проверка прав доступа
if ((!isset($_SESSION['klient_ID']) || ($_SESSION['klient_ID'] != $order['zakazchik'] && (!isset($_SESSION['admin']) || $_SESSION['admin'] != 2)) {
    header("Location: index.php");
    exit();
}

// Получаем товары в заказе
$items = $db->query("SELECT b.name_eda, b.photo, b.cash 
                    FROM korzina k 
                    JOIN bludo b ON k.number_bludo = b.bludo_ID 
                    WHERE k.number_zakaz = $order_id");
$total = 0;
?>
<main>
    <div class="zag">Детали заказа №<?= $order_id ?></div>
    
    <div class="order-details">
        <div class="order-info">
            <p><strong>Дата:</strong> <?= date('d.m.Y H:i', strtotime($order['vrema'])) ?></p>
            <p><strong>Статус:</strong> <?= $order['status'] ?></p>
            <p><strong>Способ оплаты:</strong> <?= $order['oplata'] ?></p>
            <p><strong>Адрес доставки:</strong> <?= $order['adres'] ?></p>
            <p><strong>Комментарий:</strong> <?= $order['komment'] ?></p>
        </div>
        
        <div class="order-items">
            <h3>Состав заказа:</h3>
            <?php while($item = $items->fetch_assoc()): 
                $total += $item['cash'];
            ?>
            <div class="order-item">
                <img src="<?= $item['photo'] ?>" alt="<?= $item['name_eda'] ?>">
                <div class="item-info">
                    <h4><?= $item['name_eda'] ?></h4>
                    <p><?= $item['cash'] ?> руб.</p>
                </div>
            </div>
            <?php endwhile; ?>
            
            <div class="order-total">
                <strong>Итого:</strong> <?= $total ?> руб.
            </div>
        </div>
    </div>
</main>
<?php include "footer.php"; ?>