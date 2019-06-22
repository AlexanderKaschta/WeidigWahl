<?php
/**
 * Project: WeidigWahl
 * Author: AlexanderKaschta
 * License: MIT License
 */

require_once "config.php";
include "Database.php";

session_start();

if (isset($_SESSION['loggedin']) && isset($_SESSION['admin']) && isset($_SESSION['benutzer']) && isset($_GET['id']) && isset($_GET['wahl'])) {
    if ($_SESSION['loggedin'] == 1  && $_SESSION['admin'] == 1) {

        $db = new Database();
        $pdo = $db->connect();

        if (is_null($pdo)){
            session_destroy();
            header("Location: ../index.php?errorCode=1 ");
            exit();
        }

        $deleteTeilnehmer = $pdo->prepare("DELETE FROM tbl_teilnehmer WHERE wahl_id = :wahl AND benutzer = :id LIMIT 1;");
        $deleteTeilnehmer->bindParam(":wahl", $_GET['wahl']);
        $deleteTeilnehmer->bindParam(":id", $_GET['id']);
        $deleteTeilnehmer->execute();

        header("Location: ../config_wahl.php?errorCode=6&id=".$_GET['wahl']);
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