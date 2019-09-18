<?php
/**
 * Project: WeidigWahl
 * Author: AlexanderKaschta
 * License: MIT License
 */

require "Database.php";
include_once "config.php";

session_start();

if (isset($_SESSION['loggedin']) && isset($_SESSION['benutzer']) && isset($_GET['action'])){
    if ($_SESSION['loggedin'] == 1  && $_SESSION['admin'] == 1 && is_int((int)$_GET['action'])){

        $db = new Database();
        $pdo = $db->connect();

        if (is_null($pdo)){
            session_destroy();
            header("Location: ../index.php?errorCode=1 ");
            exit();
        }

        if ((int)$_GET['action'] == 1){
            //Lösche alle Accounts, die keine Administrator-Rechte besitzen.
            $deleteQuery = $pdo->prepare("DELETE FROM tbl_users WHERE ist_admin = 0;");
            $deleteQuery->execute();
            header("Location: ../manage_students.php?errorCode=1");
            exit();
        } else if((int)$_GET['action'] == 2){

            if (isset($_GET['id']) && is_int((int)$_GET['id']) && $_GET['id'] != $_SESSION['id']){
                //Delete a single user account

                //Gehe außerdem sicher, dass dies nicht der Account ist, der gerade aktiv die Software benutzt
                $deleteSingle = $pdo->prepare("DELETE FROM tbl_users WHERE id = :id LIMIT 1;");
                $deleteSingle->bindParam(":id", $_GET['id']);
                $deleteSingle->execute();

                header("Location: ../manage_students.php?errorCode=2");
                exit();

            } else{
                session_destroy();
                header("Location: ../index.php?errorCode=1 ");
                exit();
            }
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