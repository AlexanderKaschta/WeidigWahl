<?php
/**
 * Project: WeidigWahl
 * Author: AlexanderKaschta
 * License: MIT License
 */

require "Database.php";
include_once "config.php";

session_start();

if (isset($_SESSION['loggedin']) && isset($_SESSION['admin']) && isset($_SESSION['benutzer']) && isset($_GET['file'])) {

    if ($_SESSION['loggedin'] == 1 && $_SESSION['admin'] == 1) {

        $db = new Database();
        $pdo = $db->connect();

        if (is_null($pdo)){
            session_destroy();
            header("Location: ../index.php?errorCode=1 ");
            exit();
        }

        $file = $_GET['file'];

        if (!file_exists($file)){
            header("Location: ../manage_students.php?errorCode=3 ");
            exit();
        }

        $handle = fopen($file, "r");
        if ($handle !== FALSE){
            while (($data = fgetcsv($handle,0, ";")) !== FALSE) {
                $num = count($data);

                if ($num >= 5){
                    $insert_query = $pdo->prepare("INSERT INTO tbl_users (vorname, nachname, benutzername, geburtsdatum, passwort, ist_aktiv, jahrgang, klasse, datum_erstellt, datum_letzte_anderung, kann_reset_anfordern, ist_admin) 
VALUES (:vorname, :nachname, :username, :birth, :pw, 1, :jahr, :klasse, NOW(), NOW(), 1, 0);");

                    $insert_query->bindParam(":vorname", $data[0]);
                    $insert_query->bindParam(":nachname", $data[1]);
                    $username = generateUsername($data[0], $data[1]);
                    $insert_query->bindParam(":username", $username);
                    try {
                        $date = new DateTime($data[2]);
                    } catch (Exception $e) {
                        session_destroy();
                        header("Location: ../index.php?errorCode=1 ");
                        exit();
                    }
                    $insert_query->bindParam(":birth", $date->format('Y-m-d'));
                    $pw = generatePassword();
                    $insert_query->bindParam(":pw", $pw);
                    $insert_query->bindParam(":jahr", $data[3]);
                    $insert_query->bindParam(":klasse", $data[4]);

                    $insert_query->execute();

                }
            }
            fclose($handle);
            unlink($file);
            header("Location: ../manage_students.php ");
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

function generateUsername($vorname, $nachname){
    $username = $vorname.".".$nachname;
    $username = str_replace(" ", ".", $username);
    $username = $username.rand(100, 999);
    return $username;
}

function generatePassword(){
    $length = 8;
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789+-#@%$/()?.=*!';
    return substr( str_shuffle( $chars ), 0, $length );
}

