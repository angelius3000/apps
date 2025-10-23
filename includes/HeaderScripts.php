<?php require_once('Connections/ConDB.php');

//initialize the session

if (!isset($_SESSION)) {
    session_start();
}

if (!isset($_SESSION['Username'])) { // Script para Sacar al usuario si no tiene el permiso 

    // Need the functions:
    require('includes/login_functions.inc.php');
    redirect_user();
}

$colname_UsuarioDeLogIn = "-1";
if (isset($_SESSION['Username'])) {
    $colname_UsuarioDeLogIn = $_SESSION['Username'];
}

if ($conn) {
    $query_UsuarioDeLogIn = "SELECT *
FROM usuarios
WHERE usuarios.email = '$colname_UsuarioDeLogIn'";
    $UsuarioDeLogIn = mysqli_query($conn, $query_UsuarioDeLogIn);
    if ($UsuarioDeLogIn) {
        $row_UsuarioDeLogIn = mysqli_fetch_assoc($UsuarioDeLogIn);
        $totalRows_UsuarioDeLogIn = mysqli_num_rows($UsuarioDeLogIn);
    } else {
        $row_UsuarioDeLogIn = null;
        $totalRows_UsuarioDeLogIn = 0;
    }

    if (isset($_SESSION['USUARIOID'])) {
        $permisos = [];
        $stmtPermisos = @mysqli_prepare(
            $conn,
            'SELECT s.Slug, COALESCE(us.PuedeVer, 0) as PuedeVer, s.MostrarEnMenu
             FROM secciones s
             LEFT JOIN usuario_secciones us ON us.SECCIONID = s.SECCIONID AND us.USUARIOID = ?
             ORDER BY s.Orden, s.Nombre'
        );

        if ($stmtPermisos) {
            mysqli_stmt_bind_param($stmtPermisos, 'i', $_SESSION['USUARIOID']);
            mysqli_stmt_execute($stmtPermisos);
            mysqli_stmt_bind_result($stmtPermisos, $slugPermiso, $puedeVerPermiso, $mostrarEnMenu);

            $configuracionSecciones = [];
            while (mysqli_stmt_fetch($stmtPermisos)) {
                $permisos[$slugPermiso] = (int)$puedeVerPermiso;
                $configuracionSecciones[$slugPermiso] = (int)$mostrarEnMenu;
            }

            mysqli_stmt_close($stmtPermisos);
        }

        $_SESSION['PermisosSecciones'] = $permisos;
        $_SESSION['SeccionesVisibles'] = $configuracionSecciones;
    }
} else {
    $row_UsuarioDeLogIn = null;
    $totalRows_UsuarioDeLogIn = 0;
}

if (!function_exists('usuarioTieneAccesoSeccion')) {
    function usuarioTieneAccesoSeccion(string $slug): bool
    {
        return !empty($_SESSION['PermisosSecciones'][$slug]);
    }
}
