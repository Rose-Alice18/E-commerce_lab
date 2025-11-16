<?php
// Database credentials
// Settings/db_cred.php

// Use the database name from your SQL file
if (!defined("SERVER")) {
    define("SERVER", "localhost");
}

if (!defined("USERNAME")) {
    define("USERNAME", "root");
}

if (!defined("PASSWD")) {
    define("PASSWD", "");
}

if (!defined("DATABASE")) {
    // Local: pharmavault_db
    // Production Server: ecommerce_2025A_roseline_tsatsu
    define("DATABASE", "pharmavault_db");
}
?>