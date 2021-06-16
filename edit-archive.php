<?php
include("config.php");
include("functions.php");

date_default_timezone_set('UTC');

session_start();

if (!isset($_SESSION['schtroumpf']) || !isset($_SESSION['papa']) )
{
   header("Location: index.php");
   die();
}

if ( !isset( $_GET["_id"] ) || $_GET["_id"] == "" )
{
   echo "ERR: Id of the archive is unknown.";
   exit();
}
else
{
   $id=$_GET["_id"];
   $result = db_query( "SELECT author, title, collection, date, creator FROM archive WHERE id=".$id );
   if ( mysqli_num_rows( $result ) != 1 )
   {
      die("ERR: Archive not found.");
   }
   else
   {
      $row = mysqli_fetch_row( $result );
      $author = htmlentities($row[0],ENT_QUOTES);
      $title = htmlentities($row[1],ENT_QUOTES);
      $collection = htmlentities($row[2],ENT_QUOTES);
      $date = $row[3];
      $creator = htmlentities($row[4],ENT_QUOTES);
   }
}

?>

<html>
<head>
  <meta charset="UTF-8">

  <script src="js/jquery.min.js"></script>
  <script src="js/jquery.datetimepicker.js"></script>
  <script src="js/alertify.min.js"></script>

  <link href="css/alertify.core.css" rel="stylesheet">
  <link href="css/alertify.default.css" rel="stylesheet">
  <link href="css/jquery.datetimepicker.css" rel="stylesheet"/>
  <link href="css/font-awesome.min.css" rel="stylesheet">
  <link href="css/all.css" rel="stylesheet">

  <script type="text/javascript">

    function updateArchive() {
       aid=$("#archive-form :input[name='aid']").val();
       author=$("#archive-form :input[name='author']").val();
       title=$("#archive-form :input[name='title']").val();
       collection=$("#archive-form :input[name='collection']").val();
       sdate=$("#archive-form :input[name='date']").val();
       $.post( "update-archive.php", { aid: aid, author: author, title: title, collection: collection, date: sdate }, function(data) {
        if ( data == "OK" )
        {
          document.location = "manage-archives.php";
        }
        else
        {
          $('#error-zone').css({background:'red'});
          $('#error-zone').html(data.replace("ERR: ",""));
          $('#error-zone').animate({ opacity : 1.0 },{queue:false,duration:1000});
        }
       })
       .fail(function() {
        $('#error-zone').css({background:'red'});
        $('#error-zone').html("Archive update error");
        $('#error-zone').animate({ opacity : 1.0 },{queue:false,duration:1000});
       });
    }

  </script>

</head>

<body background="img/background.png">
<a href="./index.php"><i class="fa fa-chevron-left fa-1x" aria-hidden="true" style="color: #000000; float:left; margin-left:20px;" ></i></a>

<center><table width=40%>
<tr><td align=right>
</td><td valign=center>
<h1><?php echo $config['project-name']; ?></h1>
</td</tr>
</table></center>

<center>
<h1>Edit Archive</h1>
</center>

<form id="archive-form" action="javascript:updateArchive()" method="post" enctype="multipart/form-data">
<table width=100% align=center>

<?php
  print "<tr><td colspan=2><input type='hidden' id='aid' name='aid' value='".$id."' /></td></tr>";
  print "<tr><td width=50% align=right>Author : </td><td><input type='text' id='author' name='author' value='".$author."' /></td></tr>";
  print "<tr><td width=50% align=right>Title : </td><td><input type='text' id='title' name='title' value='".$title."' /></td></tr>";
  print "<tr><td width=50% align=right>Genre / Collection : </td><td><input type='text' id='collection' name='collection' value='".$collection."' /></td></tr>";
  print "<tr><td width=50% align=right>Date : </td><td><input type='text' id='date' name='date' value='".$date."' /></td></tr>";

?>

<tr><td>&nbsp;</td><td>&nbsp;</td></tr>
<tr><td align=center colspan=2><input class="bluebutton" type="submit" value="Update">
</td></tr>
<tr><td align=center colspan=2>
<div id='error-zone'></div>
</td></tr>
</table>
</form> 

<script type="text/javascript">

$(document).ready( function(){

  var width = $(this).outerWidth();

  $('#date').datetimepicker({
      format:'d/m/Y H:i:s'
  });


});

</script>

</body>
</html>
