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

// reading user's colors
$waveColor="#000000";
$progressColor="#000000";
$mapWaveColor="#000000";
$mapProgressColor="#000000";

$ressettings = db_query( "SELECT name, value FROM settings" );
while ( $rowsetting = mysqli_fetch_array( $ressettings) )
{
   if ( $rowsetting['name'] == "waveColor" )
      $waveColor = $rowsetting['value'];
   if ( $rowsetting['name'] == "progressColor" )
      $progressColor = $rowsetting['value'];
   if ( $rowsetting['name'] == "mapWaveColor" )
      $mapWaveColor = $rowsetting['value'];
   if ( $rowsetting['name'] == "mapProgressColor" )
      $mapProgressColor = $rowsetting['value'];
}

?>

<html>
<head>
  <meta charset="UTF-8">
  <script src="js/jquery.min.js"></script>
  <script src="js/alertify.min.js"></script>
  <script src="js/jscolor.min.js"></script>

  <link href="css/bootstrap.min.css" rel="stylesheet">
  <link href="css/app.css" rel="stylesheet">
  <link href="css/alertify.core.css" rel="stylesheet">
  <link href="css/alertify.default.css" rel="stylesheet">
  <link href="css/font-awesome.min.css" rel="stylesheet">

  <script type="text/javascript">

    function updateSettings() {
       wc=$("#archive-form :input[name='wc']").val();
       pc=$("#archive-form :input[name='pc']").val();
       mwc=$("#archive-form :input[name='mwc']").val();
       mpc=$("#archive-form :input[name='mpc']").val();
       $.post( "update-settings.php", { wc: wc, pc: pc, mwc: mwc, mpc: mpc }, function(data) {
        if ( data == "OK" )
        {
          document.location = "index.php";
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
          $('#error-zone').html("Settings update request error");
          $('#error-zone').animate({ opacity : 1.0 },{queue:false,duration:1000});
       });
    }

  </script>

</head>

<body background="img/background.png">
<a href="./index.php"><i class="fa fa-home fa-2x" aria-hidden="true" style="color: #999; float:left; margin-left:20px; margin-top:28px;" ></i></a>

<center><table width=90%>
<tr><td align=right>
</td><td align=center>
<h1><?php echo $config['project-name']; ?></h1>
</td</tr>
</table></center>

<center>
<h1>Edit Settings</h1>
</center>

<form id="archive-form" action="javascript:updateSettings()" method="post" enctype="multipart/form-data">
<table width=100% align=center>

<?php
  print "<tr><td width=50% align=right>Wave Color : </td><td><input type='text' id='wc' name='wc' value='".$waveColor."' data-jscolor='{}' /></td></tr>";
  print "<tr><td width=50% align=right>Progress Color : </td><td><input type='text' id='pc' name='pc' value='".$progressColor."' data-jscolor='{}' /></td></tr>";
  print "<tr><td width=50% align=right>Map Wave Color : </td><td><input type='text' id='mwc' name='mwc' value='".$mapWaveColor."' data-jscolor='{}' /></td></tr>";
  print "<tr><td width=50% align=right>Map Progress Color : </td><td><input type='text' id='mpc' name='mpc' value='".$mapProgressColor."' data-jscolor='{}' /></td></tr>";
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

  $('input[type="text"]').keydown(function(e){
      e.preventDefault();
  });

});

</script>

</body>
</html>
