<?php 
$titl = "Личный кабинет";
include "header.php";

if (!isset($_SESSION['klient_ID'])) {
    header("Location: index.php");
    exit();
}

// Получаем данные пользователя
$user = $db->query("SELECT * FROM users WHERE klient_ID = {$_SESSION['klient_ID']}")->fetch_assoc();

// Получаем заказы пользователя
$orders = $db->query("SELECT * FROM zakaz WHERE zakazchik = {$_SESSION['klient_ID']} ORDER BY vrema DESC");
?>
<main>
    <div class="zag">Личный кабинет</div>
    
    <div class="profile-info">
        <h2>Мои данные</h2>
        <p><strong>Имя:</strong> <?= $user['name'] ?></p>
        <p><strong>Телефон:</strong> <?= $user['phone'] ?></p>
        <a href="edit_profile.php" class="edit-btn">Редактировать профиль</a>
    </div>
    
    <div class="orders-history">
        <h2>Мои заказы</h2>
        <?php if($orders->num_rows > 0): ?>
        <table class="orders-table">
            <tr>
                <th>№</th>
                <th>Дата</th>
                <th>Адрес</th>
                <th>Сумма</th>
                <th>Статус</th>
                <th></th>
            </tr>
            <?php while($order = $orders->fetch_assoc()): 
                // Получаем сумму заказа
                $sum = $db->query("SELECT SUM(b.cash) as total 
                                  FROM korzina k 
                                  JOIN bludo b ON k.number_bludo = b.bludo_ID 
                                  WHERE k.number_zakaz = {$order['zakaz_ID']}")->fetch_assoc()['total'];
            ?>
            <tr>
                <td><?= $order['zakaz_ID'] ?></td>
                <td><?= date('d.m.Y H:i', strtotime($order['vrema'])) ?></td>
                <td><?= $order['adres'] ?></td>
                <td><?= $sum ?> руб.</td>
                <td><?= $order['status'] ?></td>
                <td><a href="order_details.php?id=<?= $order['zakaz_ID'] ?>">Подробнее</a></td>
            </tr>
            <?php endwhile; ?>
        </table>
        <?php else: ?>
        <p>У вас пока нет заказов</p>
        <?php endif; ?>
    </div>
</main>
<?php include "footer.php"; ?>