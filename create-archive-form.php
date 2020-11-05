<?php
include("config.php");
include("functions.php");

session_start();

if ( !isset($_SESSION['schtroumpf']) || !isset($_SESSION['papa']) )
{
    header( "Location: ./index.php" );
    exit();
}

?>

<html>
<head>
  <meta charset="UTF-8">
  <style type="text/css">
      #error-zone { height: 50px; width : 500px; text-align: center; margin-top: 20px;
                    line-height: 50px; vertical-align:middle; opacity : 0;
                    -moz-border-radius: 15px; border-radius: 15px; background : lightblue }
      .bluebutton { height: 30px; width : 200px; text-align: center;
                   line-height: 30px; vertical-align:middle; opacity : 1;
                   -moz-border-radius: 10px; border-radius: 10px; background : lightblue }
      .databutton { height: 30px; width : 400px; text-align: center;
                   line-height: 30px; vertical-align:middle; opacity : 1;
                   -moz-border-radius: 10px; border-radius: 10px; background : lightgrey }
  </style>
  <link rel="stylesheet" href="css/spinner.css" />
  <link href="css/alertify.core.css" rel="stylesheet">
  <link href="css/alertify.default.css" rel="stylesheet">

  <script src="js/jquery.min.js"></script>
  <script src="js/alertify.min.js"></script>
  <script type="text/javascript">

    function doCreateArchive() {
       url=$("#createform :input[name='url']").val();
       if ( url == "" )
       {
          alertify.alert("Please, enter a url!");
          return;
       }
       $('.lds-spinner').css('opacity','1.0');
       $.get( "create-archive.php", { file : encodeURIComponent(url), user : '<?php echo $_SESSION['schtroumpf']; ?>' }, function(data) {
        if ( data.indexOf("ERR:")>=0 )
        {
          $('#error-zone').css({background:'red'});
          $('#error-zone').html(data.replace("ERR: ",""));
          $('#error-zone').animate({ opacity : 1.0 },{queue:false,duration:1000});
        }
        else
        {
          document.location = data;
        }
       })
       .fail(function() {
        $('#error-zone').css({background:'red'});
        $('#error-zone').html("Archive creation error");
        $('#error-zone').animate({ opacity : 1.0 },{queue:false,duration:1000});
       });
    }

    function back() {
       document.location='index.php';
    }

  </script>

</head>

<body background="img/background.png">
<a href="./index.php"><img src="img/back.png" width=40px height=40px /></a>

<center><table width=40%>
<tr><td align=right>
</td><td valign=center>
<h1><?php echo $config['project-name']; ?></h1>
</td</tr>
</table></center>

<?php
print "
<form action='javascript:doCreateArchive()' id='createform' name='createform' action=post>
<table width=50% align=center>
<tr><td align=center colspan=2>
<label for='user'>Url</label>
</td></tr>
<tr><td colspan=2>
<input type='text' id='url' name='url' style='width:100%;'/>
</td></tr>
<tr><td colspan=2 align=center>
<br/><br/>
<input type='submit' class='bluebutton' value='Create' />
</td></tr>
<tr><td align=center colspan=2>
<div id='error-zone'>Error text</div>
</td></tr>
</table>
</form>
";
?>

<center><div class="lds-spinner"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div></center>


<script type="text/javascript">
$(document).ready( function(){
  $('.lds-spinner').css('opacity','0.0');
});
</script>

</body>
</html>
