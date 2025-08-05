<?php

function FechasTextoCompleto($FechaDeMysql) // Función para convertir la fecha MYSQL a Fecha Textual

{

    $day = date("l", strtotime($FechaDeMysql));

    $daynum = date("j", strtotime($FechaDeMysql));

    $month = date("F", strtotime($FechaDeMysql));

    $year = date("Y", strtotime($FechaDeMysql));


    switch ($day) {

        case "Monday":
            $day = "Lunes";
            break;

        case "Tuesday":
            $day = "Martes";
            break;

        case "Wednesday":
            $day = "Mi&eacute;rcoles";
            break;

        case "Thursday":
            $day = "Jueves";
            break;

        case "Friday":
            $day = "Viernes";
            break;

        case "Saturday":
            $day = "S&aacute;bado";
            break;

        case "Sunday":
            $day = "Domingo";
            break;

        default:
            $day = "";
            break;
    }



    switch ($month) {

        case "January":
            $month = "Enero";
            break;

        case "February":
            $month = "Febrero";
            break;

        case "March":
            $month = "Marzo";
            break;

        case "April":
            $month = "Abril";
            break;

        case "May":
            $month = "Mayo";
            break;

        case "June":
            $month = "Junio";
            break;

        case "July":
            $month = "Julio";
            break;

        case "August":
            $month = "Agosto";
            break;

        case "September":
            $month = "Septiembre";
            break;

        case "October":
            $month = "Octubre";
            break;

        case "November":
            $month = "Noviembre";
            break;

        case "December":
            $month = "Diciembre";
            break;

        default:
            $month = "";
            break;
    }

    if ($FechaDeMysql) {

        return $day . ', ' . $daynum . " de " . $month . ", " . $year;
    } else {

        return "Sin Fecha";
    }
}

function FechasTextoCalendario($FechaDeMysql) // Función para convertir la fecha MYSQL a Fecha Textual

{

    $day = date("l", strtotime($FechaDeMysql));

    $daynum = date("j", strtotime($FechaDeMysql));

    $month = date("F", strtotime($FechaDeMysql));

    $year = date("Y", strtotime($FechaDeMysql));



    switch ($day) {

        case "Monday":
            $day = "Lunes";
            break;

        case "Tuesday":
            $day = "Martes";
            break;

        case "Wednesday":
            $day = "Mi&eacute;rcoles";
            break;

        case "Thursday":
            $day = "Jueves";
            break;

        case "Friday":
            $day = "Viernes";
            break;

        case "Saturday":
            $day = "S&aacute;bado";
            break;

        case "Sunday":
            $day = "Domingo";
            break;

        default:
            $day = "";
            break;
    }



    switch ($month) {

        case "January":
            $month = "Enero";
            break;

        case "February":
            $month = "Febrero";
            break;

        case "March":
            $month = "Marzo";
            break;

        case "April":
            $month = "Abril";
            break;

        case "May":
            $month = "Mayo";
            break;

        case "June":
            $month = "Junio";
            break;

        case "July":
            $month = "Julio";
            break;

        case "August":
            $month = "Agosto";
            break;

        case "September":
            $month = "Septiembre";
            break;

        case "October":
            $month = "Octubre";
            break;

        case "November":
            $month = "Noviembre";
            break;

        case "December":
            $month = "Diciembre";
            break;

        default:
            $month = "";
            break;
    }

    if ($FechaDeMysql) {

        return $daynum . " de " . $month . ", " . $year;
    } else {

        return "Sin Fecha";
    }
}

function SoloFecha($FechaDeMysql) // Función para convertir la fecha MYSQL a Fecha Textual

{

    $day = date("l", strtotime($FechaDeMysql));

    $daynum = date("j", strtotime($FechaDeMysql));

    $month = date("m", strtotime($FechaDeMysql));

    $year = date("Y", strtotime($FechaDeMysql));



    return $daynum . "/" . $month . "/" . $year;
}

function random_num($size)
{ // Funcion para crear el HASH que lleva la activacion del usuario
    $alpha_key = '';
    $keys = range('A', 'Z');

    for ($i = 0; $i < 2; $i++) {
        $alpha_key .= $keys[array_rand($keys)];
    }

    $length = $size - 2;

    $key = '';
    $keys = range(0, 9);

    for ($i = 0; $i < $length; $i++) {
        $key .= $keys[array_rand($keys)];
    }

    return $alpha_key . $key;
}
