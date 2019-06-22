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


if (isset($_SESSION['loggedin']) && isset($_SESSION['benutzer']) && isset($_GET['id'])) {
    if ($_SESSION['loggedin'] == 1 && $_SESSION['admin'] == 1) {

        if (file_exists("../".$_GET['id'])){
            unlink("../".$_GET['id']);
        }

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
