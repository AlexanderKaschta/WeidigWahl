<?php
require_once("core/config.php");
$creation_year = RELEASE_YEAR;
$current_year = date("Y");

$anzahl = 0;

if (SHOW_GITHUB) {
    $anzahl++;
}

if (SHOW_IMPRINT) {
    $anzahl++;
}

if (SHOW_DSGVO) {
    $anzahl++;
}

if (SHOW_SCHOOL_LINK) {
    $anzahl++;
}

$width = 12 / $anzahl;

?>

<footer class="text-muted">
    <div class="container">
        <p><b>WeidigWahl | Ein Wahlsystem für die <?php echo SCHOOL; ?></b></p>
        <p>&copy; <?php echo $creation_year . (($creation_year != $current_year) ? '-' . $current_year : '') . " " . HOSTER; ?></p>
        <hr class="col-xs-12">
        <div class="row">
            <?php

            if (SHOW_SCHOOL_LINK) {
                echo '<div class="col-md-'.$width.'"><a href="'.SCHOOL_LINK.'" class="text-muted">Schule</a></div>';
            }
            if (SHOW_GITHUB) {
                echo '<div class="col-md-'.$width.'"><a href="'.GITHUB_LINK.'" class="text-muted">GitHub</a></div>';
            }
            if (SHOW_IMPRINT) {
                echo '<div class="col-md-'.$width.'"><a href="'.IMPRINT_LINK.'" class="text-muted">Impressum</a></div>';
            }
            if (SHOW_DSGVO) {
                echo '<div class="col-md-'.$width.'"><a href="'.DSGVO_LINK.'" class="text-muted">Datenschutz</a></div>';
            }

            ?>
        </div>
    </div>
</footer>