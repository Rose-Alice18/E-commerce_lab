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
    // Changed from "shoppn" to "dbforlab" to match your SQL file
    define("DATABASE", "dbforlab");
}
?>