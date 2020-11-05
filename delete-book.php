<?php
include("config.php");
include("functions.php");

session_start();

if ( !isset($_SESSION['schtroumpf']) || !isset($_SESSION['papa']) )
{
    print( "ERR: Unauthorized access." );
    exit();
}

if ( !isset( $_POST['title'] ) || $_POST['title'] == "" )
{
    print "ERR: The title of the book is unknown.";
    exit();
}
$title=$_POST['title'];

if ( !isset( $_POST['user'] ) || $_POST['user'] == "" )
{
    print "ERR: The user is not set.";
    exit();
}
$user=$_POST['user'];

$query = "DELETE FROM audiobook WHERE title='".addslashes($title)."' AND user='".addslashes($user)."';";
error_log($query);
$delres=db_query($query);
if ( $delres == true)
{
   print "OK";
}
else
{
   print "ERR: Delete Error : You must own some contents in it.";
}

?>
