<?php
/**
 * Project: WeidigWahl
 * Author: AlexanderKaschta
 * License: MIT License
 */

require "Database.php";
include_once "config.php";

session_start();

if (isset($_SESSION['loggedin']) && isset($_SESSION['benutzer']) && isset($_GET['wahl'])){
    if ($_SESSION['loggedin'] == 1  && $_SESSION['admin'] == 1){

        $db = new Database();
        $pdo = $db->connect();

        if (is_null($pdo)){
            session_destroy();
            header("Location: ../index.php?errorCode=1 ");
            exit();
        }

        //Lösche alle Kurse der Sportwahl
        $deleteQuery = $pdo->prepare("DELETE FROM tbl_kurse WHERE tbl_kurse.sportwahl = :wahl;");
        $deleteQuery->bindParam(":wahl", $_GET['wahl']);
        $deleteQuery->execute();

        //Lösche alle Zuweisungen der Wahl
        $deleteQuery2 = $pdo->prepare("DELETE FROM tbl_teilnehmer WHERE wahl_typ = 1 AND wahl_id = :wahl;");
        $deleteQuery2->bindParam(":wahl", $_GET['wahl']);
        $deleteQuery2->execute();

        //Lösche alle Ergebnisse/Auswertungen der Wahl
        $deleteQuery3 = $pdo->prepare("DELETE FROM tbl_ergebnisse WHERE tbl_ergebnisse.sportwahl = :wahl;");
        $deleteQuery3->bindParam(":wahl", $_GET['wahl']);
        $deleteQuery3->execute();

        $deleteQuery4 = $pdo->prepare("DELETE FROM tbl_sportwahl WHERE id = :wahl;");
        $deleteQuery4->bindParam(":wahl", $_GET['wahl']);
        $deleteQuery4->execute();

        header("Location: ../main.php");
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