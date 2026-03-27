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
    $perfilesNuevos = ['Auditor', 'Supervisor', 'Soporte IT'];
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

    $crearTablaTickets = "CREATE TABLE IF NOT EXISTS tickets (
        TICKETID INT NOT NULL AUTO_INCREMENT,
        Folio VARCHAR(25) NOT NULL,
        Titulo VARCHAR(200) NOT NULL,
        Descripcion TEXT NOT NULL,
        Prioridad VARCHAR(20) NOT NULL DEFAULT 'Media',
        Categoria VARCHAR(50) NOT NULL DEFAULT 'Otros',
        STATUS VARCHAR(30) NOT NULL DEFAULT 'Abierto',
        USUARIOID_CREADOR INT NOT NULL,
        USUARIOID_ASIGNADO INT NULL,
        AutoAsignado TINYINT(1) NOT NULL DEFAULT 0,
        FechaCreacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        FechaActualizacion TIMESTAMP NULL DEFAULT NULL,
        FechaCierre TIMESTAMP NULL DEFAULT NULL,
        PRIMARY KEY (TICKETID),
        UNIQUE KEY uq_tickets_folio (Folio),
        CONSTRAINT fk_tickets_usuario_creador FOREIGN KEY (USUARIOID_CREADOR)
            REFERENCES usuarios (USUARIOID) ON DELETE RESTRICT,
        CONSTRAINT fk_tickets_usuario_asignado FOREIGN KEY (USUARIOID_ASIGNADO)
            REFERENCES usuarios (USUARIOID) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    $crearTablaTicketsComentarios = "CREATE TABLE IF NOT EXISTS tickets_comentarios (
        COMENTARIOID INT NOT NULL AUTO_INCREMENT,
        TICKETID INT NOT NULL,
        USUARIOID INT NOT NULL,
        Comentario TEXT NOT NULL,
        Fecha TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (COMENTARIOID),
        CONSTRAINT fk_tickets_comentarios_ticket FOREIGN KEY (TICKETID)
            REFERENCES tickets (TICKETID) ON DELETE CASCADE,
        CONSTRAINT fk_tickets_comentarios_usuario FOREIGN KEY (USUARIOID)
            REFERENCES usuarios (USUARIOID) ON DELETE RESTRICT
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    @mysqli_query($conn, $crearTablaTickets);
    @mysqli_query($conn, $crearTablaTicketsComentarios);

    $crearTablaEncuestas = "CREATE TABLE IF NOT EXISTS encuestas (
        ENCUESTAID INT NOT NULL AUTO_INCREMENT,
        Titulo VARCHAR(200) NOT NULL,
        Descripcion TEXT NULL,
        Estado VARCHAR(20) NOT NULL DEFAULT 'borrador',
        Categoria VARCHAR(100) NULL,
        Anonima TINYINT(1) NOT NULL DEFAULT 0,
        UnaRespuestaPorUsuario TINYINT(1) NOT NULL DEFAULT 1,
        PermitirMultiplesRespuestas TINYINT(1) NOT NULL DEFAULT 0,
        RequiereLogin TINYINT(1) NOT NULL DEFAULT 1,
        FechaInicio DATETIME NULL,
        FechaFin DATETIME NULL,
        MensajeConfirmacion TEXT NULL,
        ConfiguracionJSON LONGTEXT NULL,
        CreadoPor INT NOT NULL,
        PublicadoPor INT NULL,
        FechaPublicacion DATETIME NULL,
        FechaCreacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        FechaActualizacion TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
        Eliminado TINYINT(1) NOT NULL DEFAULT 0,
        PRIMARY KEY (ENCUESTAID),
        INDEX idx_encuestas_estado (Estado),
        INDEX idx_encuestas_creado_por (CreadoPor),
        CONSTRAINT fk_encuestas_creado_por FOREIGN KEY (CreadoPor) REFERENCES usuarios (USUARIOID) ON DELETE RESTRICT,
        CONSTRAINT fk_encuestas_publicado_por FOREIGN KEY (PublicadoPor) REFERENCES usuarios (USUARIOID) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    $crearTablaEncuestaPreguntas = "CREATE TABLE IF NOT EXISTS encuesta_preguntas (
        PREGUNTAID INT NOT NULL AUTO_INCREMENT,
        ENCUESTAID INT NOT NULL,
        Tipo VARCHAR(40) NOT NULL,
        Titulo VARCHAR(500) NOT NULL,
        Descripcion TEXT NULL,
        Requerida TINYINT(1) NOT NULL DEFAULT 0,
        Orden INT NOT NULL DEFAULT 1,
        ConfiguracionJSON LONGTEXT NULL,
        FechaCreacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        FechaActualizacion TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (PREGUNTAID),
        INDEX idx_encuesta_preguntas_encuesta (ENCUESTAID),
        INDEX idx_encuesta_preguntas_orden (Orden),
        CONSTRAINT fk_encuesta_preguntas_encuesta FOREIGN KEY (ENCUESTAID) REFERENCES encuestas (ENCUESTAID) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    $crearTablaEncuestaOpciones = "CREATE TABLE IF NOT EXISTS encuesta_opciones (
        OPCIONID INT NOT NULL AUTO_INCREMENT,
        PREGUNTAID INT NOT NULL,
        Texto VARCHAR(255) NOT NULL,
        Valor VARCHAR(255) NULL,
        Orden INT NOT NULL DEFAULT 1,
        Activo TINYINT(1) NOT NULL DEFAULT 1,
        FechaCreacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        FechaActualizacion TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (OPCIONID),
        INDEX idx_encuesta_opciones_pregunta (PREGUNTAID),
        CONSTRAINT fk_encuesta_opciones_pregunta FOREIGN KEY (PREGUNTAID) REFERENCES encuesta_preguntas (PREGUNTAID) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    $crearTablaEncuestaRespuestas = "CREATE TABLE IF NOT EXISTS encuesta_respuestas (
        RESPUESTAID INT NOT NULL AUTO_INCREMENT,
        ENCUESTAID INT NOT NULL,
        USUARIOID INT NULL,
        HashControlRespuesta VARCHAR(255) NULL,
        TokenInvitacion VARCHAR(255) NULL,
        IpRespuesta VARCHAR(64) NULL,
        UserAgent VARCHAR(255) NULL,
        FechaCreacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        FechaActualizacion TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (RESPUESTAID),
        INDEX idx_encuesta_respuestas_encuesta (ENCUESTAID),
        INDEX idx_encuesta_respuestas_usuario (USUARIOID),
        CONSTRAINT fk_encuesta_respuestas_encuesta FOREIGN KEY (ENCUESTAID) REFERENCES encuestas (ENCUESTAID) ON DELETE CASCADE,
        CONSTRAINT fk_encuesta_respuestas_usuario FOREIGN KEY (USUARIOID) REFERENCES usuarios (USUARIOID) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    $crearTablaEncuestaRespuestaDetalle = "CREATE TABLE IF NOT EXISTS encuesta_respuesta_detalle (
        DETALLEID INT NOT NULL AUTO_INCREMENT,
        RESPUESTAID INT NOT NULL,
        PREGUNTAID INT NOT NULL,
        Criterio VARCHAR(255) NULL,
        OpcionTexto VARCHAR(255) NULL,
        OPCIONID INT NULL,
        ValorTexto TEXT NULL,
        ValorNumero DECIMAL(14,4) NULL,
        ValorFecha DATETIME NULL,
        FechaCreacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        FechaActualizacion TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (DETALLEID),
        INDEX idx_encuesta_respuesta_detalle_respuesta (RESPUESTAID),
        INDEX idx_encuesta_respuesta_detalle_pregunta (PREGUNTAID),
        CONSTRAINT fk_encuesta_detalle_respuesta FOREIGN KEY (RESPUESTAID) REFERENCES encuesta_respuestas (RESPUESTAID) ON DELETE CASCADE,
        CONSTRAINT fk_encuesta_detalle_pregunta FOREIGN KEY (PREGUNTAID) REFERENCES encuesta_preguntas (PREGUNTAID) ON DELETE CASCADE,
        CONSTRAINT fk_encuesta_detalle_opcion FOREIGN KEY (OPCIONID) REFERENCES encuesta_opciones (OPCIONID) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    @mysqli_query($conn, $crearTablaEncuestas);
    @mysqli_query($conn, $crearTablaEncuestaPreguntas);
    @mysqli_query($conn, $crearTablaEncuestaOpciones);
    @mysqli_query($conn, $crearTablaEncuestaRespuestas);
    @mysqli_query($conn, $crearTablaEncuestaRespuestaDetalle);


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
