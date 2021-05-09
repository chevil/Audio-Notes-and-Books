<?php

  if ( empty($_POST['json']) )
  {
     header('HTTP/1.1 406 Data is Mandatory');	  
     exit(-1);
  }
  $peaks = $_POST['json'];
  if ( !file_put_contents( "./peaks.json", $peaks ) )
  {
     $error = error_get_last();
     header('HTTP/1.1 500 Could not store peaks : '.$error['message']);	  
     exit(-1);
  }

  header('HTTP/1.1 200 OK');	  
  exit(0);
?>
