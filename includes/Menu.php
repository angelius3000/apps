<div class="app-menu">
    <ul class="accordion-menu">
        <!--   <li class="sidebar-title">
            Apps
        </li> -->
        <?php
        $permisosSecciones = $_SESSION['PermisosSecciones'] ?? [];
        $seccionesVisibles = $_SESSION['SeccionesVisibles'] ?? [];
        $tipoUsuarioActual = isset($_SESSION['TipoDeUsuario']) ? strtolower(trim((string) $_SESSION['TipoDeUsuario'])) : '';
        $menuSecciones = [
            [
                'slug' => 'aplicaciones',
                'ruta' => 'main.php',
                'icono' => 'dashboard',
                'nombre' => 'Aplicaciones',
            ],
            [
                'slug' => 'charolas',
                'ruta' => 'charolas.php',
                'icono' => 'view_day',
                'nombre' => 'Charolas',
            ],
            [
                'slug' => 'reparto',
                'ruta' => 'Repartos.php',
                'icono' => 'local_shipping',
                'nombre' => 'Reparto',
            ],
            [
                'slug' => 'clientes',
                'ruta' => 'Clientes.php',
                'icono' => 'people',
                'nombre' => 'Clientes',
            ],
            [
                'slug' => 'usuarios',
                'ruta' => 'Usuarios.php',
                'icono' => 'person_add_alt',
                'nombre' => 'Usuarios',
            ],
            [
                'slug' => 'administracion',
                'ruta' => 'Administracion.php',
                'icono' => 'settings',
                'nombre' => 'Administración',
                'soloAdministrador' => true,
            ],
        ];

        foreach ($menuSecciones as $seccionMenu) {
            $slug = $seccionMenu['slug'];
            $mostrar = !isset($permisosSecciones[$slug]) || (int)$permisosSecciones[$slug] === 1;

            if (isset($seccionesVisibles[$slug]) && (int)$seccionesVisibles[$slug] !== 1) {
                $mostrar = false;
            }

            if (!empty($seccionMenu['soloAdministrador']) && $tipoUsuarioActual !== 'administrador') {
                $mostrar = false;
            }

            if ($mostrar) {
                echo '<li>';
                echo '<a href="' . htmlspecialchars($seccionMenu['ruta'], ENT_QUOTES, 'UTF-8') . '"><i class="material-icons-two-tone">' . htmlspecialchars($seccionMenu['icono'], ENT_QUOTES, 'UTF-8') . '</i>' . htmlspecialchars($seccionMenu['nombre'], ENT_QUOTES, 'UTF-8') . '</a>';
                echo '</li>';
            }
        }
        ?>

        <br>

        <li class="border-menu-top">
            <a href="logout.php"><i class="material-icons-two-tone">logout</i>Cerrar Sesión</a>
        </li>

    </ul>
</div>
