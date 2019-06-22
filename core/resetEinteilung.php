<?php
/**
 * Project: WeidigWahl
 * Author: AlexanderKaschta
 * License: MIT License
 */

require "Database.php";
include_once "config.php";

session_start();

if (isset($_SESSION['loggedin']) && isset($_SESSION['benutzer']) && isset($_GET['id'])) {
    if ($_SESSION['loggedin'] == 1 && $_SESSION['admin'] == 1) {

        $db = new Database();
        $pdo = $db->connect();

        if (is_null($pdo)){
            session_destroy();
            header("Location: ../index.php?errorCode=1 ");
            exit();
        }

        $selectQuery = $pdo->prepare("SELECT * FROM tbl_sportwahl WHERE tbl_sportwahl.id = :id;");
        $selectQuery->bindParam(":id", $_GET['id']);
        $selectQuery->execute();

        $rowCount = $selectQuery->rowCount();

        if ($rowCount > 0){
            $row = $selectQuery->fetch();
            $anzahl = (int)$row["anzahl_auswertung"];

            //Durchgehe jeden Kurs
            for ($i = 1; $i <= $anzahl; $i++){

                $selectQuery2 = $pdo->prepare("SELECT * FROM tbl_kurse WHERE sportwahl = :id AND alias LIKE :nr ;");
                $string = "-".$i;
                $selectQuery2->bindParam(":id", $_GET['id']);
                $selectQuery2->bindParam(":nr", $string);
                $selectQuery2->execute();

                $kurs_row = $selectQuery2->fetch();

                $deleteQuery = $pdo->prepare("DELETE FROM tbl_ergebnisse WHERE sportwahl = :id AND kurs = :kurs;");
                $deleteQuery->bindParam(":id", $_GET['id']);
                $deleteQuery->bindParam(":kurs", $kurs_row['id']);
                $deleteQuery->execute();

                $deleteQuery2 = $pdo->prepare("DELETE FROM tbl_kurse WHERE id = :id;");
                $deleteQuery2->bindParam(":id", $kurs_row['id']);
                $deleteQuery2->execute();
            }
        }


        //Setze alle Einteilungen zurück
        $updateQuery = $pdo->prepare("UPDATE tbl_ergebnisse SET akzeptiert = 0 WHERE sportwahl = :id");
        $updateQuery->bindParam(":id", $_GET['id']);
        $updateQuery->execute();

        header("Location: ../config_wahl.php?id=".$_GET['id']);
        exit();

    } else{
        session_destroy();
        header("Location: ../index.php?errorCode=1 ");
        exit();
    }

} else{
    session_destroy();
    header("Location: ../index.php?errorCode=1 ");
    exit();
}