<?php
session_start();
unset( $_SESSION['klient_ID']);
unset($_SESSION['admin']);
session_destroy();
header("Location: index.php");
?>