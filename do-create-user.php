<?php
include("config.php");
include("functions.php");

session_start();

if ( !isset($_SESSION['schtroumpf']) || !isset($_SESSION['papa']) || ($_SESSION['schtroumpf'] != $config['owner']) )
{
    echo "ERR: Unauthorized access.";
    exit();
}

if ( !isset($_POST['user']) )
{
    echo "ERR: The user name must be set.";
    exit();
}
else
{
    $user=$_POST['user'];
}

if ( !isset($_POST['password']) )
{
    echo "ERR: The user password must be set.";
    exit();
}
else
{
    $password=$_POST['password'];
}


$acolor="rgba(".rand(0,255).",".rand(0,255).",".rand(0,255).",0.1)";

$userres=db_query( "SELECT user FROM user  WHERE user='".addslashes($user)."'" );
if ( mysqli_num_rows($userres) > 0 )
{
   echo "ERR: This user already exists.";
}
else
{
   $insres=db_query( "INSERT INTO user ( user, password, color ) VALUES ('".addslashes($user)."','".addslashes($password)."', '".$acolor."' )" );
   if ( $insres == '1')
   {
      echo "OK";
   }
   else
   {
      echo "ERR: Insert Error";
   }
}

?>
