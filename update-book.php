<?php
include("config.php");
include("functions.php");

session_start();

if ( !isset($_SESSION['schtroumpf']) || !isset($_SESSION['papa']) )
{
    die("ERR: Unauthorized access.");
}

if ( !isset($_POST['title']) )
{
    die("ERR: The title must be set.");
}
else
{
    $title=$_POST['title'];
    if ( $title == '' ) $title='Unknown';
}

if ( !isset($_POST['otitle']) )
{
    die("ERR: The old title must be set.");
}
else
{
    $otitle=$_POST['otitle'];
}

if ( !isset($_POST['order']) )
{
    die("ERR: The order must be set.");
}
else
{
    $order=$_POST['order'];
}

$updres=db_query( "UPDATE audiobook SET title='".addslashes($title)."' WHERE title='".addslashes($otitle)."'" );
if ( $updres != true )
{
   die("ERR: Could not update book.");
}

error_log( "DELETE FROM audiobook WHERE norder NOT IN (".$order.")" );
$delres=db_query( "DELETE FROM audiobook WHERE norder NOT IN (".$order.") AND title='".addslashes($otitle)."'" );
if ( $delres != true )
{
   die("ERR: Could not update book.");
}

$neworder = explode( ",", $order );
$selres=db_query( "SELECT id, norder FROM audiobook WHERE title='".addslashes($otitle)."' ORDER BY norder" );
while ( $rowres = mysqli_fetch_row($selres) ) 
{
   $counter=0;
   foreach ( $neworder as $rank )
   {
     ++$counter;
     if ( $rank == $rowres[1] )
     {
        $newrank = $counter;
        break;
     }
   }
   $updres=db_query( "UPDATE audiobook SET norder=".$newrank." WHERE id=".$rowres[0] );
   if ( $updres != true )
   {
      die("ERR: Could not update book.");
   }
}

print "OK";

?>
