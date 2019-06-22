<?php
/**
 * Project: WeidigWahl
 * Author: AlexanderKaschta
 * License: MIT License
 */

require "Database.php";
include_once "config.php";

//Starte die Session
session_start();

//Verzeichnis für die Uploads
$uploadDir = "../uploads/";
$uploadDirDocs = "../docs/";


//Maximale Größe
$maxFilesize = 500*1024*1024;

//Datei
$file = $uploadDir.basename($_FILES["datei"]["name"]);

if (isset($_SESSION['loggedin']) && isset($_SESSION['admin']) && isset($_POST['submit']) && isset($_POST['action'])) {

    //Kontrolliere, ob der Nutzer angemeldet ist und Administrator ist
    if ($_SESSION['loggedin'] == 1 && isset($_SESSION['benutzer']) && $_SESSION['admin'] == 1) {

        $actions = array("1", "2");

        //Wenn die angegebene Aktion korrekt ist
        if (is_int((int)$_POST['action']) && in_array($_POST['action'], $actions)){

            //Dateityp bestimmen
            $filetype = strtolower(pathinfo($_FILES["datei"]["name"],PATHINFO_EXTENSION));


            if($_FILES["datei"]["size"] > $maxFilesize){

                if (isset($_POST['extra'])){
                    header("Location: ../uploadFile.php?errorCode=2&action=".$_POST['action']."&extra=".$_POST['extra']);
                } else{
                    header("Location: ../uploadFile.php?errorCode=2&action=".$_POST['action']);
                }
                exit();
            }

            if ($_FILES["datei"]["error"] !== 0){
                if (isset($_POST['extra'])){
                    header("Location: ../uploadFile.php?errorCode=3&action=".$_POST['action']."&extra=".$_POST['extra']);
                } else{
                    header("Location: ../uploadFile.php?errorCode=3&action=".$_POST['action']);
                }
                exit();
            }

            if ($_POST['action'] == 1){
                //Erlaubte Dateitypen
                $allowedFiletypes = array("csv", "txt");

                if(!in_array($filetype, $allowedFiletypes)) {
                    //Ungültige Datei
                    if (isset($_POST['extra'])){
                        header("Location: ../uploadFile.php?errorCode=1&action=".$_POST['action']."&extra=".$_POST['extra']);
                    } else{
                        header("Location: ../uploadFile.php?errorCode=1&action=".$_POST['action']);
                    }
                    exit();
                }

                $dest = $uploadDir."students.".$filetype;

                if (file_exists($dest)){
                    $current_id = 1;
                    do {
                        $dest = $uploadDir."students".$current_id.'.'.$filetype;
                        $current_id++;
                    } while(file_exists($dest));
                }
            } else if ($_POST['action'] == 2){
                //Erlaubte Dateitypen
                $allowedFiletypes = array("pdf");

                if(!in_array($filetype, $allowedFiletypes)) {
                    //Ungültige Datei
                    if (isset($_POST['extra'])){
                        header("Location: ../uploadFile.php?errorCode=1&action=".$_POST['action']."&extra=".$_POST['extra']);
                    } else{
                        header("Location: ../uploadFile.php?errorCode=1&action=".$_POST['action']);
                    }
                    exit();
                }

                if (!isset($_POST['extra'])){
                    header("Location: ../uploadFile.php?errorCode=1&action=".$_POST['action']);
                    exit();
                }

                $dest = $uploadDirDocs.$_POST['extra'].".".$filetype;

                if (file_exists($dest)){
                    $current_id = 1;
                    do {
                        $dest = $uploadDirDocs.$_POST['extra'].$current_id.'.'.$filetype;
                        $current_id++;
                    } while(file_exists($dest));
                }

            }

            move_uploaded_file($_FILES["datei"]['tmp_name'], $dest);

            //Redirect to the correct page

            if ($_POST['action'] == 1){
                header("Location: parseStudentCSV.php?file=".$dest);
                exit();
            } else if($_POST['action'] == 2){
                //Define the path
                header("Location: ../main.php");
                exit();
            }

        } else{
            //Fehler
            session_destroy();
            header("Location: ../index.php?errorCode=1 ");
            exit();
        }

    } else{
        //Fehler
        session_destroy();
        header("Location: ../index.php?errorCode=1 ");
        exit();
    }

} else{
    //Fehler
    session_destroy();
    header("Location: ../index.php?errorCode=1 ");
    exit();
}