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

class Teilnehmer extends tFPDF{

    function LoadData($id){
        $db = new Database();
        $pdo = $db->connect();

        $query = $pdo->prepare("SELECT tbl_users.vorname, tbl_users.nachname, tbl_users.klasse, tbl_users.jahrgang, tbl_kurse.alias FROM tbl_ergebnisse, tbl_kurse, tbl_users WHERE tbl_ergebnisse.kurs = tbl_kurse.id AND tbl_ergebnisse.benutzer = tbl_users.id AND tbl_ergebnisse.akzeptiert = 1 AND tbl_ergebnisse.sportwahl = :wahl ORDER BY tbl_users.id, tbl_ergebnisse.stimmnummer;");
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
        $this->Cell(100, 12, "Wahlergebnisse", 0, 1);

        $this->SetFont('DejaVuB','',10);
        foreach($header as $col)
            $this->Cell(34,7,$col,1);
        $this->SetFont('DejaVu','',10);
        $this->Ln();
        // Data
        foreach($data as $row)
        {
            foreach($row as $col)
                $this->Cell(34,6,$col,1);
            $this->Ln();
        }
    }

}

if (isset($_SESSION['loggedin']) && isset($_SESSION['admin']) && isset($_GET['id'])) {
    if ($_SESSION['loggedin'] == 1 && isset($_SESSION['benutzer']) && $_SESSION['admin'] == 1) {

        $pdf = new Teilnehmer();
        $header = array('Vorname', 'Nachname', 'Klasse', 'Jahrgang', 'Kurs');
        $data = $pdf->LoadData($_GET['id']);
        $pdf->SetTitle("Wahlergebnis");
        $pdf->SetAuthor("WeidigWahl");
        $pdf->AddFont('DejaVu','','DejaVuSans.ttf',true);
        $pdf->SetFont('DejaVu','',10);
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