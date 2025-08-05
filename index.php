<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Responsive Admin Dashboard Template">
    <meta name="keywords" content="admin,dashboard">
    <meta name="author" content="stacks">
    <!-- The above 6 meta tags *must* come first in the head; any other head content must come *after* these tags -->

    <!-- Title -->
    <title>Edison - Reparto</title>

    <!-- Styles -->
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Material+Icons|Material+Icons+Outlined|Material+Icons+Two+Tone|Material+Icons+Round|Material+Icons+Sharp" rel="stylesheet">
    <link href="assets/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/plugins/perfectscroll/perfect-scrollbar.css" rel="stylesheet">
    <link href="assets/plugins/pace/pace.css" rel="stylesheet">


    <!-- Theme Styles -->
    <link href="assets/css/main.css" rel="stylesheet">
    <link href="assets/css/custom.css" rel="stylesheet">

    <link rel="apple-touch-icon" sizes="57x57" href="App/Graficos/Favicon/apple-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="60x60" href="App/Graficos/Favicon/apple-icon-60x60.png">
    <link rel="apple-touch-icon" sizes="72x72" href="App/Graficos/Favicon/apple-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="76x76" href="App/Graficos/Favicon/apple-icon-76x76.png">
    <link rel="apple-touch-icon" sizes="114x114" href="App/Graficos/Favicon/apple-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="120x120" href="App/Graficos/Favicon/apple-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="144x144" href="App/Graficos/Favicon/apple-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="152x152" href="App/Graficos/Favicon/apple-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="App/Graficos/Favicon/apple-icon-180x180.png">
    <link rel="icon" type="image/png" sizes="144x144" href="App/Graficos/Favicon/android-icon-144x144.png">
    <link rel="icon" type="image/png" sizes="192x192" href="App/Graficos/Favicon/android-icon-192x192.png">
    <link rel="icon" type="image/png" sizes="32x32" href="App/Graficos/Favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="App/Graficos/Favicon/favicon-96x96.png">
    <link rel="icon" type="image/png" sizes="16x16" href="App/Graficos/Favicon/favicon-16x16.png">
    <link rel="manifest" href="App/Graficos/Favicon/manifest.json">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="App/Graficos/Favicon/ms-icon-144x144.png">
    <meta name="theme-color" content="#ffffff">


    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>

<body>
    <div class="app app-auth-sign-in align-content-stretch d-flex flex-wrap justify-content-end">
        <div class="app-auth-background">

        </div>
        <div class="app-auth-container">
            <div class="logomain">
                <a href="main.php"> <img src="App/Graficos/Logo/LogoEdison.png" style="max-width :250px;"> </a>
            </div>
            <br>

            <?php // echo "Este es el HTTPHOST <strong>" . $_SERVER['HTTP_HOST'] . '</strong>'; 
            ?>

            <?php if (isset($_GET['login']) && $_GET['login'] == 'no') { ?>

                <span class="text-danger pb-4"><strong>Tu usuario o contraseña es incorrecta. Vuelve a intentar</strong></span>

            <?php } ?>

            <form action="includes/login.php" method="POST">
                <div class="auth-credentials m-b-xxl">
                    <label for="username" class="form-label">Email</label>

                    <input type="email" class="form-control m-b-md" id="username" name="username" aria-describedby="username" placeholder="">

                    <label for="password" class="form-label">Contraseña</label>

                    <input type="password" class="form-control" id="password" name="password" aria-describedby="password" placeholder="">
                </div>

                <div class="auth-submit">
                    <button type="submit" href="#" class="btn btn-primary">Entrar</button>
                    <a href="OlvidasteTuPassword.php" class="auth-forgot-password float-end">¿Olvidaste tu contraseña?</a>
                </div>


            </form>

            <div class="divider"></div>

        </div>
    </div>

    <!-- Javascripts -->
    <script src="assets/plugins/jquery/jquery-3.5.1.min.js"></script>
    <script src="assets/plugins/bootstrap/js/bootstrap.min.js"></script>
    <script src="assets/plugins/perfectscroll/perfect-scrollbar.min.js"></script>
    <script src="assets/plugins/pace/pace.min.js"></script>
    <script src="assets/js/main.min.js"></script>
    <script src="assets/js/custom.js"></script>
</body>

</html>