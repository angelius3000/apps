<?php
include('login_functions.inc.php');
include('../Connections/ConDB.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

	// Need two helper files:

	// Check the login:
	list($check, $data) = check_login($conn, $_POST['username'], $_POST['password']);

	if ($check) { // OK!

		// Set the session data:
		session_start();

		$NombreDelUsuario = $data['PrimerNombre'] . " " . $data['SegundoNombre'] . " " . $data['ApellidoPaterno'] . " " . $data['ApellidoMaterno'];

		$_SESSION['Username'] = $data['email'];
		$_SESSION['TIPOUSUARIO'] = $data['TIPODEUSUARIOID'];
		$_SESSION['USUARIOID'] = $data['USUARIOID'];
		$_SESSION['Deshabiitado'] = $data['Deshabilitado'];
                $_SESSION['NombreDelUsuario'] = $NombreDelUsuario;
                $_SESSION['TipoDeUsuario'] = $data['TipoDeUsuario'];
                $_SESSION['NombreCliente'] = $data['NombreCliente'];
               $_SESSION['CLIENTEID'] = $data['CLIENTEID'];
                $seccionInicioRuta = $data['RutaSeccionInicio'] ?? '';
                $seccionInicioSlug = $data['SlugSeccionInicio'] ?? '';
                $_SESSION['SeccionInicioID'] = $data['SECCIONINICIOID'] ?? null;
                $_SESSION['SeccionInicioRuta'] = $seccionInicioRuta;
                $_SESSION['SeccionInicioSlug'] = $seccionInicioSlug;

                $permisos = [];
                $stmtPermisos = @mysqli_prepare(
                    $conn,
                    'SELECT s.Slug, COALESCE(us.PuedeVer, 0) as PuedeVer
                     FROM secciones s
                     LEFT JOIN usuario_secciones us ON us.SECCIONID = s.SECCIONID AND us.USUARIOID = ?
                     ORDER BY s.Orden, s.Nombre'
                );

                if ($stmtPermisos) {
                    mysqli_stmt_bind_param($stmtPermisos, 'i', $_SESSION['USUARIOID']);
                    mysqli_stmt_execute($stmtPermisos);
                    mysqli_stmt_bind_result($stmtPermisos, $slugPermiso, $puedeVerPermiso);

                    while (mysqli_stmt_fetch($stmtPermisos)) {
                        $permisos[$slugPermiso] = (int)$puedeVerPermiso;
                    }

                    mysqli_stmt_close($stmtPermisos);
                }

                $_SESSION['PermisosSecciones'] = $permisos;

                $destino = 'main.php';
                if (!empty($seccionInicioRuta)) {
                    $puedeIngresar = true;
                    if ($seccionInicioSlug !== '') {
                        $puedeIngresar = !isset($permisos[$seccionInicioSlug]) || (int)$permisos[$seccionInicioSlug] === 1;
                    }
                    if ($puedeIngresar) {
                        $destino = $seccionInicioRuta;
                    }
                }

                redirect_user($destino);
       } else { // Unsuccessful!
               // Failed login should return to the login form with an error
               redirect_user('index.php?login=no');
       }

	mysqli_close($conn); // Close the database connection.

} // End of the main submit conditional.
