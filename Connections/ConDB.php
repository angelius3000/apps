<?php
/* Database connection start */
if ($_SERVER['HTTP_HOST'] == "localhost") {

    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "edison";
} else if ($_SERVER['HTTP_HOST'] == "local.edison:8888") {

    $servername = "localhost";
    $username = "root";
    $password = "root";
    $dbname = "edison";
} else {

    /* Database connection start */
    $servername = "localhost:3306";
    $username = "reparto";
    $password = "Edison2024!";
    $dbname = "edison";
}

$connectionError = null;
mysqli_report(MYSQLI_REPORT_OFF);
$conn = @mysqli_connect($servername, $username, $password, $dbname);
if ($conn === false) {
    $connectionError = mysqli_connect_error();
} else {
    mysqli_set_charset($conn, 'utf8mb4');
}
