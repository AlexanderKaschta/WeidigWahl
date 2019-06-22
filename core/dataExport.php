<?php
/**
 * Project: WeidigWahl
 * Author: AlexanderKaschta
 * License: MIT License
 */


require "Database.php";
include_once "config.php";

session_start();

$pageTitle = "Datenexport";

if (isset($_SESSION['loggedin']) && isset($_SESSION['benutzer']) && isset($_GET['id']) && isset($_GET['action'])) {

    if ($_SESSION['loggedin'] == 1 && $_SESSION['admin'] == 1) {

        $db = new Database();
        $pdo = $db->connect();

        if (is_null($pdo)){
            session_destroy();
            header("Location: ../index.php?errorCode=1 ");
            exit();
        }

        if ($_GET['action'] == 1){

            $query = $pdo->prepare("SELECT tbl_users.vorname, tbl_users.nachname, tbl_users.klasse, tbl_users.jahrgang, tbl_users.passwort FROM tbl_users, tbl_teilnehmer WHERE tbl_users.id = tbl_teilnehmer.benutzer AND tbl_teilnehmer.wahl_id = :wahl ORDER BY jahrgang, klasse, nachname, vorname;");
            $query->bindParam(":wahl", $_GET['id']);
            $query->execute();

            header('Content-Type: application/csv');
            header('Content-Disposition: attachment; filename="zugangsdaten.csv";');

            $f = fopen('php://output', 'w');

            while ($row = $query->fetch(PDO::FETCH_ASSOC)){
                fputcsv($f, $row);
            }
            fclose($f);
        } else if($_GET['action'] == 2){
            $query = $pdo->prepare("SELECT tbl_ergebnisse.stimmnummer, tbl_users.vorname, tbl_users.nachname, tbl_users.klasse, tbl_users.jahrgang, tbl_kurse.alias FROM tbl_ergebnisse, tbl_kurse, tbl_users WHERE tbl_ergebnisse.kurs = tbl_kurse.id AND tbl_ergebnisse.benutzer = tbl_users.id AND tbl_ergebnisse.akzeptiert = 1 AND tbl_ergebnisse.sportwahl = :wahl ORDER BY tbl_users.id, tbl_ergebnisse.stimmnummer;");
            $query->bindParam(":wahl", $_GET['id']);
            $query->execute();

            header('Content-Type: application/csv');
            header('Content-Disposition: attachment; filename="wahlergebnisse_'.$_GET['id'].'.csv";');

            $f = fopen('php://output', 'w');

            while ($row = $query->fetch(PDO::FETCH_ASSOC)){
                fputcsv($f, $row);
            }
            fclose($f);
        }


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
