<?php
/**
 * Project: WeidigWahl
 * Author: AlexanderKaschta
 * License: MIT License
 */

require "Database.php";
include_once "config.php";

session_start();

if (isset($_SESSION['loggedin']) && isset($_SESSION['benutzer']) && isset($_GET['wahl']) && isset($_GET['id'])){
    if ($_SESSION['loggedin'] == 1  && $_SESSION['admin'] == 1){

        $db = new Database();
        $pdo = $db->connect();

        if (is_null($pdo)){
            session_destroy();
            header("Location: ../index.php?errorCode=1 ");
            exit();
        }

        $deleteQuery = $pdo->prepare("DELETE FROM tbl_kurse WHERE tbl_kurse.id = :id;");
        $deleteQuery->bindParam(":id", $_GET['id']);
        $deleteQuery->execute();

        $deleteQuery2 = $pdo->prepare("DELETE FROM tbl_ergebnisse WHERE kurs = :id");
        $deleteQuery2->bindParam(":id", $_GET['id']);
        $deleteQuery2->execute();

        header("Location: ../config_wahl.php?errorCode=9&id=".$_GET['wahl']);
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