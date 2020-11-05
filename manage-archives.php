<?php
include("config.php");
include("functions.php");

session_start();

if (!isset($_SESSION['schtroumpf']) || !isset($_SESSION['papa']) )
{
   header("Location: index.php");
   die();
}

if ( isset( $_GET['search'] ) )
{
   $search = strtolower($_GET['search']);
}
else
{
   $search = "";
}

$clause = "WHERE ( LOWER(title) LIKE '%".addslashes($search)."%' ) OR ( LOWER(author) LIKE '%".addslashes($search)."%' ) OR ( LOWER(collection) LIKE '%".addslashes($search)."%' ) OR ( LOWER(date) LIKE '%".addslashes($search)."%' )";

if ( isset( $_GET['start'] ) )
{
   $start = $_GET['start'];
}
else
{
   $start = 0;
}

if ( isset( $_GET['size'] ) )
{
   $size = $_GET['size'];
}
else
{
   $size = 20;
}

if (isset($_SESSION['schtroumpf']) && isset($_SESSION['papa']) )
{
   $resallarchives = db_query( "SELECT id FROM archive ".$clause );
   $allcount = mysqli_num_rows( $resallarchives );
   $nbpages = intval( $allcount / $size );
   if ( $nbpages*$size < $allcount )
   {
      $nbpages += 1;
   }

   $respageusers = db_query( "SELECT id, uri, url, author, title, collection, date, creator FROM archive ".$clause );
}
else
{
   header( "Location: ./index.php" );
}

?>

<html>
<head>
  <meta charset="UTF-8">
  <style type="text/css">
      .bluebutton { height: 30px; width : 200px; text-align: center;
                   line-height: 30px; vertical-align:middle; opacity : 1;
                   -moz-border-radius: 10px; border-radius: 10px; background : lightblue }
      .databutton { height: 30px; width : 400px; text-align: center;
                   line-height: 30px; vertical-align:middle; opacity : 1;
                   -moz-border-radius: 10px; border-radius: 10px; background : lightgrey }
      .stable { -moz-border-radius: 10px; border-radius: 10px; background : lightgrey }
      .pages { float: left; margin-left: 10%; width: 80%; overflow-wrap: break-word; }
      .search { float: left; margin-left: 20%;}
      .add { float: right; margin-right: 10%; width: 10%; }
      .license { float: left; margin-left: 10%;}
  </style>

  <link href="css/alertify.core.css" rel="stylesheet">
  <link href="css/alertify.default.css" rel="stylesheet">
  <link href="css/app.css" rel="stylesheet">

  <script src="js/jquery.min.js"></script>
  <script src="js/alertify.min.js"></script>
  <script src="js/sort-table.min.js"></script>

  <script type="text/javascript">

    function editArchive(id) {
      document.location="./edit-archive.php?_id="+id;
    }

    function deleteArchive(id) {
      alertify.confirm( "Are you sure that you want to delete that archive?",
        function (e) {
           if (e) 
           {
              $.post( "delete-archive.php", { _id: id }, function(data) {
                if ( data == "OK" )
                {
                  alertify.alert( "The archive has been deleted.",
                    function () {
                      document.location = "manage-archives.php?start=<?php echo $start; ?>&size=<?php echo $size; ?>&search=<?php echo $search; ?>";
                    }
                  );
                }
                else
                {
                  alertify.alert( data.replace("ERR: ","") );
                }
              })
              .fail(function() {
                 alertify.alert("Couldn't delete archive");
              });
           }
        });
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

<center>
<h1>Audio Archives</h1>
<h3>Count : <?php echo $allcount; ?></h3>
</center>

<?php
print "<form id='search-form' method='get' enctype='multipart/form-data'>";
print "<div class='search'>";
print "Search : ";
print "<input type='text' id='search' name='search' value='".$search."' />";
print "</div>";
print "</form>";
?>
<br/>
<br/>
<center><table width=80% border=0px></table></center>

<div class="pages">
<?php
$page=0;
print "Pages : ";
while ( $page < $nbpages )
{
   print "<a href='manage-archives.php?start=".($page*$size)."&search=".$search."&licensed=".$licensed."' >".($page+1)."</a>&nbsp;";
   if ( $page%30 == 29 )
   {
      // print "<br/>";
   }
   $page++;
}
?>
</div>
<div>&nbsp;</div>

<center><table class="js-sort-table" width=80% border=2px>

<?php

$count = $start+1;
print "<th align=left>Author</th><th align=left>Title</th><th align=left>Collection</th><th align=left>Date</th><th align=left>Creator</th><th align=center>Edit</th><th align=center>Delete</th><th align=center>Annotations</th>";
while ( $rowuser = mysqli_fetch_row( $respageusers) )
{
   print "<tr><td align=left>".$rowuser[3]."</td>";
   print "<td align=left>".$rowuser[4]."</td>";
   print "<td align=left>".$rowuser[5]."</td>";
   print "<td align=left>".$rowuser[6]."</td>";
   print "<td align=left>".$rowuser[7]."</td>";
   print "<td align=center><a href='javascript:editArchive(".$rowuser[0].");'><img src='img/edit.png' width=20px height=20px /></a></td>";
   print "<td align=center><a href='javascript:deleteArchive(".$rowuser[0].");'><img src='img/delete.png' width=20px height=20px /></a></td>";
   print "<td align=center><a href='".$rowuser[1]."'><img src='img/see.png' width=20px height=20px /></a></td>";
   print "</tr>";
   $count++;
}

?>

</table></center>

<script type="text/javascript">
$(document).ready( function(){

});
</script>

</body>
</html>