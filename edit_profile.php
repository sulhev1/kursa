<?php 
$titl = "Редактирование профиля";
include "header.php";

if (!isset($_SESSION['klient_ID'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['klient_ID'];
$error = '';
$success = '';

// Получаем данные пользователя
$user = $db->query("SELECT * FROM users WHERE klient_ID = $user_id")->fetch_assoc();

// Обработка формы
if (isset($_POST['update'])) {
    $name = POSTS('name');
    $phone = POSTS('phone');
    $current_pass = POSTS('current_pass');
    $new_pass = POSTS('new_pass');
    
    // Проверяем текущий пароль
    if (!empty($current_pass) {
        if (!password_verify($current_pass, $user['password'])) {
            $error = "Неверный текущий пароль";
        } else {
            // Обновляем пароль
            $hash = password_hash($new_pass, PASSWORD_DEFAULT);
            $db->query("UPDATE users SET password = '$hash' WHERE klient_ID = $user_id");
            $success = "Пароль успешно изменен";
        }
    }
    
    // Обновляем остальные данные
    $db->query("UPDATE users SET name = '$name', phone = '$phone' WHERE klient_ID = $user_id");
    
    if (empty($error)) {
        $success = empty($success) ? "Данные успешно обновлены" : $success;
        $user = $db->query("SELECT * FROM users WHERE klient_ID = $user_id")->fetch_assoc();
    }
}
?>
<main>
    <div class="zag">Редактирование профиля</div>
    
    <?php if($error): ?>
    <div class="error-message"><?= $error ?></div>
    <?php endif; ?>
    
    <?php if($success): ?>
    <div class="success-message"><?= $success ?></div>
    <?php endif; ?>
    
    <form method="POST" class="profile-form">
        <div class="form-group">
            <label>Имя:</label>
            <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
        </div>
        
        <div class="form-group">
            <label>Телефон:</label>
            <input type="text" name="phone" value="<?= htmlspecialchars($user['phone']) ?>" required>
        </div>
        
        <div class="form-group">
            <label>Текущий пароль (оставьте пустым, если не хотите менять):</label>
            <input type="password" name="current_pass">
        </div>
        
        <div class="form-group">
            <label>Новый пароль:</label>
            <input type="password" name="new_pass">
        </div>
        
        <button type="submit" name="update" class="update-btn">Сохранить изменения</button>
    </form>
</main>
<?php include "footer.php"; ?>