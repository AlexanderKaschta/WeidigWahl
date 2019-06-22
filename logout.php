<?php
/**
 * Project: WeidigWahl
 * Author: AlexanderKaschta
 * License: MIT License
 */

session_start();

session_destroy();

header("Location: index.php");
exit();