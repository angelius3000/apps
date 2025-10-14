<?php
function redirect_user($page = 'index.php')
{
        // Determine the protocol being used
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';

        // Build the URL relative to the domain root
        $url = $protocol . $_SERVER['HTTP_HOST'] . '/' . ltrim($page, '/');

        // Redirect the user
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
		$trimmedUsername = trim($username);

		if (!filter_var($trimmedUsername, FILTER_VALIDATE_EMAIL)) {
			$errors[] = 'The email address is not valid.';
		} else {
			$u = $trimmedUsername;
		}
	}

	// Validate the password:
	if (empty($pass)) {
		$errors[] = 'You forgot to enter your password.';
	} else {
		$p = SHA1(trim($pass)); // Aqui encrypto el passowrd
	}

	if (empty($errors)) { // If everything's OK.

		// Sacamos lo que necesita ir en la session:
		$q = "SELECT email, usuarios.TIPODEUSUARIOID, USUARIOID, PrimerNombre, SegundoNombre, ApellidoPaterno, ApellidoMaterno, Deshabilitado, tipodeusuarios.TipoDeUsuario, clientes.NombreCliente, clientes.CLIENTEID, usuarios.SECCIONINICIOID, secciones.Ruta AS RutaSeccionInicio, secciones.Slug AS SlugSeccionInicio FROM usuarios
		LEFT JOIN tipodeusuarios ON usuarios.TIPODEUSUARIOID = tipodeusuarios.TIPODEUSUARIOID 
		LEFT JOIN clientes ON usuarios.CLIENTEID = clientes.CLIENTEID
		LEFT JOIN secciones ON usuarios.SECCIONINICIOID = secciones.SECCIONID
		WHERE email = ? AND Password = ?";

		$stmt = @mysqli_prepare($dbc, $q);

		if ($stmt === false) {
			$errors[] = 'Unable to process the login request at this time.';
		} else {
			mysqli_stmt_bind_param($stmt, 'ss', $u, $p);
			mysqli_stmt_execute($stmt);
			$r = mysqli_stmt_get_result($stmt);

			// Check the result:
			if ($r && mysqli_num_rows($r) == 1) {

				// Fetch the record:
				$row = mysqli_fetch_array($r, MYSQLI_ASSOC);

				if ($r instanceof mysqli_result) {
					mysqli_free_result($r);
				}

				mysqli_stmt_close($stmt);

				// Return true and the record:
				return array(true, $row);
			} else { // Not a match!
				$errors[] = 'The email address and password entered do not match those on file.';
				if ($r instanceof mysqli_result) {
					mysqli_free_result($r);
				}

				mysqli_stmt_close($stmt);
			}
		}
	} // End of empty($errors) IF.

	// Return false and the errors:
	return array(false, $errors);
} // End of check_login() function.
