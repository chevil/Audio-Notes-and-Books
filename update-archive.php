<?php
include("config.php");
include("functions.php");

session_start();

if ( !isset($_SESSION['schtroumpf']) || !isset($_SESSION['papa']) )
{
    echo "ERR: Unauthorized access.";
    exit();
}

if ( !isset($_POST['aid']) )
{
    echo "ERR: The id must be set.";
    exit();
}
else
{
    $id=$_POST['aid'];
}

if ( !isset($_POST['author']) )
{
    echo "ERR: The author must be set.";
    exit();
}
else
{
    $author=$_POST['author'];
    if ( $author == '' ) $author='Unknown';
}

if ( !isset($_POST['title']) )
{
    echo "ERR: The title must be set.";
    exit();
}
else
{
    $title=$_POST['title'];
    if ( $title == '' ) $title='Unknown';
}

if ( !isset($_POST['collection']) )
{
    echo "ERR: The collection must be set.";
    exit();
}
else
{
    $collection=$_POST['collection'];
    if ( $collection == '' ) $collection='Unknown';
}

if ( !isset($_POST['date']) )
{
    echo "ERR: The date must be set.";
    exit();
}
else
{
    $date=$_POST['date'];
    if ( $date == '' ) $date='Unknown';
}

$updres=db_query( "UPDATE archive SET author='".addslashes($author)."', title='".addslashes($title)."', collection='".addslashes($collection)."', date='".addslashes($date)."' WHERE id=".$id );
if ( $updres == '1')
{
   echo "OK";
}
else
{
   echo "ERR: Could not update archive";
}

?>
