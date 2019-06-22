<?php
/**
 * Project: WeidigWahl
 * Author: AlexanderKaschta
 * License: MIT License
 */

require "Database.php";
include_once "config.php";

session_start();

if (isset($_SESSION['loggedin']) && isset($_SESSION['benutzer']) && isset($_GET['action']) && isset($_GET['id'])) {
    if ($_SESSION['loggedin'] == 1 && $_SESSION['admin'] == 1) {

        $db = new Database();
        $pdo = $db->connect();

        if ($_GET['action'] == 1){
            //Wahl aktivieren
            $wert = 1;
        } else if ($_GET['action'] == 2){
            //Wahl deaktivieren
            $wert = 0;
        } else{
            //Fehler schmeißen ;D
            session_destroy();
            header("Location: ../index.php?errorCode=1 ");
            exit();
        }

        $request = $pdo->prepare("UPDATE tbl_sportwahl SET ist_aktiv = :wert WHERE id = :id;");
        $request->bindParam(":wert", $wert);
        $request->bindParam(":id", $_GET['id']);

        $request->execute();

        if ($wert == 1){
            header("Location: ../config_wahl.php?errorCode=2&id=".$_GET['id']);
            exit();
        } else if($wert == 0){
            header("Location: ../config_wahl.php?errorCode=3&id=".$_GET['id']);
            exit();
        }

    } else{
        session_destroy();
        header("Location: ../index.php?errorCode=1 ");
        exit();
    }

}else{
    session_destroy();
    header("Location: ../index.php?errorCode=1 ");
    exit();
}
