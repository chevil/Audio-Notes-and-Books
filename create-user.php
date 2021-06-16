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
  <link href="css/bootstrap.min.css" rel="stylesheet">
  <link href="css/app.css" rel="stylesheet">
  <link rel="stylesheet" href="css/font-awesome.min.css" />
  <link href="css/all.css" rel="stylesheet">

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
<a href="./index.php"><i class="fa fa-home fa-2x" aria-hidden="true" style="color: #999; float:left; margin-left:20px; margin-top:28px;" ></i></a>

<center><table width=90%>
<tr><td align=right>
</td><td align=center>
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
<table width=100% align=center>
<tr><td width=50% align=right>
<label for='user'>User</label>
</td><td width=50%>
<input type='text' id='user' name='user' />
</td></tr align=center>
<tr><td width=50% align=right>
<label for='password'>Password</label>
</td><td width=50%>
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
