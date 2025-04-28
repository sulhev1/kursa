<?php 
$titl = "Корзина";
include "header.php";

if (!isset($_SESSION['klient_ID'])) {
    header("Location: index.php");
    exit();
}

// Добавление товара
if (isset($_GET['add'])) {
    $bludo_ID = GETI('add');
    $sql = "INSERT INTO korzina (number_bludo, zakazchik) VALUES ($bludo_ID, {$_SESSION['klient_ID']})";
    $db->query($sql);
    header("Location: korzinka.php");
    exit();
}

// Удаление товара
if (isset($_GET['remove'])) {
    $korzina_ID = GETI('remove');
    $db->query("DELETE FROM korzina WHERE korzina_ID = $korzina_ID AND zakazchik = {$_SESSION['klient_ID']}");
    header("Location: korzinka.php");
    exit();
}

// Оформление заказа
if (isset($_POST['order'])) {
    $adres = POSTS('adres');
    $oplata = POSTS('oplata');
    $komment = POSTS('komment');
    
    // Создаем заказ
    $db->query("INSERT INTO zakaz (adres, oplata, komment, zakazchik, vrema, status) 
               VALUES ('$adres', '$oplata', '$komment', {$_SESSION['klient_ID']}, NOW(), 'Новый')");
    $order_id = $db->insert_id;
    
    // Привязываем товары к заказу
    $db->query("UPDATE korzina SET number_zakaz = $order_id WHERE number_zakaz = 0 AND zakazchik = {$_SESSION['klient_ID']}");
    
    header("Location: lichniy_kabinet.php");
    exit();
}

// Получаем содержимое корзины
$cart = $db->query("SELECT k.*, b.name_eda, b.photo, b.cash 
                   FROM korzina k 
                   JOIN bludo b ON k.number_bludo = b.bludo_ID 
                   WHERE k.number_zakaz = 0 AND k.zakazchik = {$_SESSION['klient_ID']}");
$total = 0;
?>
<main>
    <div class="zag">Корзина</div>
    <?php if($cart->num_rows > 0): ?>
    <div class="cart-container">
        <?php while($item = $cart->fetch_assoc()): 
            $total += $item['cash'];
        ?>
        <div class="cart-item">
            <img src="<?= $item['photo'] ?>" alt="<?= $item['name_eda'] ?>">
            <div class="cart-item-info">
                <h3><?= $item['name_eda'] ?></h3>
                <p><?= $item['cash'] ?> руб.</p>
            </div>
            <a href="?remove=<?= $item['korzina_ID'] ?>" class="remove-btn">×</a>
        </div>
        <?php endwhile; ?>
        
        <div class="cart-total">
            Итого: <?= $total ?> руб.
        </div>
        
        <form method="POST" class="order-form">
            <input type="text" name="adres" placeholder="Адрес доставки" required>
            <select name="oplata" required>
                <option value="Наличные">Наличные</option>
                <option value="Карта">Карта</option>
            </select>
            <textarea name="komment" placeholder="Комментарий к заказу"></textarea>
            <button type="submit" name="order" class="order-btn">Оформить заказ</button>
        </form>
    </div>
    <?php else: ?>
    <div class="empty-cart">Корзина пуста</div>
    <?php endif; ?>
</main>
<?php include "footer.php"; ?>