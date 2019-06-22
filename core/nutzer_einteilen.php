<?php
/**
 * Project: WeidigWahl
 * Author: AlexanderKaschta
 * License: MIT License
 */

require_once "Database.php";
include_once "config.php";

session_start();

if (isset($_SESSION['loggedin']) && isset($_SESSION['admin']) && isset($_GET['id'])) {
    if ($_SESSION['loggedin'] == 1 && isset($_SESSION['benutzer']) && $_SESSION['admin'] == 1) {

        $db = new Database();
        $pdo = $db->connect();

        if (is_null($pdo)) {
            session_destroy();
            header("Location: ../index.php?errorCode=1 ");
            exit();
        }

        //Check if there exists a wahl
        //$checkQuery = $pdo->prepare("SELECT * FROM tbl_sportwahl WHERE id = :id AND datum_ende < NOW();");
        $checkQuery = $pdo->prepare("SELECT * FROM tbl_sportwahl WHERE id = :id;");
        $checkQuery->bindParam(":id", $_GET['id']);
        $checkQuery->execute();

        $checkQuery_rowCount = $checkQuery->rowCount();

        if ($checkQuery_rowCount != 1) {
            header("Location: ../config_wahl.php?errorCode=7 ");
            exit();
        }

        $checkQueryRow = $checkQuery->fetch();

        $anzahl_Benutzereingaben = $checkQueryRow["anzahl_wahl"];
        $anzahl_Auswertung = $checkQueryRow["anzahl_auswertung"];

        $teilnehmerQuery = $pdo->prepare("SELECT * FROM tbl_teilnehmer WHERE wahl_id= :id;");
        $teilnehmerQuery->bindParam(":id", $_GET['id']);
        $teilnehmerQuery->execute();

        $anzahl_teilnehmer = $teilnehmerQuery->rowCount();

        $kursCheckQuery = $pdo->prepare("SELECT SUM(tbl_kurse.max) AS summe FROM tbl_kurse WHERE sportwahl= :id;");
        $kursCheckQuery->bindParam(":id", $_GET['id']);
        $kursCheckQuery->execute();

        $kursCheckRow = $kursCheckQuery->fetch();

        $verfugbarePlatze = $kursCheckRow['summe'];
        $benotigtePlatze = $anzahl_teilnehmer * $anzahl_Auswertung;

        if ($benotigtePlatze > $verfugbarePlatze) {
            //Gib einen Fehler aus
            header("Location: ../config_wahl.php?errorCode=4&id=" . $_GET['id']);
            exit();
        }

        //Gruppiere die Kurse in Zeitleisten

        $kontrolliere_die_Zeitgruppen = $pdo->prepare("SELECT von, bis FROM tbl_kurse WHERE sportwahl = :id GROUP BY von, bis;");
        $kontrolliere_die_Zeitgruppen->bindParam(":id", $_GET['id']);
        $kontrolliere_die_Zeitgruppen->execute();

        $zeitgruppen = $kontrolliere_die_Zeitgruppen->rowCount();

        if ($zeitgruppen != $anzahl_Auswertung) {
            //Es liegt ein Fehler vor
            header("Location: ../config_wahl.php?errorCode=5&id=" . $_GET['id']);
            exit();
        }
        //echo $zeitgruppen." Zeitgruppen";
        $zeitleistenIndex = 0;

        //Handhabe jede Zeitleiste
        while ($zeitleiste = $kontrolliere_die_Zeitgruppen->fetch(PDO::FETCH_ASSOC)) {
            //Gehe durch jeden Teilnehmer und stelle sicher, dass er einem Kurs zugeteilt werden kann
            //echo "Bearbeite Zeitleiste";
            $zeitleistenIndex += 1;

            //Erstelle einen Kurs für die überflüssigen Schüler
            $createKursQuery = $pdo->prepare("INSERT INTO tbl_kurse (name, beschreibung, lehrer, min, max, von, bis, sportwahl, alias) VALUES (:name, :beschreibung, 'Unbekannt', 0, :max, :von, :bis, :id, :alias);");
            $createKurs_name = "Übrige Schüler #" . $zeitleistenIndex;
            $createKurs_beschreibung = "Hier sind alle Schüler, die nicht eingeteilt werden konnten.";
            //max entspricht der Teilnehmerzahl
            //von aus der zeitleiste auslesen
            //bis aus der zeitleiste auslesen
            //id wie immer
            $createKurs_alias = "-" . $zeitleistenIndex;
            $createKursQuery->bindParam(":name", $createKurs_name);
            $createKursQuery->bindParam(":beschreibung", $createKurs_beschreibung);
            $createKursQuery->bindParam(":max", $anzahl_teilnehmer);
            $createKursQuery->bindParam(":von", $zeitleiste['von']);
            $createKursQuery->bindParam(":bis", $zeitleiste['bis']);
            $createKursQuery->bindParam(":id", $_GET['id']);
            $createKursQuery->bindParam(":alias", $createKurs_alias);
            $createKursQuery->execute();

            $resteKurs_Index = $pdo->lastInsertId();

            $teilnehmerQuery1 = $pdo->prepare("SELECT * FROM tbl_teilnehmer WHERE wahl_id= :id;");
            $teilnehmerQuery1->bindParam(":id", $_GET['id']);
            $teilnehmerQuery1->execute();


            while ($teilnehmer = $teilnehmerQuery1->fetch(PDO::FETCH_ASSOC)) {
                //echo "Analysiere den Nutzer " . $teilnehmer['benutzer'] . "\n";
                $kurs_gefunden = false;

                //Gehe durch alle abgegebene Stimmen
                for ($i = 1; $i <= $anzahl_Benutzereingaben; $i++) {

                    $checkKursQuery = $pdo->prepare("SELECT * FROM tbl_ergebnisse, tbl_kurse WHERE tbl_ergebnisse.kurs = tbl_kurse.id AND tbl_ergebnisse.stimmnummer = :nr AND
                                              tbl_kurse.sportwahl = :wahl AND tbl_kurse.von = :von AND tbl_kurse.bis = :bis AND tbl_ergebnisse.benutzer = :benutzer;");
                    $checkKursQuery->bindParam(":nr", $i);
                    $checkKursQuery->bindParam(":wahl", $_GET['id']);
                    $checkKursQuery->bindParam(":von", $zeitleiste['von']);
                    $checkKursQuery->bindParam(":bis", $zeitleiste['bis']);
                    $checkKursQuery->bindParam(":benutzer", $teilnehmer['benutzer']);
                    $checkKursQuery->execute();

                    $checkKursRows = $checkKursQuery->rowCount();

                    if ($checkKursRows == 1) {
                        //Akzeptiere die Wahl
                        $updateKursQuery = $pdo->prepare("UPDATE tbl_ergebnisse SET akzeptiert = 1 WHERE benutzer= :benutzer AND sportwahl=:wahl AND stimmnummer = :nr;");
                        $updateKursQuery->bindParam(":wahl", $_GET['id']);
                        $updateKursQuery->bindParam(":benutzer", $teilnehmer['benutzer']);
                        $updateKursQuery->bindParam(":nr", $i);
                        $updateKursQuery->execute();

                        //echo "\nKurs gefunden";

                        $kurs_gefunden = true;
                        break;
                    }
                }

                //Wenn kein Kurs gefunden wurde, dann füge ihn dem Reste-Kurs hinzu
                if ($kurs_gefunden == false) {

                    $insertIntoReste = $pdo->prepare("INSERT INTO tbl_ergebnisse (sportwahl, stimmnummer, kurs, benutzer, akzeptiert) VALUES (:wahl, :nr, :kurs, :benutzer, 1);");
                    $insertIntoReste->bindParam(":wahl", $_GET['id']);
                    $wert = $anzahl_Benutzereingaben + 1;
                    $insertIntoReste->bindParam(":nr", $wert);
                    $insertIntoReste->bindParam(":kurs", $resteKurs_Index);
                    $insertIntoReste->bindParam(":benutzer", $teilnehmer['benutzer']);
                    $insertIntoReste->execute();
                }
            }

            //Kontrolliere, ob man innerhalb der Zeitleiste noch etwas optimieren kann

            //Kontrolliere, ob es überfüllte Kurse gibt

            $kursControllQuery = $pdo->prepare("SELECT * FROM tbl_kurse WHERE sportwahl= :wahl AND von = :von AND bis = :bis;");
            $kursControllQuery->bindParam(":wahl", $_GET['id']);
            $kursControllQuery->bindParam(":von", $zeitleiste['von']);
            $kursControllQuery->bindParam(":bis", $zeitleiste['bis']);
            $kursControllQuery->execute();

            //echo "\n Probiere die Ergebnisse zu optimieren";

            //Durchgehe alle Kurse aus der Abfrage
            while ($kurs = $kursControllQuery->fetch(PDO::FETCH_ASSOC)) {
                //echo "Bearbeite Kurs";

                $kursTeilnehmerAnzahlQuery = $pdo->prepare("SELECT COUNT(tbl_ergebnisse.id) As anzahl FROM tbl_ergebnisse WHERE sportwahl=:wahl AND kurs= :kurs AND akzeptiert = 1;");
                $kursTeilnehmerAnzahlQuery->bindParam(":wahl", $_GET['id']);
                $kursTeilnehmerAnzahlQuery->bindParam(":kurs", $kurs['id']);
                $kursTeilnehmerAnzahlQuery->execute();

                $kursTeilnehmerRow = $kursTeilnehmerAnzahlQuery->fetch();
                $teilnehmerAnzahl = $kursTeilnehmerRow['anzahl'];

                //Wenn es mehr Teilnehmer im Kurs gibt als er maximale Plätze hat, dann ist er überfüllt
                $max_platz = (int)$kurs['max'];
                if ($max_platz < $teilnehmerAnzahl) {
                    //echo "Der Kurs" . $kurs['name'] . " ist überfüllt";
                    //Wenn er überfüllt ist, dann suche nach Teilnehmer, die noch andere Kurse besuchen möchten
                    $ist_uberfullt = true;

                    for ($i = 1; $i < $anzahl_Benutzereingaben; $i++) {

                        $nutzerAbfrage = $pdo->prepare("SELECT * FROM tbl_ergebnisse WHERE sportwahl=:id AND stimmnummer <=:nr AND kurs = :kurs AND akzeptiert = 1;");
                        $nutzerAbfrage->bindParam(":id", $_GET['id']);
                        $nutzerAbfrage->bindParam(":nr", $i);
                        $nutzerAbfrage->bindParam(":kurs", $kurs['id']);
                        $nutzerAbfrage->execute();

                        while ($nutzer = $nutzerAbfrage->fetch(PDO::FETCH_ASSOC) && $ist_uberfullt == true) {
                            //Kontrolliere, ob der nächste Kurs ein Kurs ist, der im aktuellen Zeitfenster liegt und
                            $passenderKursQuery = $pdo->prepare("SELECT * FROM tbl_ergebnisse, tbl_kurse WHERE tbl_ergebnisse.kurs = tbl_kurse.id AND tbl_ergebnisse.benutzer = :benutzer AND tbl_ergebnisse.stimmnummer = :nr AND tbl_kurse.von = :von AND tbl_kurse.bis = :bis;");
                            $passenderKursQuery->bindParam(":benutzer", $nutzer['benutzer']);
                            $zahl = $i + 1;
                            $passenderKursQuery->bindParam(":von", $zeitleiste['von']);
                            $passenderKursQuery->bindParam(":bis", $zeitleiste['bis']);
                            $passenderKursQuery->execute();

                            $passenderKursZeilen = $passenderKursQuery->rowCount();

                            //Wenn es einen solchen Kurs gibt
                            if ($passenderKursZeilen == 1) {
                                //Aktualisiere den Kurs
                                $update1Query = $pdo->prepare("UPDATE tbl_ergebnisse SET akzeptiert = 0 WHERE sportwahl=:wahl AND benutzer=:benutzer AND akzeptiert = 1;");
                                $update1Query->bindParam(":wahl", $_GET['id']);
                                $update1Query->bindParam(":benutzer", $nutzer['benutzer']);
                                $update1Query->execute();

                                $update2Query = $pdo->prepare("UPDATE tbl_ergebnisse SET akzeptiert = 1 WHERE sportwahl=:wahl AND benutzer=:benutzer AND kurs=:kurs;");
                                $update2Query->bindParam(":wahl", $_GET['id']);
                                $update2Query->bindParam(":benutzer", $nutzer['benutzer']);
                                $update2Query->bindParam(":kurs", $kurs['id']);
                                $update2Query->execute();
                                //und kontrolliere, ob der Kurs immer noch überfüllt ist

                                $kursTeilnehmerAnzahlQuery2 = $pdo->prepare("SELECT COUNT(tbl_ergebnisse.id) As anzahl FROM tbl_ergebnisse WHERE sportwahl=:wahl AND kurs= :kurs AND akzeptiert = 1;");
                                $kursTeilnehmerAnzahlQuery2->bindParam(":wahl", $_GET['id']);
                                $kursTeilnehmerAnzahlQuery2->bindParam(":kurs", $kurs['id']);
                                $kursTeilnehmerAnzahlQuery2->execute();

                                $kursTeilnehmerRow2 = $kursTeilnehmerAnzahlQuery2->fetch();
                                $teilnehmerAnzahl2 = $kursTeilnehmerRow2['anzahl'];

                                if ($kurs['max'] < $teilnehmerAnzahl2) {

                                    $ist_uberfullt = true;
                                } else {
                                    $ist_uberfullt = false;
                                    break;
                                }

                            }


                        }


                    }

                }

            }
            if ($zeitleistenIndex == $zeitgruppen){
                //Fertig mit der Einteilung
                header("Location: ../viewEinteilung.php?id=" . $_GET['id']);
                exit();
            }
        }

        //Fertig mit der Einteilung
        header("Location: ../viewEinteilung.php?id=" . $_GET['id']);
        exit();

    } else {
        session_destroy();
        header("Location: ../index.php?errorCode=1 ");
        exit();
    }

} else {
    session_destroy();
    header("Location: ../index.php?errorCode=1 ");
    exit();
}

