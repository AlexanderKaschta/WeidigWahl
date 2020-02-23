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

        if ($_GET['action'] == 1){
            //Insert a new user into the database

            $insertQuery = $pdo->prepare("INSERT INTO tbl_users (vorname, nachname, benutzername, geburtsdatum, passwort, ist_aktiv, jahrgang, klasse, datum_erstellt, datum_letzte_anderung, kann_reset_anfordern, ist_admin) 
VALUES (:vorname, :nachname, :username, :birth, :pw, :aktiv, :jahr, :klasse, NOW(), NOW(), 1, :admin)");

            //Check if all the required parameters are given for this action
            //TODO: Make sure, that they are not Strings only containing whitespaces
            $vorname = checkParameter("vorname");
            $nachname = checkParameter("nachname");
            $benutzername = checkParameter("username");
            $birth = checkParameter("birth");
            try {
                $date = new DateTime($birth);
            } catch (Exception $e) {
                session_destroy();
                header("Location: ../index.php?errorCode=1 ");
                exit();
            }
            $pw = checkParameter("passwort");
            $jahrgang = checkParameter("jahrgang");
            $klasse = checkParameter("klasse");

            if (!isset($_POST['ist_aktiv'])){
                $aktiv = 0;
            } else{
                $aktiv = 1;
            }

            if (!isset($_POST['ist_admin'])){
                $ist_admin = 0;
            } else{
                $ist_admin = 1;
            }

            //Bind the parameters to the given values

            $insertQuery->bindParam(":vorname", $vorname);
            $insertQuery->bindParam(":nachname", $nachname);
            $insertQuery->bindParam(":username", $benutzername);
            $date_string = $date->format("Y-m-d");
            $insertQuery->bindParam(":birth", $date_string);
            $insertQuery->bindParam(":pw", $pw);
            $insertQuery->bindParam(":aktiv", $aktiv);
            $insertQuery->bindParam(":jahr", $jahrgang);
            $insertQuery->bindParam(":klasse", $klasse);
            $insertQuery->bindParam(":admin", $ist_admin);

            $insertQuery->execute();

            header("Location: ../manage_students.php?errorCode=4");
            exit();

        }
        else if ($_GET['action'] == 2 && isset($_GET['id'])){
            //Update the given user with the given data
            $updateQuery = $pdo->prepare("UPDATE tbl_users SET vorname = :vorname,nachname = :nachname,benutzername = :username,geburtsdatum = :birth, 
            passwort = :pw, ist_aktiv = :aktiv, jahrgang = :jahr, klasse = :klasse, ist_admin = :admin WHERE id = :id; ");

            $vorname = checkParameter("vorname");
            $nachname = checkParameter("nachname");
            $benutzername = checkParameter("username");
            $birth = checkParameter("birth");
            try {
                $date = new DateTime($birth);
            } catch (Exception $e) {
                session_destroy();
                header("Location: ../index.php?errorCode=1 ");
                exit();
            }
            $pw = checkParameter("passwort");
            $jahrgang = checkParameter("jahrgang");
            $klasse = checkParameter("klasse");

            if (!isset($_POST['ist_aktiv'])){
                $aktiv = 0;
            } else{
                $aktiv = 1;
            }

            if (!isset($_POST['ist_admin'])){
                $ist_admin = 0;
            } else{
                $ist_admin = 1;
            }

            $updateQuery->bindParam(":vorname", $vorname);
            $updateQuery->bindParam(":nachname", $nachname);
            $updateQuery->bindParam(":username", $benutzername);
            $date_string = $date->format("Y-m-d");
            $updateQuery->bindParam(":birth", $date_string);
            $updateQuery->bindParam(":pw", $pw);
            $updateQuery->bindParam(":aktiv", $aktiv);
            $updateQuery->bindParam(":jahr", $jahrgang);
            $updateQuery->bindParam(":klasse", $klasse);
            $updateQuery->bindParam(":admin", $ist_admin);
            $updateQuery->bindParam(":id", $_GET['id']);

            $updateQuery->execute();

            header("Location: ../manage_students.php?errorCode=5");
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
} else{
    session_destroy();
    header("Location: ../index.php?errorCode=1 ");
    exit();
}

function checkParameter($name){
    if (!isset($_POST[$name])){
        session_destroy();
        header("Location: ../index.php?errorCode=1");
        exit();
    } else{
        return $_POST[$name];
    }
}