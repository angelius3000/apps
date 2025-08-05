<div class="app-menu">
    <ul class="accordion-menu">
        <!--   <li class="sidebar-title">
            Apps
        </li> -->
        <li>
            <a href="main.php"><i class="material-icons-two-tone">dashboard</i>Aplicaciones</a>
        </li>
        <li>
            <a href="charolas.php"><i class="material-icons-two-tone">view_day</i>Charolas</a>
        </li>
        <li>
            <a href="Repartos.php"><i class="material-icons-two-tone">local_shipping</i>Reparto</a>
        </li>
        <?php if ($_SESSION['TIPOUSUARIO'] == '1') { ?>
            <li>
                <a href="Clientes.php"><i class="material-icons-two-tone">people</i>Clientes</a>
            </li>

            <li>
                <a href="Usuarios.php"><i class="material-icons-two-tone">person_add_alt</i>Usuarios</a>
            </li>
        <?php } ?>

        <br>

        <li class="border-menu-top">
            <a href="logout.php"><i class="material-icons-two-tone">logout</i>Cerrar Sesión</a>
        </li>

    </ul>
</div>
