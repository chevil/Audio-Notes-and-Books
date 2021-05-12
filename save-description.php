<?php

include("config.php");

if ( empty($_POST['title']) )
{
   header('HTTP/1.1 406 Title is Mandatory');	  
   exit(-1);
}
$title = $_POST['title'];

if ( !isset($_POST['description']) )
{
   header('HTTP/1.1 406 Description is Mandatory');	  
   exit(-1);
}
$description = $_POST['description'];

$link = mysqli_connect($config['dbhost'], $config['dbuser'], $config['dbpass'], $config['dbname']);
if (!$link) {
   error_log( "Couldn't connect to the database : ".$config['dbname']);
   header('HTTP/1.1 500 Could not connect to the database');
   exit(-1);
} else {
   $link->query('SET NAMES utf8');
   // error_log( 'Updating description : '.$title );
   $sqlu = "UPDATE archive SET description='".addslashes($description)."' WHERE title LIKE '%".addslashes($title)."';";
   error_log($sqlu);
   $resultu = $link->query($sqlu);
   if ( $resultu != 1 ) {
      header('HTTP/1.1 500 Error updating description : '.$resultu);	  
      mysqli_close($link);
      exit(-1);
   } else {
      header('HTTP/1.1 200 OK');	  
      mysqli_close($link);
   }
}
exit(0);

?>
