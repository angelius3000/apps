<?php
if (!function_exists('obtenerDefinicionSeccionesBase')) {
    function obtenerDefinicionSeccionesBase(): array
    {
        return [
            [
                'Nombre' => 'Aplicaciones',
                'Slug' => 'aplicaciones',
                'Ruta' => 'main.php',
                'Orden' => 1,
                'MostrarEnMenu' => 1,
            ],
            [
                'Nombre' => 'Charolas',
                'Slug' => 'charolas',
                'Ruta' => 'charolas.php',
                'Orden' => 2,
                'MostrarEnMenu' => 1,
            ],
            [
                'Nombre' => 'Reparto',
                'Slug' => 'reparto',
                'Ruta' => 'Repartos.php',
                'Orden' => 3,
                'MostrarEnMenu' => 1,
            ],
            [
                'Nombre' => 'Clientes',
                'Slug' => 'clientes',
                'Ruta' => 'Clientes.php',
                'Orden' => 4,
                'MostrarEnMenu' => 1,
            ],
            [
                'Nombre' => 'Usuarios',
                'Slug' => 'usuarios',
                'Ruta' => 'Usuarios.php',
                'Orden' => 5,
                'MostrarEnMenu' => 1,
            ],
            [
                'Nombre' => 'Administración',
                'Slug' => 'administracion',
                'Ruta' => 'Administracion.php',
                'Orden' => 6,
                'MostrarEnMenu' => 1,
            ],
        ];
    }
}

if (!function_exists('asegurarEstructuraTablaSecciones')) {
    function asegurarEstructuraTablaSecciones($conn): void
    {
        if (!class_exists('mysqli') || !$conn instanceof mysqli) {
            return;
        }

        @mysqli_query(
            $conn,
            "CREATE TABLE IF NOT EXISTS secciones (" .
            "    SECCIONID INT NOT NULL AUTO_INCREMENT," .
            "    Nombre VARCHAR(100) NOT NULL," .
            "    Slug VARCHAR(100) NOT NULL," .
            "    Ruta VARCHAR(255) DEFAULT NULL," .
            "    Orden INT DEFAULT 0," .
            "    MostrarEnMenu TINYINT(1) NOT NULL DEFAULT 1," .
            "    PRIMARY KEY (SECCIONID)" .
            ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );

        $columnasActuales = [];
        $resultadoColumnas = @mysqli_query($conn, 'SHOW COLUMNS FROM secciones');
        if ($resultadoColumnas instanceof mysqli_result) {
            while ($columna = mysqli_fetch_assoc($resultadoColumnas)) {
                $nombreColumna = strtolower((string)($columna['Field'] ?? ''));
                if ($nombreColumna !== '') {
                    $columnasActuales[$nombreColumna] = true;
                }
            }
            mysqli_free_result($resultadoColumnas);
        }

        $alteraciones = [];
        if (!isset($columnasActuales['slug'])) {
            $alteraciones[] = 'ADD COLUMN Slug VARCHAR(100) NOT NULL AFTER Nombre';
        }
        if (!isset($columnasActuales['ruta'])) {
            $alteraciones[] = 'ADD COLUMN Ruta VARCHAR(255) DEFAULT NULL AFTER Slug';
        }
        if (!isset($columnasActuales['orden'])) {
            $alteraciones[] = 'ADD COLUMN Orden INT DEFAULT 0 AFTER Ruta';
        }
        if (!isset($columnasActuales['mostrarenmenu'])) {
            $alteraciones[] = 'ADD COLUMN MostrarEnMenu TINYINT(1) NOT NULL DEFAULT 1 AFTER Orden';
        }

        if (!empty($alteraciones)) {
            @mysqli_query($conn, 'ALTER TABLE secciones ' . implode(', ', $alteraciones));
        }

        $indiceSlug = @mysqli_query(
            $conn,
            "SHOW INDEX FROM secciones WHERE Key_name = 'Slug_UNIQUE'"
        );

        $tieneIndiceSlug = false;
        if ($indiceSlug instanceof mysqli_result) {
            $tieneIndiceSlug = mysqli_num_rows($indiceSlug) > 0;
            mysqli_free_result($indiceSlug);
        }

        if (!$tieneIndiceSlug) {
            @mysqli_query($conn, 'ALTER TABLE secciones ADD UNIQUE KEY Slug_UNIQUE (Slug)');
        }
    }
}

if (!function_exists('sincronizarSeccionesBase')) {
    function sincronizarSeccionesBase($conn): void
    {
        if (!class_exists('mysqli') || !$conn instanceof mysqli) {
            return;
        }

        asegurarEstructuraTablaSecciones($conn);

        $seccionesBase = obtenerDefinicionSeccionesBase();
        if (empty($seccionesBase)) {
            return;
        }

        $stmtInsertSeccion = @mysqli_prepare(
            $conn,
            'INSERT INTO secciones (Nombre, Slug, Ruta, Orden, MostrarEnMenu) VALUES (?, ?, ?, ?, ?)' .
            ' ON DUPLICATE KEY UPDATE Nombre = VALUES(Nombre), Ruta = VALUES(Ruta), Orden = VALUES(Orden)'
        );

        if (!$stmtInsertSeccion) {
            return;
        }

        $nombre = '';
        $slug = '';
        $ruta = '';
        $orden = 0;
        $mostrarEnMenu = 1;

        mysqli_stmt_bind_param($stmtInsertSeccion, 'sssii', $nombre, $slug, $ruta, $orden, $mostrarEnMenu);

        foreach ($seccionesBase as $seccionBase) {
            $nombre = (string)($seccionBase['Nombre'] ?? '');
            $slug = (string)($seccionBase['Slug'] ?? '');
            $ruta = (string)($seccionBase['Ruta'] ?? '');
            $orden = (int)($seccionBase['Orden'] ?? 0);
            $mostrarEnMenu = (int)($seccionBase['MostrarEnMenu'] ?? 1);

            if ($slug === '') {
                continue;
            }

            @mysqli_stmt_execute($stmtInsertSeccion);
        }

        mysqli_stmt_close($stmtInsertSeccion);
    }
}
