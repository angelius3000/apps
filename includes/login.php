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


                if ($_SERVER['HTTP_HOST'] == "local.edison:8888") {

                        echo "<script>window.location.href='http://local.edison:8888/main.php';</script>";
                        exit;
                } else if ($_SERVER['HTTP_HOST'] == "localhost") {

                        echo "<script>window.location.href='http://localhost/DesarrolloWeb/edisonreparto/main.php';</script>";
                        exit;
                } else {

                        echo "<script>window.location.href='https://reparto.edison.com.mx/main.php';</script>";
                        exit;
                }
	} else { // Unsuccessful!

		if ($_SERVER['HTTP_HOST'] == "local.edison:8888") {
			echo "<script>window.location.href='http://local.edison:8888/index.php?login=no';</script>";
			exit;
		} else if ($_SERVER['HTTP_HOST'] == "localhost/edisonreparto") {
			echo "<script>window.location.href=''http://localhost/index.php?login=no';</script>";
			exit;
		} else {
			echo "<script>window.location.href='https://reparto.edison.com.mx/index.php?login=no';</script>";
			exit;
		}
	}

	mysqli_close($conn); // Close the database connection.

} // End of the main submit conditional.
