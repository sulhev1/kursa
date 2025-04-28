<?php 
$titl="Проверка";
include "header.php";
$name = ""; $error_name = ""; $err_name = "";
$phone = ""; $error_phone = ""; $err_phone = "";
$pass = ""; $error_pass = ""; $err_pass = "";
$admin = 0; $error = ""; $admicik = "";
$otkrit = 0;
if (isset($_SESSION['admin'])) { $admin = $_SESSION['admin']; }
if (isset($_GET['delete_bludo'])) {
    $db->query("DELETE FROM bludo WHERE `bludo_ID` = '{$_GET['delete_bludo']}'");
}
if (isset($_POST["name"])) {
    $name = $db->real_escape_string($_POST["name"]);
    $phone = $db->real_escape_string($_POST["phone"]);
    $pass = $db->real_escape_string($_POST["pass"]);
    if($name == "") {
        $error_name .= "Укажите Имя!<br>";
        $err_name = "error";
    }
    if($phone == "") {
        $error_phone .= "Укажите номер телефона!<br>";
        $err_phone = "error";
    }
    if($pass == "") {
        $error_pass .= "Укажите пароль!<br>";
        $err_pass = "error";
    }

    $sql ="SELECT * FROM users WHERE phone = '$phone'";
        if (FindDublicate($db, $sql) == true) {
            $error_phone .= "Номер телефона уже зарегистрирован<br>";
            $err_phone = "error"; 
        };
        if ($error_phone == "") {
        ///новый пользователь
        $hash_password = password_hash($pass, PASSWORD_DEFAULT);
        $ss = "INSERT INTO `users`(`name`, `phone`, `password`) VALUES ('$name', '$phone', '$hash_password')";
        $db->query($ss); 
        $query = $db->query($sql);
        ?>

        <div class="zareg">
            <div class="ysp">Вы успешно зарегистрировались!</div>
        </div>

<?php
exit();
}
}
if (isset($_POST["avtoriz"])) {
    $phone = $db->real_escape_string($_POST["phone_avtor"]);
    $pass = $db->real_escape_string($_POST["pass_avtor"]);
    if($phone == "") {
        $error_phone .= "Укажите номер телефона!<br>";
        $err_phone = "error";
        $otkrit = "2";
    }
    if($pass == "") {
        $error_pass .= "Укажите пароль!<br>";
        $err_pass = "error";
        $otkrit = "2";
    }

    $sql = "SELECT * FROM users WHERE phone = '$phone'";
    $query = $db->query($sql);
    if ($query->num_rows > 0) {
        $line = $query->fetch_array();
        if (password_verify($pass, $line['password'])) {
            $_SESSION['klient_ID'] = $line['klient_ID'];
            $_SESSION['admin'] = $line['admin'];
            header('Location: index.php');
            exit();
        }
    }
    $error_pass .="Не верно введен номер телефона или пароль!";
    $otkrit = "2";
    $query->close();
}

if ($admin == 1) {
    $admicik = "Вы обычный пользователь";
}elseif ($admin == 2) {
    $admicik = "Вы администратор";
}

print $admicik;
print $admin;
?>
<main>
    <div class="name">
        <div class="name1">
            <img src="image/logo.svg" alt="">
            <div class="tname">Шаурмастер</div>
        </div>
        <div class="tname2">Доставка шаурмы по тольятти!</div>
        <?php if(isset($_SESSION['admin'])): ?>
            <a href="exit.php" class="knopka" style="text-decoration: none; color: black;">Выйти</a>
            <a href="korzinka.php" class="knopka">Корзина</a>

        <?php else: ?>
            <div class="knopka" onclick="ak1()">Войти</div>
            <div class="knopka" onclick="ak()">Регистрация</div>
        <?php endif; ?>
    </div>
    <div class="zag">Шаурма</div>
    <div class="shava">
    <?php
    $sql = new SELECT($db, 'bludo');
    while ($sql->nextRow()) {
        print tovar($admin, $sql->get('bludo_ID'),$sql->get('name_eda'),$sql->get('infa'),$sql->get('cash'),$sql->get('photo'));
    }
    ?></div>
    <?php if($admin == ADMIN) {?>
    <a href="dobav.php" class="knopka_pozic">Добавить позицию</a>
    <?php } ?>
    <form action="index.php" method="POST">
        <section>
            <div id="zapisat">
                <div class="kvadratik">
                    <div class="BLOCK" style="width: 460px;">
                        <div id="krest" onclick="krest1()" style="cursor: pointer;"><img src="image/krest.png" alt=""></div>
                        <div class="regg">Регистрация</div>
                        <input class="input <?php print $err_name; ?>" type="text" name="name" placeholder="Имя" value="<?php print $name; ?>">
                        <div class="message"><?php print $error_name; ?></div>
                        <input class="input <?php print $err_phone; ?>" type="text" name="phone" placeholder="Телефон" value="<?php print $phone; ?>">
                        <div class="message"><?php print $error_phone; ?></div>
                        <input class="input <?php print $err_pass; ?>" type="password" name="pass" placeholder="Пароль" value="<?php print $pass; ?>" class="<?php print $err_pass; ?>">
                        <div class="message"><?php print $error_pass; ?></div>
                        <input class="knopka" type="submit" name="registr" value="Зарегестрироваться" style="width: 200px">
                    </div>
                </div>
            </div>
        </section>
    </form>
    <form action="index.php" method="POST">
        <section>
            <div id="zapisat1">
                <div class="kvadratik">
                    <div class="BLOCK" style="width: 460px;">
                        <div id="krest" onclick="krest2()" style="cursor: pointer;"><img src="image/krest.png" alt=""></div>
                        <div class="regg">Авторизация</div>
                        <input class="input <?php print $err_phone; ?>" type="text" name="phone_avtor" placeholder="Телефон" value="<?php print $phone; ?>">
                        <div class="message"><?php print $error_phone; ?></div>
                        <input class="input <?php print $err_pass; ?>" type="password" name="pass_avtor" placeholder="Пароль" value="<?php print $pass; ?>" class="<?php print $err_pass; ?>">
                        <div class="message"><?php print $error_pass; ?></div>
                        <input class="knopka" type="submit" value="Войти" name="avtoriz">
                        <div class="zabil" onclick="ak(); krest2()">Нет аккаунта? </div>
                    </div>
                </div>
            </div>
        </section>
    </form>
<?php 
if($otkrit == 1) {
    print '<script>ak();</script>';
} elseif ($otkrit == 2) {
    print '<script>ak1();</script>';
}

function tovar($admin, $id, $name_eda, $infa, $cash, $photo) {
    $result = '
    <div class="block1">
        <div class="foto"><img src="'.$photo.'" alt=""></div>
        <div class="namebludo">'.$name_eda.'</div>
        <div class="inf">'.$infa.'</div>
        <div class="flex">
            <div class="stoimost">'.$cash.'</div>';
            if ($admin > UNKNOWN) {
                $result .= '<div class="knopka" onclick="">Добавить</div>';
            } else {
                $result .= '<div class="knopka" onclick="ak1()">Добавить</div>';
            }
        $result .= '</div>';
        if ($admin == ADMIN) {
            $result .= '<a class="knopka" href="?delete_bludo='.$id.'">Удалить</a>';
        }
    $result .=' </div>';
    return $result;
}
?>
</main>
</body>
</html>
