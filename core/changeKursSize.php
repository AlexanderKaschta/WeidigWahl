<?php
/**
 * Project: WeidigWahl
 * Author: AlexanderKaschta
 * License: MIT License
 */

require "Database.php";
include_once "config.php";

session_start();

if (isset($_SESSION['loggedin']) && isset($_SESSION['benutzer']) && isset($_GET['id']) && isset($_GET['wahl'])) {
    if ($_SESSION['loggedin'] == 1 && $_SESSION['admin'] == 1) {

        $db = new Database();
        $pdo = $db->connect();

        if (is_null($pdo)){
            session_destroy();
            header("Location: ../index.php?errorCode=1 ");
            exit();
        }

        if (isset($_POST['min']) && isset($_POST['max'])){

            //Kontrolliere die Parameter

            if (isInteger($_POST['min']) && isInteger($_POST['max'])){
                $min = (int)$_POST['min'];
                $max = (int)$_POST['max'];
            }else{
                session_destroy();
                header("Location: ../index.php?errorCode=1 ");
                exit();
            }


            if ($min < 0 || $max < 0 || $min > $max){
                session_destroy();
                header("Location: ../config_wahl.php?errorCode=8&id=".$_GET['wahl']);
                exit();
            }

            $query = $pdo->prepare("UPDATE tbl_kurse SET tbl_kurse.min = :min, tbl_kurse.max = :max WHERE tbl_kurse.id = :id;");
            $query->bindParam(":min", $min);
            $query->bindParam(":max", $max);
            $query->bindParam(":id", $_GET['id']);
            $query->execute();


            header("Location: ../config_wahl.php?id=".$_GET['wahl']);
            exit();

        } else{
            session_destroy();
            header("Location: ../config_wahl.php?errorCode=8&id=".$_GET['wahl']);
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


function isInteger($input){
    return(ctype_digit(strval($input)));
}