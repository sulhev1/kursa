<?php 
$titl = "Управление заказами";
include "header.php";

// Проверка прав администратора
if (!isset($_SESSION['admin']) || $_SESSION['admin'] != 2) {
    header("Location: index.php");
    exit();
}

// Обновление статуса заказа
if (isset($_POST['update_status'])) {
    $order_id = POSTI('order_id');
    $status = POSTS('status');
    $db->query("UPDATE zakaz SET status = '$status' WHERE zakaz_ID = $order_id");
}

// Получаем все заказы
$orders = $db->query("SELECT z.*, u.name as user_name 
                     FROM zakaz z 
                     JOIN users u ON z.zakazchik = u.klient_ID 
                     ORDER BY z.vrema DESC");
?>
<main>
    <div class="zag">Управление заказами</div>
    
    <div class="admin-orders">
        <?php if($orders->num_rows > 0): ?>
        <table class="orders-table">
            <tr>
                <th>№</th>
                <th>Дата</th>
                <th>Клиент</th>
                <th>Адрес</th>
                <th>Статус</th>
                <th>Действия</th>
            </tr>
            <?php while($order = $orders->fetch_assoc()): ?>
            <tr>
                <td><?= $order['zakaz_ID'] ?></td>
                <td><?= date('d.m.Y H:i', strtotime($order['vrema'])) ?></td>
                <td><?= $order['user_name'] ?></td>
                <td><?= $order['adres'] ?></td>
                <td>
                    <form method="POST" class="status-form">
                        <input type="hidden" name="order_id" value="<?= $order['zakaz_ID'] ?>">
                        <select name="status" onchange="this.form.submit()">
                            <option value="Новый" <?= $order['status'] == 'Новый' ? 'selected' : '' ?>>Новый</option>
                            <option value="Готовится" <?= $order['status'] == 'Готовится' ? 'selected' : '' ?>>Готовится</option>
                            <option value="В пути" <?= $order['status'] == 'В пути' ? 'selected' : '' ?>>В пути</option>
                            <option value="Доставлен" <?= $order['status'] == 'Доставлен' ? 'selected' : '' ?>>Доставлен</option>
                            <option value="Отменен" <?= $order['status'] == 'Отменен' ? 'selected' : '' ?>>Отменен</option>
                        </select>
                        <noscript><button type="submit" name="update_status">Обновить</button></noscript>
                    </form>
                </td>
                <td><a href="order_details.php?id=<?= $order['zakaz_ID'] ?>" class="details-btn">Подробности</a></td>
            </tr>
            <?php endwhile; ?>
        </table>
        <?php else: ?>
        <p>Нет заказов</p>
        <?php endif; ?>
    </div>
</main>
<?php include "footer.php"; ?>