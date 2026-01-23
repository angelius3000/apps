<?php

function establecerZonaHorariaConteo(): void
{
    if (function_exists('date_default_timezone_set')) {
        date_default_timezone_set('America/Denver');
    }
}

function asegurarTablaConteo(mysqli $conn, string $baseDatos): void
{
    $crearTablaSQL = "CREATE TABLE IF NOT EXISTS conteo_visitas (\n"
        . "    ConteoID INT NOT NULL AUTO_INCREMENT,\n"
        . "    Fecha DATE NOT NULL,\n"
        . "    HoraInicio TIME NOT NULL,\n"
        . "    HoraFin TIME NOT NULL,\n"
        . "    Hombre INT NOT NULL DEFAULT 0,\n"
        . "    Mujer INT NOT NULL DEFAULT 0,\n"
        . "    Pareja INT NOT NULL DEFAULT 0,\n"
        . "    Familia INT NOT NULL DEFAULT 0,\n"
        . "    Cuadrilla INT NOT NULL DEFAULT 0,\n"
        . "    PRIMARY KEY (ConteoID),\n"
        . "    UNIQUE KEY uniq_conteo_fecha_hora (Fecha, HoraInicio)\n"
        . ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    @mysqli_query($conn, $crearTablaSQL);

    $columnasRequeridas = [
        'Hombre' => "ALTER TABLE conteo_visitas ADD COLUMN Hombre INT NOT NULL DEFAULT 0 AFTER HoraFin",
        'Mujer' => "ALTER TABLE conteo_visitas ADD COLUMN Mujer INT NOT NULL DEFAULT 0 AFTER Hombre",
        'Pareja' => "ALTER TABLE conteo_visitas ADD COLUMN Pareja INT NOT NULL DEFAULT 0 AFTER Mujer",
        'Familia' => "ALTER TABLE conteo_visitas ADD COLUMN Familia INT NOT NULL DEFAULT 0 AFTER Pareja",
        'Cuadrilla' => "ALTER TABLE conteo_visitas ADD COLUMN Cuadrilla INT NOT NULL DEFAULT 0 AFTER Familia",
    ];

    foreach ($columnasRequeridas as $columna => $sqlAlter) {
        $stmtColumna = mysqli_prepare(
            $conn,
            'SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ? LIMIT 1'
        );

        if ($stmtColumna) {
            $tabla = 'conteo_visitas';
            mysqli_stmt_bind_param($stmtColumna, 'sss', $baseDatos, $tabla, $columna);
            mysqli_stmt_execute($stmtColumna);
            mysqli_stmt_store_result($stmtColumna);

            if (mysqli_stmt_num_rows($stmtColumna) === 0) {
                @mysqli_query($conn, $sqlAlter);
            }

            mysqli_stmt_close($stmtColumna);
        }
    }
}

function obtenerRangosHorasConteo(): array
{
    $rangos = [];
    for ($hora = 8; $hora <= 18; $hora++) {
        $horaInicio = sprintf('%02d:00:00', $hora);
        $horaFin = sprintf('%02d:00:00', $hora + 1);
        $etiqueta = sprintf('%02d:00-%02d:00', $hora, $hora + 1);
        $rangos[] = [
            'horaInicio' => $horaInicio,
            'horaFin' => $horaFin,
            'etiqueta' => $etiqueta,
        ];
    }

    return $rangos;
}

function asegurarFilasConteo(mysqli $conn, string $fecha): void
{
    $rangos = obtenerRangosHorasConteo();

    $stmtInsert = mysqli_prepare(
        $conn,
        'INSERT IGNORE INTO conteo_visitas (Fecha, HoraInicio, HoraFin, Hombre, Mujer, Pareja, Familia, Cuadrilla) VALUES (?, ?, ?, 0, 0, 0, 0, 0)'
    );

    if (!$stmtInsert) {
        return;
    }

    foreach ($rangos as $rango) {
        $horaInicio = $rango['horaInicio'];
        $horaFin = $rango['horaFin'];
        mysqli_stmt_bind_param($stmtInsert, 'sss', $fecha, $horaInicio, $horaFin);
        mysqli_stmt_execute($stmtInsert);
    }

    mysqli_stmt_close($stmtInsert);
}

function obtenerConteoPorFecha(mysqli $conn, string $fecha): array
{
    $stmtSelect = mysqli_prepare(
        $conn,
        'SELECT Fecha, HoraInicio, HoraFin, Hombre, Mujer, Pareja, Familia, Cuadrilla ' .
        'FROM conteo_visitas WHERE Fecha = ? ORDER BY HoraInicio ASC'
    );

    if (!$stmtSelect) {
        return [];
    }

    mysqli_stmt_bind_param($stmtSelect, 's', $fecha);
    mysqli_stmt_execute($stmtSelect);
    mysqli_stmt_bind_result(
        $stmtSelect,
        $fechaDb,
        $horaInicio,
        $horaFin,
        $hombre,
        $mujer,
        $pareja,
        $familia,
        $cuadrilla
    );

    $registros = [];
    while (mysqli_stmt_fetch($stmtSelect)) {
        $registros[] = [
            'fecha' => $fechaDb,
            'horaInicio' => $horaInicio,
            'horaFin' => $horaFin,
            'hombre' => (int) $hombre,
            'mujer' => (int) $mujer,
            'pareja' => (int) $pareja,
            'familia' => (int) $familia,
            'cuadrilla' => (int) $cuadrilla,
        ];
    }

    mysqli_stmt_close($stmtSelect);

    return $registros;
}

function obtenerHoraActualConteo(): int
{
    return (int) date('G');
}

function obtenerFechaActualConteo(): string
{
    return date('Y-m-d');
}

function obtenerEtiquetaHoraConteo(string $horaInicio, string $horaFin): string
{
    $horaInicioFormateada = substr($horaInicio, 0, 5);
    $horaFinFormateada = substr($horaFin, 0, 5);

    return $horaInicioFormateada . '-' . $horaFinFormateada;
}
