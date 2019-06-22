<?php
/**
 * Project: WeidigWahl
 * Author: AlexanderKaschta
 * License: MIT License
 */

require "Database.php";

if (isset($_POST['user']) && isset($_POST['password'])){

    $password = $_POST['password'];
    $user = $_POST['user'];

    $db = new Database();
    $pdo = $db->connect();
    if (is_null($pdo)){
        header("Location: ../index.php?errorCode=1 ");
        exit();
    }

    $query = $pdo->prepare("SELECT * FROM tbl_users WHERE passwort = :pw AND benutzername = :username AND ist_aktiv = 1;");
    $query->bindParam(":pw", $_POST['password']);
    $query->bindParam(":username", $_POST['user']);
    $query->execute();

    $count = $query->rowCount();

    if ($count == 1){
        //Der Login war erfolgreich

        $row = $query->fetch();

        //Beginne mit der Session
        session_start();
        $_SESSION['loggedin'] = 1;
        $_SESSION['benutzer'] = $user;
        $_SESSION['id'] = $row['id'];
        $_SESSION['admin'] = 0;
        if (isset($row['ist_admin']) && $row['ist_admin'] == 1){
            $_SESSION['admin'] = 1;
        }


        header("Location: ../main.php");
        exit();
    } else{
        header("Location: ../index.php?errorCode=2 ");
        exit();
    }

} else{
    header("Location: ../index.php?errorCode=1 ");
    exit();
}
