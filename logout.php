<?php
include("config.php");
include("functions.php");

session_start();

if ( !isset($_SESSION['schtroumpf']) )
{
    echo "The user must be set.";
    exit();
}
if ( !isset($_SESSION['papa']) )
{
    echo "The password must be set.";
    exit();
}

session_unset();
print "OK";

?>
