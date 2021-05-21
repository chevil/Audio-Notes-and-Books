<?php
include("config.php");
include("functions.php");

session_start();

if ( !isset($_SESSION['schtroumpf']) || !isset($_SESSION['papa']) || ($_SESSION['schtroumpf'] != $config['owner']) )
{
    print( "ERR: Only the admin can delete archives." );
    exit();
}

$id=$_POST['_id'];

if ( !isset( $_POST['_id'] ) || $_POST['_id'] == "" )
{
    print "ERR: The id of archive is unknown.";
    exit();
}

$query = "DELETE FROM archive WHERE id=".$id.";";
$delres=db_query($query);
if ( $delres == true)
{
   // print "OK";
}
else
{
   print "ERR: Delete Error";
}

$query = "DELETE FROM upload WHERE aid=".$id.";";
$delres=db_query($query);
if ( $delres == true)
{
   print "OK";
}
else
{
   print "ERR: Delete Error";
}

?>
