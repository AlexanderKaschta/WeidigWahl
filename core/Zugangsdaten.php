<?php
/**
 * Project: WeidigWahl
 * Author: AlexanderKaschta
 * License: MIT License
 */

require('tfpdf.php');
require "Database.php";
include_once "config.php";

session_start();

class zugangsdaten extends tFPDF{

    function LoadData($id){
        $db = new Database();
        $pdo = $db->connect();

            $query = $pdo->prepare("SELECT tbl_users.vorname, tbl_users.nachname, tbl_users.klasse, tbl_users.benutzername, tbl_users.passwort FROM tbl_users, tbl_teilnehmer WHERE tbl_users.id = tbl_teilnehmer.benutzer AND tbl_teilnehmer.wahl_id = :wahl ORDER BY klasse, nachname, vorname;");
        $query->bindParam(":wahl", $id);
        $query->execute();

        $data = array();
        while ($row = $query->fetch(PDO::FETCH_ASSOC)){
            $data[] = $row;
        }
        return $data;
    }

    function BasicTable($header,$data){
        // Header
        $this->AddFont('DejaVuB','','DejaVuSans-Bold.ttf',true);
        $this->SetFont('DejaVuB','',20);
        $this->Cell(100, 12, "Zugangsdaten", 0, 1);

        $this->SetFont('DejaVuB','',7);
        $index = 1;
        foreach($header as $col){
            if($index == 3){
                $this->Cell(26,7,$col,1);
            } else if ($index == 4){
                $this->Cell(58,7,$col,1);
            } else{
                $this->Cell(28,7,$col,1);
            }
            $index = $index + 1;
        }
        $this->SetFont('DejaVu','',7);
        $this->Ln();
        // Data

        foreach($data as $row)
        {
            $index = 1;
            foreach($row as $col){
                if($index == 3){
                    $this->Cell(26,7,$col,1);
                } else if ($index == 4){
                    $this->Cell(58,7,$col,1);
                } else{
                    $this->Cell(28,7,$col,1);
                }
                $index = $index + 1;
            }
            $this->Ln();
        }
    }

}

if (isset($_SESSION['loggedin']) && isset($_SESSION['admin']) && isset($_GET['id'])) {
    if ($_SESSION['loggedin'] == 1 && isset($_SESSION['benutzer']) && $_SESSION['admin'] == 1) {

        $pdf = new zugangsdaten();
        $header = array('Vorname', 'Nachname', 'Klasse', 'Benutzername' , 'Passwort');
        $data = $pdf->LoadData($_GET['id']);
        $pdf->SetTitle("Zugangsdaten");
        $pdf->SetAuthor("WeidigWahl");
        $pdf->AddFont('DejaVu','','DejaVuSans.ttf',true);
        $pdf->SetFont('DejaVu','',7);
        $pdf->SetMargins(20.0, 25.0, 20.0);
        $pdf->AddPage();
        $pdf->BasicTable($header,$data);
        $pdf->Output();

    } else {
        session_destroy();
        header("Location: index.php?errorCode=1 ");
        exit();
    }

} else {
    session_destroy();
    header("Location: index.php?errorCode=1 ");
    exit();
}