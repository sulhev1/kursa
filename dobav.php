<?php 
$titl = "Добавить";
include "header.php";
$name_eda = ""; $photo = ""; $infa = ""; $cash = "";
$error_name_eda = ""; $error_photo = ""; $error_infa = ""; $error_cash = "";
$err_name_eda = ""; $err_photo = ""; $err_infa = ""; $err_cash = "";
if (isset($_POST["name_eda"])) {
    $name_eda = $db->real_escape_string($_POST["name_eda"]);    
    $infa = $db->real_escape_string($_POST["infa"]);
    $cash = $db->real_escape_string($_POST["cash"]);
    if ($_FILES && $_FILES["photo"]["error"]== UPLOAD_ERR_OK) {
        $tempname = $_FILES["photo"]["tmp_name"];
        $ext =  pathinfo($_FILES["photo"]["name"], PATHINFO_EXTENSION);
        $filename = 'image/'.uniqid().'.'.$ext;
        if (move_uploaded_file($tempname, $filename)) {
            $photo = $_FILES["photo"]["name"];
        }
    }
    if($name_eda == "") {
        $error_name_eda .= "Укажите название блюда!<br>";
        $err_name_eda = "error";
    }
    if($photo == "") {
        $error_photo .= "Вставте фото!<br>";
        $err_photo = "error";
    }
    if($infa == "") {
        $error_infa .= "Укажите состав!<br>";
        $err_infa = "error";
    }
    if($cash == "") {
        $error_cash .= "Укажите стоиость блюда!<br>";
        $err_cash = "error";
    }
    if ($err_name_eda == "" && $err_photo == "" && $err_infa == "" && $err_cash == "") {
        $ss = "INSERT INTO `bludo`(`name_eda`, `photo`, `infa`, `cash`) VALUES ('$name_eda','$filename','$infa','$cash')";
        $db->query($ss); 
    } else {
        @unlink($filename);
    }
}
?>
<div class="zag">Добавление блюда в меню</div>
<div class="width_inp">
    <form action="dobav.php" method="POST" enctype="multipart/form-data">
            <input class="input <?php print $err_name_eda; ?>" type="text" name="name_eda" placeholder="Название блюда" value="<?php print $name_eda; ?>">
            <div class="message"><?php print $error_name_eda; ?></div>
            <input class="input <?php print $err_photo; ?>" type="file" name="photo" placeholder="Фото" value="<?php print $photo; ?>" class="<?php print $err_photo; ?>">
            <div class="message"><?php print $error_photo; ?></div>
            <input class="input <?php print $err_infa; ?>" type="text" name="infa" placeholder="Информация" value="<?php print $infa; ?>">
            <div class="message"><?php print $error_infa; ?></div>
            <input class="input <?php print $err_cash; ?>" type="text" name="cash" placeholder="Стоимость" value="<?php print $cash; ?>" class="<?php print $err_cash; ?>">
            <div class="message"><?php print $error_cash; ?></div>
            <input class="knopka" type="submit" value="Добавить" name="knopka_dobav">
    </form>
</div>
</body>
</html>

