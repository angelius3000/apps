<?php
function redirect_user($page = 'index.php')
{

	// Start defining the URL...
	// URL is http:// plus the host name plus the current directory:


	if ($_SERVER['HTTP_HOST'] == "local.edison:8888") {
		$url = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);
	} else if ($_SERVER['HTTP_HOST'] == "localhost") {
		$url = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);
	} else {
		$url = 'https://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);
	}

	// Remove any trailing slashes:
	$url = rtrim($url, '/\\');

	// Add the page:
	$url .= '/' . $page;

	// Redirect the user:
	header("Location: $url");
	exit(); // Quit the script.

} // End of redirect_user() function.

function check_login($dbc, $username = '', $pass = '')
{

	$errors = array(); // Initialize error array.

	// Validate the email address:
	if (empty($username)) {
		$errors[] = 'You forgot to enter your email address.';
	} else {
		$u = mysqli_real_escape_string($dbc, trim($username));
	}

	// Validate the password:
	if (empty($pass)) {
		$errors[] = 'You forgot to enter your password.';
	} else {
		$p = mysqli_real_escape_string($dbc, trim($pass));
		$p = SHA1($p); // Aqui encrypto el passowrd

	}

	if (empty($errors)) { // If everything's OK.

		// Sacamos lo que necesita ir en la session:
		$q = "SELECT email, usuarios.TIPODEUSUARIOID, USUARIOID, PrimerNombre, SegundoNombre, ApellidoPaterno, ApellidoMaterno, Deshabilitado, tipodeusuarios.TipoDeUsuario, clientes.NombreCliente, clientes.CLIENTEID FROM usuarios
		LEFT JOIN tipodeusuarios ON usuarios.TIPODEUSUARIOID = tipodeusuarios.TIPODEUSUARIOID 
		LEFT JOIN clientes ON usuarios.CLIENTEID = clientes.CLIENTEID
		WHERE email='$u' AND Password ='$p'";		// 
		$r = @mysqli_query($dbc, $q); // Run the query.

		// Check the result:
		if (mysqli_num_rows($r) == 1) {

			// Fetch the record:
			$row = mysqli_fetch_array($r, MYSQLI_ASSOC);

			// Return true and the record:
			return array(true, $row);
		} else { // Not a match!
			$errors[] = 'The email address and password entered do not match those on file.';
		}
	} // End of empty($errors) IF.

	// Return false and the errors:
	return array(false, $errors);
} // End of check_login() function.
