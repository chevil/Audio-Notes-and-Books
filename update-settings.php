<?php
include("config.php");
include("functions.php");

session_start();

if ( !isset($_SESSION['schtroumpf']) || !isset($_SESSION['papa']) )
{
    echo "ERR: Unauthorized access.";
    exit();
}

if ( !isset($_POST['wc']) )
{
    echo "ERR: The wave color must be set.";
    exit();
}
else
{
    $wc=$_POST['wc'];
}

if ( !isset($_POST['pc']) )
{
    echo "ERR: The progress color must be set.";
    exit();
}
else
{
    $pc=$_POST['pc'];
}

if ( !isset($_POST['mwc']) )
{
    echo "ERR: The map wave color must be set.";
    exit();
}
else
{
    $mwc=$_POST['mwc'];
}

if ( !isset($_POST['mpc']) )
{
    echo "ERR: The map progress color must be set.";
    exit();
}
else
{
    $mpc=$_POST['mpc'];
}

$updres=db_query( "UPDATE settings SET value='".addslashes($wc)."' WHERE name='waveColor'" );
if ( $updres != '1')
{
   echo "ERR: Could not update settings";
   exit(-1);
}

$updres=db_query( "UPDATE settings SET value='".addslashes($pc)."' WHERE name='progressColor'" );
if ( $updres != '1')
{
   echo "ERR: Could not update settings";
   exit(-1);
}

$updres=db_query( "UPDATE settings SET value='".addslashes($mwc)."' WHERE name='mapWaveColor'" );
if ( $updres != '1')
{
   echo "ERR: Could not update settings";
   exit(-1);
}

$updres=db_query( "UPDATE settings SET value='".addslashes($mpc)."' WHERE name='mapProgressColor'" );
if ( $updres != '1')
{
   echo "ERR: Could not update settings";
   exit(-1);
}

echo "OK";

?>
