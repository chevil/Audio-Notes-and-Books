<?php

include("config.php");

if ( empty($_POST['title']) )
{
   header('HTTP/1.1 406 Title is Mandatory');	  
   exit(-1);
}
$title = $_POST['title'];

$link = mysqli_connect($config['dbhost'], $config['dbuser'], $config['dbpass'], $config['dbname']);
if (!$link) {
   error_log( "Couldn't connect to the database : ".$config['dbname']);
   header('HTTP/1.1 500 Could not connect to the database');
   exit(-1);
} else {
   $link->query('SET NAMES utf8');
   // error_log( 'Updating biography : '.$title );
   $sqls = "SELECT biography FROM archive WHERE title LIKE '%".addslashes($title)."';";
   $results = $link->query($sqls);
   if ( mysqli_num_rows($results) != 1 ) {
      header('HTTP/1.1 500 Error getting biography : '.$resultu);	  
      mysqli_close($link);
      exit(-1);
   } else {
      $row = mysqli_fetch_array($results);
      echo $row['biography'];
      mysqli_close($link);
   }
}
exit(0);

?>
