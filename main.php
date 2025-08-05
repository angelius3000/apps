<?php include("includes/HeaderScripts.php");

if ($_SESSION['TIPOUSUARIO'] != 1) {
    header("Location: index.php");
}

?>

<!DOCTYPE html>
<html lang="en">

<?php include("includes/Header.php") ?>

<body>
    <div class="app full-width-header align-content-stretch d-flex flex-wrap">
        <div class="app-sidebar">
            <div class="logo logo-sm">
                <a href="main.php"> <img src="App/Graficos/Logo/LogoEdison.png" style="max-width :130px;"> </a>
            </div>

            <?php include("includes/Menu.php") ?>

        </div>
        <div class="app-container">
            <div class="search">
                <form>
                    <!-- <input class="form-control" type="text" placeholder="Type here..." aria-label="Search"> -->
                </form>
                <a href="#" class="toggle-search"><i class="material-icons">close</i></a>
            </div>

            <?php include("includes/MenuHeader.php") ?>

            <div class="app-content">
                <div class="content-wrapper">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col">
                                <div class="page-description">
                                    <h1>Full-width Header</h1>


                                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d1470.0296650689004!2d-106.42566092351314!3d31.698776125172365!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x86e75d3d94598019%3A0x1f38d02956229c6d!2sEdison%20Material%20El%C3%A9ctrico!5e0!3m2!1ses-419!2smx!4v1719510658585!5m2!1ses-419!2smx" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>


                                    <?php // ESTE ES EL QUE IMPRIME LAS SESSIONES VARIABLES
                                    echo '<pre>' . print_r($_SESSION, TRUE) . '</pre>';
                                    ?>
                                    <span>Header without spacing to sidebar and page edges. <div class="alert alert-secondary m-t-lg" role="alert">Note! Logo block with user info in it is not compatible with full-width header.</div></span>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Javascripts -->
    <script src="assets/plugins/jquery/jquery-3.5.1.min.js"></script>
    <script src="assets/plugins/bootstrap/js/popper.min.js"></script>
    <script src="assets/plugins/bootstrap/js/bootstrap.min.js"></script>
    <script src="assets/plugins/perfectscroll/perfect-scrollbar.min.js"></script>
    <script src="assets/plugins/pace/pace.min.js"></script>
    <script src="assets/plugins/highlight/highlight.pack.js"></script>
    <script src="assets/js/main.min.js"></script>
    <script src="assets/js/custom.js"></script>

    <script src="App/js/AppCambiarContrasena.js"></script>

</body>

</html>