<?php
include("config.php");
include("functions.php");

$list="";

$result=db_query("SELECT user, _id FROM user WHERE user != '".$config['owner']."'" );
$numrows=mysqli_num_rows($result);
if ( $numrows == 0 )
{
    echo "No users found.";
    exit();
}
else
{
  while ($row = mysqli_fetch_row($result))
  {
    $list .= "<button class=databutton onclick='javascript:doDeleteUser(".$row[1].")'>".$row[0]."</button><br>\n";
  }
  echo $list;
}

?>

