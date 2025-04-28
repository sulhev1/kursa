<?php
include "sql.php";
include "httpdata.php";
session_start();
const DB_HOST = "localhost";
const DB_USER = "sulhaev";    // логин к БД
const DB_PASS = "sulhaev536400";     // пароль к БД
const DB_NAME = "sulhaev";    // имя базы данных

const UNKNOWN = 0;
const USER = 1;
const ADMIN = 2;

const OPEN_REGISTER_WINDOW = 2;
const OPEN_AUTHORIZATION_WINDOW = 1;
// подключение с помощью функции
$db = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// подключение с помощью ООП
// $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

//
function FindDublicate($db, $sql) {
    $result = false;
    $query = $db->query($sql);
    if ($query->num_rows > 0) { $result = true; }
    $query->close();
    return $result;
}

function Input($text, string $type, string $name, $value, string $placeholder = null) {
    $label = '<label>'.$text.'</label>';
    if ($type == 'hidden') { $label = ''; }
    if ($type == 'textarea') {
        $input = '<textarea name="' . $name . '">' . $value . '</textarea>';
    } else {
        $input = '<input type = "' . $type . '" name = "' . $name . '" value = "' . $value . '"';
        if (!is_null($placeholder)) { $input .= ' placeholder = "' . $placeholder . '"'; }
        $input .= '>';
    }
    print '<div>' . $label . $input . '</div>';
}

?>