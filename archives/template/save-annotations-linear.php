<?php

include("../../config.php");

  if ( empty($_POST['json']) )
  {
     header('HTTP/1.1 406 Data is Mandatory');	  
     exit(-1);
  }
  $annotations = $_POST['json'];
  if ( !file_put_contents( "./annotations-linear.json", $annotations ) )
  {
     $error = error_get_last();
     header('HTTP/1.1 500 Could not store annotations : '.$error['message']);	  
     exit(-1);
  }

  // saving in the database also for setting bookmarks over the collection
  $link = mysqli_connect($config['dbhost'], $config['dbuser'], $config['dbpass'], $config['dbname']);
  if (!$link) {
     error_log( "Couldn't connect to the database : ".$config['dbname']);
     header('HTTP/1.1 500 Could not connect to the database');
     exit(-1);
  } else {
     $link->query("SET NAMES utf8");
     $link->query("LOCK TABLES `annotation`");
     $annotes = json_decode( $annotations, true );
     // error_log( __FILE__." got : ".count($annotes)." notes" );
     foreach( $annotes as $note )
     {
         if ( count($note["data"]) > 0 ) {
            $ndata = $note["data"]["note"];
            $nuser = $note["data"]["user"];
            $ncolor = $note["data"]["color"];
         } else
            $ndata = '';
         $data = json_decode( $annotations, true );
         $sql = "SELECT url FROM annotation WHERE source='".$note["source"]."' AND norder=".$note["order"].";"; 
         // error_log($sql);
         $result = $link->query($sql);
         if ( $result && mysqli_num_rows($result) === 0) {
           $isql = "INSERT INTO annotation ( norder, start, end, url, source, title, attributes, data, user, color ) VALUES ( ".$note["order"].",".$note["start"].",".$note["end"].",'".addslashes($note["url"])."','".addslashes($note["source"])."','".addslashes($note["title"])."','".json_encode($note["attributes"])."','".addslashes($ndata)."','".addslashes($nuser)."','".addslashes($ncolor)."' )";
           $insert = $link->query($isql);
           if ( $insert !== true ) {
              error_log( "ERROR : ".__FILE__." : could not create annotation : ".$note["order"]." : ".mysqli_error($link) );
              header('HTTP/1.1 500 Error creating annotation');	  
              $link->query("UNLOCK TABLES `annotation`");
              mysqli_close($link);
              exit(-1);
           }
         } else if ( $result && mysqli_num_rows($result) === 1)  {
           $usql = "UPDATE annotation SET norder=".$note["order"].", start=".$note["start"].", end=".$note["end"].", url='".addslashes($note["url"])."', source='".addslashes($note["source"])."', title='".addslashes($note["title"])."', attributes='".json_encode($note["attributes"])."', data='".addslashes($ndata)."' , user='".addslashes($nuser)."' , color='".addslashes($ncolor)."' WHERE source='".$note["source"]."' AND norder=".$note["order"];
           $update = $link->query($usql);
           if ( $update !== true ) {
              error_log( "ERROR : ".__FILE__." : could not update annotation : ".$note["order"]." : ".mysqli_error($link) );
              header('HTTP/1.1 500 Error updating annotation');	  
              $link->query("UNLOCK TABLES `annotation`");
              mysqli_close($link);
              exit(-1);
           }
         } else {
           $dsql = "DELETE FROM annotation WHERE source='".$note["source"]."' AND norder=".$note["order"].";";
           $delete = $link->query($dsql);
           $isql = "INSERT INTO annotation ( norder, start, end, url, source, title, attributes, data, user, color ) VALUES ( ".$note["order"].",".$note["start"].",".$note["end"].",'".addslashes($note["url"])."','".addslashes($note["source"])."','".addslashes($note["title"])."','".json_encode($note["attributes"])."','".addslashes($ndata)."','".addslashes($nuser)."','".addslashes($ncolor)."' )";
           $insert = $link->query($isql);
           if ( $insert !== true ) {
              error_log( "ERROR : ".__FILE__." : could not create annotation : ".$note["order"]." : ".mysqli_error($link) );
              header('HTTP/1.1 500 Error creating annotation');
              $link->query("UNLOCK TABLES `annotation`");
              mysqli_close($link);
              exit(-1);
           }
         }
     }
  }

  header('HTTP/1.1 200 OK');
  $link->query("UNLOCK TABLES `annotations`");
  mysqli_close($link);
  exit(0);

?>
