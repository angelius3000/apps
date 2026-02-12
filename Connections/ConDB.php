<?php
require_once __DIR__ . '/../includes/sections_config.php';
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

    $puedeGestionarStatus = false;
    $resultadoTablaStatus = @mysqli_query(
        $conn,
        "SHOW TABLES LIKE 'status'"
    );
    if ($resultadoTablaStatus instanceof mysqli_result) {
        $puedeGestionarStatus = mysqli_num_rows($resultadoTablaStatus) > 0;
        mysqli_free_result($resultadoTablaStatus);
    }

    if ($puedeGestionarStatus) {
        $estatusRequeridos = ['Verificado', 'Auditado'];
        foreach ($estatusRequeridos as $estatus) {
            $stmtStatus = @mysqli_prepare(
                $conn,
                'SELECT STATUSID FROM status WHERE Status = ? LIMIT 1'
            );

            if ($stmtStatus) {
                mysqli_stmt_bind_param($stmtStatus, 's', $estatus);
                mysqli_stmt_execute($stmtStatus);
                mysqli_stmt_store_result($stmtStatus);

                if (mysqli_stmt_num_rows($stmtStatus) === 0) {
                    mysqli_stmt_close($stmtStatus);

                    $insertStatus = @mysqli_prepare(
                        $conn,
                        'INSERT INTO status (Status) VALUES (?)'
                    );

                    if ($insertStatus) {
                        mysqli_stmt_bind_param($insertStatus, 's', $estatus);
                        mysqli_stmt_execute($insertStatus);
                        mysqli_stmt_close($insertStatus);
                    }
                } else {
                    mysqli_stmt_close($stmtStatus);
                }
            }
        }
    }

    // Ensure sections metadata exists so that permissions can be managed from
    // the application without needing a manual migration.
    $crearTablaSecciones = "CREATE TABLE IF NOT EXISTS secciones (
        SECCIONID INT NOT NULL AUTO_INCREMENT,
        Nombre VARCHAR(100) NOT NULL,
        Slug VARCHAR(100) NOT NULL,
        Ruta VARCHAR(255) DEFAULT NULL,
        Orden INT DEFAULT 0,
        MostrarEnMenu TINYINT(1) NOT NULL DEFAULT 1,
        PRIMARY KEY (SECCIONID),
        UNIQUE KEY Slug_UNIQUE (Slug)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    $crearTablaUsuarioSecciones = "CREATE TABLE IF NOT EXISTS usuario_secciones (
        USUARIOID INT NOT NULL,
        SECCIONID INT NOT NULL,
        PuedeVer TINYINT(1) NOT NULL DEFAULT 0,
        PRIMARY KEY (USUARIOID, SECCIONID),
        CONSTRAINT fk_usuario_secciones_usuario FOREIGN KEY (USUARIOID)
            REFERENCES usuarios (USUARIOID) ON DELETE CASCADE,
        CONSTRAINT fk_usuario_secciones_seccion FOREIGN KEY (SECCIONID)
            REFERENCES secciones (SECCIONID) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    @mysqli_query($conn, $crearTablaSecciones);
    @mysqli_query($conn, $crearTablaUsuarioSecciones);

    $columnaMostrarEnMenu = @mysqli_query(
        $conn,
        "SHOW COLUMNS FROM secciones LIKE 'MostrarEnMenu'"
    );

    if ($columnaMostrarEnMenu && mysqli_num_rows($columnaMostrarEnMenu) === 0) {
        @mysqli_query(
            $conn,
            'ALTER TABLE secciones ADD COLUMN MostrarEnMenu TINYINT(1) NOT NULL DEFAULT 1 AFTER Orden'
        );
    }

    if ($columnaMostrarEnMenu) {
        mysqli_free_result($columnaMostrarEnMenu);
    }

    if (function_exists('sincronizarSeccionesBase')) {
        sincronizarSeccionesBase($conn);
    }


    // Ensure personnel catalog tables have a Deshabilitado column so records can be disabled
    // without being removed from history.
    $tablasCatalogoPersonal = ['aduana', 'vendedor', 'Surtidor', 'almacenista'];
    foreach ($tablasCatalogoPersonal as $tablaCatalogo) {
        $tablaExiste = false;
        $resultadoTablaCatalogo = @mysqli_query(
            $conn,
            "SHOW TABLES LIKE '" . mysqli_real_escape_string($conn, $tablaCatalogo) . "'"
        );

        if ($resultadoTablaCatalogo instanceof mysqli_result) {
            $tablaExiste = mysqli_num_rows($resultadoTablaCatalogo) > 0;
            mysqli_free_result($resultadoTablaCatalogo);
        }

        if (!$tablaExiste) {
            continue;
        }

        $columnaDeshabilitado = @mysqli_query(
            $conn,
            "SHOW COLUMNS FROM `" . str_replace('`', '``', $tablaCatalogo) . "` LIKE 'Deshabilitado'"
        );

        $tieneColumnaDeshabilitado = false;
        if ($columnaDeshabilitado instanceof mysqli_result) {
            $tieneColumnaDeshabilitado = mysqli_num_rows($columnaDeshabilitado) > 0;
            mysqli_free_result($columnaDeshabilitado);
        }

        if (!$tieneColumnaDeshabilitado) {
            @mysqli_query(
                $conn,
                "ALTER TABLE `" . str_replace('`', '``', $tablaCatalogo) . "` ADD COLUMN Deshabilitado TINYINT(1) NOT NULL DEFAULT 0"
            );
        }
    }

    $columnaSeccionInicio = @mysqli_query(
        $conn,
        "SHOW COLUMNS FROM usuarios LIKE 'SECCIONINICIOID'"
    );

    if ($columnaSeccionInicio && mysqli_num_rows($columnaSeccionInicio) === 0) {
        @mysqli_query(
            $conn,
            'ALTER TABLE usuarios ADD COLUMN SECCIONINICIOID INT NULL DEFAULT NULL'
        );
    }

    if ($columnaSeccionInicio) {
        mysqli_free_result($columnaSeccionInicio);
    }

    $constraintSeccionInicio = @mysqli_query(
        $conn,
        "SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE\n"
            . "WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'usuarios'\n"
            . "AND COLUMN_NAME = 'SECCIONINICIOID' AND REFERENCED_TABLE_NAME = 'secciones' LIMIT 1"
    );

    if ($constraintSeccionInicio && mysqli_num_rows($constraintSeccionInicio) === 0) {
        @mysqli_query(
            $conn,
            'ALTER TABLE usuarios '
                . 'ADD CONSTRAINT fk_usuarios_seccion_inicio '
                . 'FOREIGN KEY (SECCIONINICIOID) REFERENCES secciones (SECCIONID) '
                . 'ON DELETE SET NULL ON UPDATE CASCADE'
        );
    }

    if ($constraintSeccionInicio) {
        mysqli_free_result($constraintSeccionInicio);
    }

    $seccionesRegistradas = [];
    $resultadoSecciones = @mysqli_query(
        $conn,
        'SELECT SECCIONID, Slug FROM secciones ORDER BY Orden, Nombre'
    );
    if ($resultadoSecciones) {
        while ($filaSeccion = mysqli_fetch_assoc($resultadoSecciones)) {
            $seccionesRegistradas[(int)$filaSeccion['SECCIONID']] = $filaSeccion['Slug'];
        }
        mysqli_free_result($resultadoSecciones);
    }

    if (!empty($seccionesRegistradas)) {
        $usuariosResult = @mysqli_query($conn, 'SELECT USUARIOID FROM usuarios');
        if ($usuariosResult) {
            $stmtInsertPermiso = @mysqli_prepare(
                $conn,
                'INSERT IGNORE INTO usuario_secciones (USUARIOID, SECCIONID, PuedeVer) VALUES (?, ?, 1)'
            );

            if ($stmtInsertPermiso) {
                mysqli_stmt_bind_param($stmtInsertPermiso, 'ii', $usuarioIdParam, $seccionIdParam);

                while ($usuario = mysqli_fetch_assoc($usuariosResult)) {
                    $usuarioIdParam = (int)$usuario['USUARIOID'];

                    foreach (array_keys($seccionesRegistradas) as $seccionId) {
                        $seccionIdParam = (int)$seccionId;
                        mysqli_stmt_execute($stmtInsertPermiso);
                    }
                }

                mysqli_stmt_close($stmtInsertPermiso);
            }

            mysqli_free_result($usuariosResult);
        }
    }
}
