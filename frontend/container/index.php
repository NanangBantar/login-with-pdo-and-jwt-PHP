<?php
// start a session
session_start();
if (empty($_SESSION['userToken'])) {
    header('Location: https://localhost/remakeAbsen/frontend');
}
include("include/head.php");
include("include/sidebar.php");
include("include/main.php");
include("include/footer.php");
?>