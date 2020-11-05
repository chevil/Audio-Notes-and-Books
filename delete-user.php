<?php
include("config.php");
include("functions.php");

session_start();

if ( !isset($_SESSION['schtroumpf']) || !isset($_SESSION['papa']) || ($_SESSION['schtroumpf'] != $config['owner']) )
{
    echo "ERR: Unauthorized access.";
    exit();
}

$id=$_POST['_id'];

if ( !isset( $_POST['_id'] ) || $_POST['_id'] == "" )
{
    echo "ERR: The id of the user is unknown.";
    exit();
}

$query = "DELETE FROM user WHERE _id=".$id.";";
$delres=db_query($query);
if ( $delres == '1')
{
   echo "OK";
}
else
{
   echo "ERR: Delete Error";
}
?>
