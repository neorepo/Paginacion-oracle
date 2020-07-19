<?php

require_once 'Db.php';

define('NUMRECORDSPERPAGE', 10);
 
session_start();

$empstartrow = isset($_SESSION['empstartrow']) ? (int) $_SESSION['empstartrow'] : 0;

printcontent( calcstartrow($empstartrow) );

function printcontent($startrow) {
    echo "<div class='container'>";

    $sql = 'SELECT id_asociado AS aid, CONCAT(apellido, " ", nombre) AS nombre, nro_documento AS documento, 
            nro_cuil, condicion_ingreso FROM asociado ORDER BY id_asociado LIMIT ?, ?;';

    $res = execFetchPage($sql, $startrow, NUMRECORDSPERPAGE);
    if ($res) {
        printrecords( ($startrow === 0), $res );
    } else {
        printnorecords();
    }
 
    echo "</div>";
    $_SESSION['empstartrow'] = (int) $startrow;
}

function execFetchPage($sql, $firstrow = 0, $numrows = 1) {
    return Db::query($sql, $firstrow, $numrows);
}

function calcstartrow($empstartrow) {
    $startrow = $empstartrow; // 0
    if (isset($_POST['prevemps'])) {
        $startrow -= NUMRECORDSPERPAGE;
        if ($startrow < 1) {
            $startrow = 0;
        }
    } else if (isset($_POST['nextemps'])) {
        $startrow += NUMRECORDSPERPAGE;
    }
    return($startrow);
}

function printrecords($atfirstrow, $res) {
    echo <<<EOF
    <table class="table table-sm">
    <tr>
    <th>#</th><th>Nombre</th><th>Documento</th><th>Cuil</th><th>Condición de Ingreso</th><th>&nbsp;</th>
    </tr>
    EOF;
    foreach ($res as $row) {
        $name = htmlspecialchars($row['nombre'], ENT_NOQUOTES, 'UTF-8');
        $documento   = htmlspecialchars($row['documento'], ENT_NOQUOTES, 'UTF-8');
        $cuil   = htmlspecialchars($row['nro_cuil'], ENT_NOQUOTES, 'UTF-8');
        $ingreso   = htmlspecialchars($row['condicion_ingreso'], ENT_NOQUOTES, 'UTF-8');
        $aid  = (int) $row['aid'];
        echo "<tr><td>$aid</td>";
        echo "<td>$name</td>";
        echo "<td>$documento</td>";
        echo "<td>$cuil</td>";
        echo "<td>$ingreso</td>";
        echo "<td><a href='#?empid=$aid'>Show</a>";
        echo "</td></tr>\n";
    }
    echo "</table>";
    printnextprev($atfirstrow, count($res));
}

function printnextprev($atfirstrow, $numrows) {
    if (!$atfirstrow || $numrows == NUMRECORDSPERPAGE) {
        echo "<form method='post' action='index.php'><div>";
        if (!$atfirstrow)
            echo "<input type='submit' value='< Anterior' name='prevemps'>";
        if ($numrows == NUMRECORDSPERPAGE)
            echo "<input type='submit' value='Siguiente >' name='nextemps'>";
        echo "</div></form>\n";
    }
}

function printnorecords() {
    if (!isset($_POST['nextemps'])) {
        echo "<p>No Records Found</p>";
    } else {
        echo <<<EOF
        <p>No More Records</p>
        <form method='post' action='index.php'>
            <input type='submit' value='< Previous' name='prevemps'>
        </form>
        EOF;
    }
}