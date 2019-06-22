<?php
/**
 * Project: WeidigWahl
 * Author: AlexanderKaschta
 * License: MIT License
 */

require "Database.php";
include_once "config.php";

session_start();


if (isset($_SESSION['loggedin']) && isset($_GET['id']) && is_int((int)$_GET['id'])){
    if ($_SESSION['loggedin'] == 1 && isset($_SESSION['benutzer']) && $_SESSION['admin'] == 1){

        $db = new Database();
        $pdo = $db->connect();

        if (is_null($pdo)){
            header("Location: ../index.php?errorCode=1 ");
            exit();
        }

        $query = $pdo->prepare("INSERT INTO tbl_kurse(name, beschreibung, lehrer, min, max, von, bis, sportwahl, alias) 
        VALUES (:name_kurs, :beschreibung, :lehrer, :min, :max, :von, :bis, :sportwahl, :alias);");

        $query->bindParam(":name_kurs", $name);
        $query->bindParam(":beschreibung", $beschreibung);
        $query->bindParam(":lehrer", $lehrer);
        $query->bindParam(":min", $min);
        $query->bindParam(":max", $max);
        $query->bindParam(":von", $von);
        $query->bindParam(":bis", $bis);
        $query->bindParam(":sportwahl", $wahl);
        $query->bindParam(":alias", $alias);

        $name = checkParameter("name");
        $beschreibung = checkParameter("beschreibung");
        $lehrer = checkParameter("lehrer");
        $min = checkParameter("min");
        $max = checkParameter("max");
        $von = checkParameter("date_start");
        $bis = checkParameter("date_end");
        $wahl = $_GET['id'];
        $alias = checkParameter("alias");

        if ($min < 1){
            throwLocalError(1, $wahl);
        }
        if ($max < 1){
            throwLocalError(2, $wahl);
        }
        if ($min > $max){
            throwLocalError(2, $wahl);
        }
        $dateTimestamp1 = strtotime($von);
        $dateTimestamp2 = strtotime($bis);
        if (!($dateTimestamp1 < $dateTimestamp2)){
            throwLocalError(3, $wahl);
        }

        $query->execute();

        header("Location: ../config_wahl.php?id=".$_GET['id']);
        exit();


    }else{
        session_destroy();
        header("Location: ../index.php?errorCode=1 ");
        exit();
    }

} else{
    session_destroy();
    header("Location: ../index.php?errorCode=1 ");
    exit();
}

function checkParameter($name){
    if (!isset($_POST[$name])){
        session_destroy();
        header("Location: ../index.php?errorCode=1");
        exit();
    } else{
        return $_POST[$name];
    }
}


function throwLocalError($message_code, $wahl){
    header("Location: ../new_kurs.php?errorCode=".$message_code."&id=".$wahl);
    exit();
}