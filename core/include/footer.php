<?php
require_once("core/config.php");
$creation_year = RELEASE_YEAR;
$current_year = date("Y");
?>

<footer class="text-muted">
    <div class="container">

        <p><b>WeidigWahl | Ein Wahlsystem für die Weidigschule</b></p>
        <p>&copy; <?php echo $creation_year . (($creation_year != $current_year) ? '-' . $current_year : '') . " " . HOSTER; ?></p>
        <hr class="col-xs-12">
        <div class="row">
            <div class="col-md-4 ">
                <a href="#">GitHub</a>
            </div>
            <div class="col-md-4">
                <a href="#" class="text-muted">Impressum & Datenschutz</a>
            </div>
            <div class="col-md-4">
                <a href="#">Schulwebseite</a>
            </div>
        </div>
    </div>
</footer>