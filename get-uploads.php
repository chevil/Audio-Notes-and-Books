<?php

include("config.php");

if ( empty($_POST['url']) )
{
   header('HTTP/1.1 406 Title is Mandatory');	  
   exit(-1);
}
$url = $_POST['url'];

$link = mysqli_connect($config['dbhost'], $config['dbuser'], $config['dbpass'], $config['dbname']);
if (!$link) {
   error_log( "Couldn't connect to the database : ".$config['dbname']);
   header('HTTP/1.1 500 Could not connect to the database');
   exit(-1);
} else {
   $link->query('SET NAMES utf8');
   $sqls = "SELECT id FROM archive WHERE url LIKE '%".addslashes($url)."';";
   // error_log( 'Getting uploads : '.$sqls );
   $results = $link->query($sqls);
   if ( mysqli_num_rows($results) != 1 ) {
      header('HTTP/1.1 500 Unknown archive : '.mysqli_num_rows($results));	  
      mysqli_close($link);
      exit(-1);
   } else {
      $row = mysqli_fetch_array($results);
      $counter=0;
      $sqlu = "SELECT uri, type, size FROM upload WHERE aid = ".$row['id'].";";
      $resultsu = $link->query($sqlu);
      while ($rowu = mysqli_fetch_array($resultsu))
      {
         $uploads[$counter]['uri'] = $rowu['uri'];
         $uploads[$counter]['type'] = $rowu['type'];
         $uploads[$counter]['size'] = $rowu['size'];
         $counter++;
      }
      header('HTTP/1.1 200 OK');	  
      echo json_encode($uploads);
   }
}
mysqli_close($link);
exit(0);

?>
