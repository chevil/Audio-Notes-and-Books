<?php

include("config.php");

  $link = mysqli_connect($config['dbhost'], $config['dbuser'], $config['dbpass'], $config['dbname']);
  if (!$link) {
     error_log( "Couldn't connect to the database : ".$config['dbname']);
     header('HTTP/1.1 500 Could not connect to the database');	  
     exit(-1);
  } else {
     $link->query('SET NAMES utf8');
     $sql = "SELECT DISTINCT(title) FROM audiobook;";
     $result = $link->query($sql);
     $titles = [];
     $counter = 0;
     while( $brow = mysqli_fetch_row($result) ) {
        $titles[$counter++] = $brow[0];
     }
     print json_encode($titles);
  }

  mysqli_close($link);
  exit(0);
?>
