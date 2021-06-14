<?php
  
include("config.php");
include("functions.php");
date_default_timezone_set('UTC');

error_log( "Uploading : ".$_FILES['file']['name'] );

session_start();

if (!isset($_SESSION['schtroumpf']) || !isset($_SESSION['papa']) )
{
   header("HTTP/1.1 500 Internal Server Error");
   print("You are not connected.");
   exit(-1);
}

if ( $_FILES['file']['error'] != UPLOAD_ERR_OK )
{
    error_log( "Error onfile upload : ".$_FILES['file']['error'] );
    header("HTTP/1.1 500 Internal Server Error");
    print("Error onfile upload : ".$_FILES['file']['error']);
    exit(-1);
}

if ( !isset($_POST['url']) || $_POST['url'] == "" )
{
   header("HTTP/1.1 500 Internal Server Error");
   print("Unknown archive.");
   exit(-1);
}
$url = $_POST['url'];

$query="SELECT id FROM archive WHERE url='".htmlentities($url)."'";
error_log("Uploading document : ".$query );
$resan = db_query($query);
if ( mysqli_num_rows($resan) != 1 )
{
   header("HTTP/1.1 500 Internal Server Error");
   print("Unknown archive.");
   exit(-1);
}
else
{
   $rowan = mysqli_fetch_array( $resan ); 
   $aid = $rowan['id'];
}

$tmpname = $_FILES['file']['tmp_name'];
$name = $_FILES['file']['name'];
$type = $_FILES['file']['type'];
$size = $_FILES['file']['size'];

if (move_uploaded_file($tmpname, "uploads/".$name)) {
    error_log( "File was successfully uploaded : ".$name );
} else {
    error_log("Possible file upload attack!" );
    header("HTTP/1.1 500 Internal Server Error");
    print("Possible file upload attack!" );
}

$query="INSERT INTO upload ( aid, uri, type, size ) VALUES (".$aid.",'uploads/".addslashes($name)."','".$type."',".$size.");";
error_log($query);
$resins = db_query($query);
if ( $resins == 1 )
{
   header("HTTP/1.1 200 OK");
   print("uploads/".$_FILES['file']['name']);
}
else
{
   header("HTTP/1.1 500 Internal Server Error");
   print("Error inserting into the database.");
}

?>

