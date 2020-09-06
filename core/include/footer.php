<?php
    require_once("core/config.php");
    $creation_year = RELEASE_YEAR;
    $current_year = date("Y");
?>

<footer class="text-muted">
    <div class="container">
        <p>WeidigWahl | Ein Wahlsystem für die Weidigschule</p>
        <hr class="col-xs-12">
        &copy; <?php echo $creation_year . (($creation_year != $current_year) ? '-' . $current_year : '')." ".HOSTER; ?>
    </div>
</footer>