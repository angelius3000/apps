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

    // Ensure new user profiles exist so they are available across the
    // application without requiring a manual database migration.
    $perfilesNuevos = ['Auditor', 'Supervisor'];
    foreach ($perfilesNuevos as $perfil) {
        $stmt = @mysqli_prepare(
            $conn,
            'SELECT TIPODEUSUARIOID FROM tipodeusuarios WHERE TipoDeUsuario = ? LIMIT 1'
        );

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 's', $perfil);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);

            if (mysqli_stmt_num_rows($stmt) === 0) {
                mysqli_stmt_close($stmt);

                $insertStmt = @mysqli_prepare(
                    $conn,
                    'INSERT INTO tipodeusuarios (TipoDeUsuario) VALUES (?)'
                );

                if ($insertStmt) {
                    mysqli_stmt_bind_param($insertStmt, 's', $perfil);
                    mysqli_stmt_execute($insertStmt);
                    mysqli_stmt_close($insertStmt);
                }
            } else {
                mysqli_stmt_close($stmt);
            }
        }
    }
}
