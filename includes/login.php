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

               // Redirect all users to the application selector
               redirect_user('main.php');
       } else { // Unsuccessful!
               // Failed login should return to the login form with an error
               redirect_user('index.php?login=no');
       }

	mysqli_close($conn); // Close the database connection.

} // End of the main submit conditional.
