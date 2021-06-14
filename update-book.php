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

if ( $title != $otitle )
{
    $updres=db_query( "UPDATE audiobook SET title='".addslashes($title)."' WHERE title='".addslashes($otitle)."'" );
    if ( $updres != true )
    {
       die("ERR: Could not update book.");
    }
    error_log("mv \"audiobooks/".urldecode($otitle)."\" \"audiobooks/".urldecode($title))."\"";
    if ( $result=exec("mv \"audiobooks/".urldecode($otitle)."\" \"audiobooks/".urldecode($title)."\"; echo $?") != 0 )
    {
       die("ERR: Cannot move audio book ( too heavy? ".$result.")" );
    }
    $cmd = "sed -i \"s#".urldecode($otitle)."#".urldecode($title)."#g\" \"audiobooks/".urldecode($title)."/listen.php\"; echo $?";
    error_log($cmd);
    if ( $result=exec($cmd) != 0 )
    {
       die("ERR: Cannot move audio book ( too esoteric? ".$result.")" );
    }
}

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
