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

if (!function_exists('sincronizarSeccionesBase')) {
    function sincronizarSeccionesBase($conn): void
    {
        if (!class_exists('mysqli') || !$conn instanceof mysqli) {
            return;
        }

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
