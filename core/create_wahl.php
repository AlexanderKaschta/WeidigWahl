<?php
/**
 * Project: WeidigWahl
 * Author: AlexanderKaschta
 * License: MIT License
 */

require "Database.php";
include_once "config.php";

session_start();

if (isset($_SESSION['loggedin']) && isset($_SESSION['benutzer'])){
    if ($_SESSION['loggedin'] == 1  && $_SESSION['admin'] == 1){
        //Kontrolliere ob alle Variablen gegeben sind

        //Prepare
        $db = new Database();
        $pdo = $db->connect();

        if (is_null($pdo)){
            header("Location: ../index.php?errorCode=1 ");
            exit();
        }

        $query = $pdo->prepare("INSERT INTO tbl_sportwahl(name_wahl, beschreibung, ist_aktiv, datum_erstellt, datum_beginn,
 datum_ende, erstellt_von, pdf_pfad, anzahl_wahl, anzahl_auswertung) VALUES (:wahl_name, :beschreibung, 0, NOW(), :start_wahl, :end_wahl, :creator, '', :wahl, :auswertung);");

        $query->bindParam(":wahl_name", $name);
        $query->bindParam(":beschreibung", $beschreibung);
        $query->bindParam(":start_wahl", $date_start);
        $query->bindParam(":end_wahl", $date_end);
        $query->bindParam(":creator", $_SESSION['id']);
        $query->bindParam(":wahl", $wahl_nr);
        $query->bindParam(":auswertung", $auswertung);

        //Save the values
        $name = checkParameter("name");
        $beschreibung = checkParameter("beschreibung");
        $wahl_nr = checkParameter("wahl_nr");
        $auswertung = checkParameter("auswertung");
        $date_start = checkParameter("date_start");
        $date_end = checkParameter("date_end");

        //Check the values
        if ($wahl_nr < 1){
            throwLocalError(1);
        }
        if ($auswertung < 1){
            throwLocalError(1);
        }
        if ($auswertung > $wahl_nr){
            throwLocalError(1);
        }
        $dateTimestamp1 = strtotime($date_start);
        $dateTimestamp2 = strtotime($date_end);
        if (!($dateTimestamp1 < $dateTimestamp2)){
            throwLocalError(2);
        }

        //Insert the data into the database
        $query->execute();

        header("Location: ../main.php");
        exit();

    } else{
        session_destroy();
        header("Location: ../index.php?errorCode=1");
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

function throwLocalError($message_code){
    header("Location: ../new_wahl.php?errorCode=".$message_code);
    exit();
}