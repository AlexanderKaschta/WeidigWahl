<?php
/**
 * Project: WeidigWahl
 * Author: AlexanderKaschta
 * License: MIT License
 */

require "Database.php";
include_once "config.php";

session_start();

//Kontrolliere, ob die benötigten Parameter vorhanden sind
if (isset($_SESSION['loggedin']) && isset($_SESSION['benutzer']) && isset($_GET['nr']) && isset($_GET['id']) && isset($_POST['kurs'])){

    //Stelle sicher, dass die Parameter auch die korrekte Form haben
    if($_SESSION['loggedin'] == 1 && is_int((int)$_GET['id']) && is_int((int)$_GET['nr'])){

        //Erstelle eine Datenbankverbindung
        $db = new Database();
        $pdo = $db->connect();

        if (is_null($pdo)){
            session_destroy();
            header("Location: ../index.php?errorCode=1 ");
            exit();
        }

        //Kontrolliere ob der Nutzer an der Wahl teilnehmen darf
        $check = $pdo->prepare("SELECT * FROM tbl_teilnehmer WHERE wahl_id = :wahl AND benutzer = :id;");
        $check->bindParam(":wahl", $_GET['id']);
        $check->bindParam(":id", $_SESSION['id']);

        $check->execute();

        $rows = $check->rowCount();

        if ($rows != 1){
            header("Location: ../main.php?errorCode=1 ");
            exit();
        }

        $check2 = $pdo->prepare("SELECT * FROM tbl_sportwahl WHERE id = :id AND ist_aktiv=1;");
        $check2->bindParam(":id", $_GET['id']);
        $check2->execute();
        $check2_rows = $check2->rowCount();

        if ($check2_rows != 1){
            header("Location: ../main.php?errorCode=1 ");
            exit();
        }
        $check2_row =$check2->fetch();

        $nr = (int)$_GET['nr'];
        if ($nr < 1){
            session_destroy();
            header("Location: ../index.php?errorCode=1 ");
            exit();
        }

        if ($nr > $check2_row['anzahl_wahl']){
            session_destroy();
            header("Location: ../index.php?errorCode=1 ");
            exit();
        }

        //Besorge das aktuelle Datum
        try {
            $aktuell = new DateTime(date('Y-m-d'));
            $date2 = new DateTime($check2_row['datum_ende']);
        } catch (Exception $e) {
            session_destroy();
            header("Location: ../index.php?errorCode=1 ");
            exit();
        }

        //Wenn date2 kleiner als aktuell ist,
        if ($date2 < $aktuell){
            //dann ist die Wahl abgelaufen
            header("Location: ../main.php?errorCode=4 ");
            exit();
        }

        if ($_POST['kurs'] == -1){
            header("Location: ../wahl.php?nr=".$_GET['nr']."&id=".$_GET['id']."&errorcode=1");
            exit();
        }

        $kurs_check = $pdo->prepare("SELECT * FROM tbl_kurse WHERE id = :id AND sportwahl = :wahl;");
        $kurs_check->bindParam(":wahl", $_GET['id']);
        $kurs_check->bindParam(":id", $_POST['kurs']);
        $kurs_check->execute();

        $kurs_rows = $kurs_check->rowCount();

        if ($kurs_rows != 1){
            header("Location: ../wahl.php?nr=".$_GET['nr']."&id=".$_GET['id']."&errorcode=1");
            exit();
        }

        //Wenn all diese Bedingungen erfüllt sind, dann füge die Wahl in die Tabelle ein
        $exist_check = $pdo->prepare("SELECT * FROM tbl_ergebnisse WHERE stimmnummer = :nr AND benutzer = :nutzer AND sportwahl = :wahl;");
        $exist_check->bindParam(":nr", $_GET['nr']);
        $exist_check->bindParam(":nutzer", $_SESSION['id']);
        $exist_check->bindParam(":wahl", $_GET['id']);
        $exist_check->execute();
        $exist_rows = $exist_check->rowCount();

        if ($exist_rows == 0){
            $insert_query = $pdo->prepare("INSERT INTO tbl_ergebnisse (sportwahl, stimmnummer, kurs, benutzer) VALUES (:wahl, :nr, :kurs, :benutzer);");
            $insert_query->bindParam(":wahl", $_GET['id']);
            $insert_query->bindParam(":nr", $_GET['nr']);
            $insert_query->bindParam(":kurs", $_POST['kurs']);
            $insert_query->bindParam(":benutzer", $_SESSION['id']);
            $insert_query->execute();
        } else{
            $update_query = $pdo->prepare("UPDATE tbl_ergebnisse SET kurs = :kurs WHERE stimmnummer = :nr AND benutzer = :nutzer AND sportwahl = :wahl;");
            $update_query->bindParam(":wahl", $_GET['id']);
            $update_query->bindParam(":nr", $_GET['nr']);
            $update_query->bindParam(":kurs", $_POST['kurs']);
            $update_query->bindParam(":nutzer", $_SESSION['id']);
            $update_query->execute();
        }

        //Erfolgreich in die Tabelle eingefügt

        if ($nr == $check2_row['anzahl_wahl']){
            header("Location: ../success.php");
            exit();
        } else{
            $neu = $nr + 1;
            header("Location: ../wahl.php?nr=".$neu."&id=".$_GET['id']."&errorCode=2");
            exit();
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