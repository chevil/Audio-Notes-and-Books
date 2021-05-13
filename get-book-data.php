<?php
include("config.php");
include("functions.php");
require("html2text.php");

session_start();

if (!isset($_SESSION['schtroumpf']) || !isset($_SESSION['papa']) )
{
   die("ERR: Unauthorized access.");
}

if ( !isset( $_POST["title"] ) || $_POST["title"] == "" )
{
   die("ERR: The title of this book is not set.");
}
else
{
  $title = $_POST["title"];
  error_log("SELECT audiobook.title, data, audiobook.norder FROM audiobook, annotation WHERE audiobook.aoid=annotation.id AND audiobook.title='".addslashes($title)."' ORDER BY audiobook.norder" );
  $result=db_query("SELECT audiobook.title, data, audiobook.norder FROM audiobook, annotation WHERE audiobook.aoid=annotation.id AND audiobook.title='".addslashes($title)."' ORDER BY audiobook.norder" );
  $numrows=mysqli_num_rows($result);
  if ( $numrows == 0 )
  {
    die("ERR: This book is empty.");
  }
  else
  {
    $list=[];
    $counter=0;
    while ($row = mysqli_fetch_row($result))
    {
      $list[$counter]['title']= urldecode($row[0]);
      $fnote = convert_html_to_text($row[1]);
      if ( $fnote[2] == ':' )
      {
         $list[$counter]['note']= substr(substr($fnote,3),0,40)."...";
      } 
      else
      {
         $list[$counter]['note']= substr($fnote,0,40)."...";
      }
      $list[$counter++]['order']= $row[2];
    }
    echo json_encode($list);
  }
}

?>
