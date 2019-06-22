<?php
/**
 * Project: WeidigWahl
 * Author: AlexanderKaschta
 * License: MIT License
 */

require "Database.php";
include_once "config.php";

session_start();

if (isset($_SESSION['loggedin']) && isset($_SESSION['benutzer']) && isset($_GET['action']) && isset($_GET['wahl'])) {

    if ($_SESSION['loggedin'] == 1 && $_SESSION['admin'] == 1 && is_int((int)$_GET['action']) && is_int((int)$_GET['wahl'])) {

        $db = new Database();
        $pdo = $db->connect();

        if (is_null($pdo)){
            session_destroy();
            header("Location: ../index.php?errorCode=1 ");
            exit();
        }

        if ($_GET['action'] == 1){
            // Füge einen einzelnen Nutzer zur Wahl hinzu
            if(isset($_GET['id']) && is_int((int)$_GET['id'])){
                $data2_query = $pdo->prepare("SELECT * FROM tbl_teilnehmer WHERE benutzer = :id;");
                $data2_query->bindParam(":id", $_GET['id']);
                $data2_query->execute();
                $eintrage = $data2_query->rowCount();

                if ($eintrage == 0){
                    $insertQuery = $pdo->prepare("INSERT INTO tbl_teilnehmer (benutzer, wahl_typ, wahl_id) VALUES (:id, 1, :wahl);");
                    $insertQuery->bindParam(":id", $_GET['id']);
                    $insertQuery->bindParam(":wahl", $_GET['wahl']);
                    $insertQuery->execute();
                }

                header("Location: ../config_wahl.php?id=".$_GET["wahl"]);
                exit();

            } else{
                header("Location: ../nutzer_zuteilen.php?id=".$_GET["wahl"]);
                exit();
            }

        } else if($_GET['action'] == 2){

            //Jahrgang
            if(isset($_GET['id']) && is_int((int)$_GET['id'])){

                $selectQuery = $pdo->prepare("SELECT jahrgang FROM tbl_users WHERE id = :id;");
                $selectQuery->bindParam(":id", $_GET['id']);
                $selectQuery->execute();

                $rows = $selectQuery->rowCount();

                if ($rows != 1){
                    session_destroy();
                    header("Location: ../index.php?errorCode=1 ");
                    exit();
                }

                $reihe = $selectQuery->fetch();
                $jahrgang = $reihe["jahrgang"];

                //Durchgehe alle Schüler aus dem Jahrgang
                $queryBig = $pdo->prepare("SELECT * FROM tbl_users WHERE jahrgang= :jahrgang;");
                $queryBig->bindParam(":jahrgang", $jahrgang);
                $queryBig->execute();

                while ($row = $queryBig->fetch()){

                    //Kontrolliere, ob ein Nutzer schon hinzugefügt worden ist
                    $data2_query = $pdo->prepare("SELECT * FROM tbl_teilnehmer WHERE benutzer = :id AND wahl_id = :wahl;");
                    $data2_query->bindParam(":id", $row['id']);
                    $data2_query->bindParam(":wahl", $_GET['wahl']);
                    $data2_query->execute();

                    $eintrage = $data2_query->rowCount();

                    //Falls nicht, dann füge ihn hinzu
                    if ($eintrage == 0){
                        $insertQuery = $pdo->prepare("INSERT INTO tbl_teilnehmer (benutzer, wahl_typ, wahl_id) VALUES (:id, 1, :wahl);");
                        $insertQuery->bindParam(":id", $row['id']);
                        $insertQuery->bindParam(":wahl", $_GET['wahl']);

                        $insertQuery->execute();
                    }
                }

                header("Location: ../config_wahl.php?id=".$_GET["wahl"]);
                exit();

            } else{
                header("Location: ../nutzer_zuteilen.php?id=".$_GET["wahl"]);
                exit();
            }

        } else if($_GET['action'] == 3){
            //Klasse
            if(isset($_GET['id']) && is_int((int)$_GET['id'])){

                $selectQuery = $pdo->prepare("SELECT klasse FROM tbl_users WHERE id = :id;");
                $selectQuery->bindParam(":id", $_GET['id']);
                $selectQuery->execute();

                $rows = $selectQuery->rowCount();

                if ($rows != 1){
                    session_destroy();
                    header("Location: ../index.php?errorCode=1 ");
                    exit();
                }

                $reihe = $selectQuery->fetch();
                $klasse = $reihe["klasse"];

                $queryBig = $pdo->prepare("SELECT * FROM tbl_users WHERE klasse= :klasse;");
                $queryBig->bindParam(":klasse", $klasse);
                $queryBig->execute();

                while ($row = $queryBig->fetch()){

                    $data2_query = $pdo->prepare("SELECT * FROM tbl_teilnehmer WHERE benutzer = :id AND wahl_id = :wahl;");
                    $data2_query->bindParam(":id", $row['id']);
                    $data2_query->bindParam(":wahl", $_GET['wahl']);
                    $data2_query->execute();

                    $eintrage = $data2_query->rowCount();

                    if ($eintrage == 0){
                        $insertQuery = $pdo->prepare("INSERT INTO tbl_teilnehmer (benutzer, wahl_typ, wahl_id) VALUES (:id, 1, :wahl);");
                        $insertQuery->bindParam(":id", $row['id']);
                        $insertQuery->bindParam(":wahl", $_GET['wahl']);

                        $insertQuery->execute();
                    }

                }

                header("Location: ../config_wahl.php?id=".$_GET["wahl"]);
                exit();

            } else{
                header("Location: ../nutzer_zuteilen.php?id=".$_GET["wahl"]);
                exit();
            }
        }

    } else {
        session_destroy();
        header("Location: ../index.php?errorCode=1 ");
        exit();
    }

} else {
    session_destroy();
    header("Location: ../index.php?errorCode=1 ");
    exit();
}