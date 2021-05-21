<?php

include("config.php");

if ( empty($_POST['uri']) )
{
   header('HTTP/1.1 406 Uri is Mandatory');	  
   exit(-1);
}
$uri = $_POST['uri'];

$link = mysqli_connect($config['dbhost'], $config['dbuser'], $config['dbpass'], $config['dbname']);
if (!$link) {
   error_log( "Couldn't connect to the database : ".$config['dbname']);
   header('HTTP/1.1 500 Could not connect to the database');
   exit(-1);
} else {
   $link->query('SET NAMES utf8');
   // error_log( 'Deleting upload : '.$uri );
   $sqld = "DELETE FROM upload WHERE uri LIKE '%".addslashes($uri)."';";
   $resdel = $link->query($sqld);
   if ( $resdel != 1 ) {
      header('HTTP/1.1 500 Unknown document : '.$resdel);	  
      echo ("Unknown document."); 
      mysqli_close($link);
      exit(-1);
   } else {
      if (!unlink($uri)) { 
         $phperror = error_get_last();
         header('HTTP/1.1 500 Could not delete document : '.$phperror['message']);	  
         echo ("Could not delete document : ".$phperror['message']); 
      } 
      else 
      { 
         header('HTTP/1.1 200 OK');	  
         echo "OK";
      }
   }
}
mysqli_close($link);
exit(0);

?>
