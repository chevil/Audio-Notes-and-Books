<?php
include("config.php");
include("functions.php");

session_start();

if ( !isset($_SESSION['schtroumpf']) || !isset($_SESSION['papa']) || ($_SESSION['schtroumpf'] != $config['owner']) )
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

  <link rel="stylesheet" href="css/font-awesome.min.css" />

  <script src="js/jquery.min.js"></script>

  <script type="text/javascript">
Unknown
    function doCreateUser() {
       user=$("#createform :input[name='user']").val();
       password=$("#createform :input[name='password']").val();
       $.post( "do-create-user.php", { user : user, password : password }, function(data) {
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
        $('#error-zone').html("User creation error");
        $('#error-zone').animate({ opacity : 1.0 },{queue:false,duration:1000});
       });
    }

    function back() {
       document.location='index.php';
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
<h1>Create User</h1>
</center>

<?php if (isset($_SESSION['schtroumpf']) && isset($_SESSION['papa']) )
{
print "
<form action='javascript:doCreateUser()' id='createform' name='createform' action=post>
<table width=50% align=center>
<tr><td align=center>
<label for='user'>User</label>
</td><td align=center>
<input type='text' id='user' name='user' />
</td></tr align=center>
<tr><td align=center>
<label for='password'>Password</label>
</td><td align=center>
<input type='password' id='password' name='password' />
</td></tr>
<tr>
<td colspan=2 align=center>
<br/><br/>
<input type='submit' class='bluebutton' value='Create' />
</td></tr>
<tr><td align=center colspan=2>
<div id='error-zone'>Error text</div>
</td></tr>
</table>
</form>
";
}
else
{
print "
<script type='text/javascript'>
  document.location='index.php';
</script>
";
}
?>


<script type="text/javascript">
$(document).ready( function(){
});
</script>

</body>
</html>
