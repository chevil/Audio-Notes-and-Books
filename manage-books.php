<?php
include("config.php");
include("functions.php");

session_start();

if ( isset( $_GET['search'] ) )
{
   $search = strtolower($_GET['search']);
}
else
{
   $search = "";
}

$clause = "WHERE ( LOWER(title) LIKE '%".addslashes($search)."%' ) OR ( LOWER(user) LIKE '%".addslashes($search)."%' )";

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
   $resallbooks = db_query( "SELECT DISTINCT title FROM audiobook ".$clause );
   $allcount = mysqli_num_rows( $resallbooks );
   $nbpages = intval( $allcount / $size );
   if ( $nbpages*$size < $allcount )
   {
      $nbpages += 1;
   }

   $respagebooks = db_query( "SELECT title, id, user FROM audiobook ".$clause." GROUP BY title" );
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

  <link href="css/bootstrap.min.css" rel="stylesheet">
  <link href="css/alertify.core.css" rel="stylesheet">
  <link href="css/alertify.default.css" rel="stylesheet">
  <link href="css/app.css" rel="stylesheet">

  <script type="text/javascript" src="js/jquery.min.js"></script>
  <script type="text/javascript" src="js/alertify.min.js"></script>
  <script type="text/javascript" src="js/sort-table.min.js"></script>
  <script type="text/javascript" src="js/bootstrap.min.js"></script> 
  <script type="text/javascript" src="js/sortable.js"></script> 
  <script type="text/javascript" src="js/jquery.sortable.js"></script> 

  <script type="text/javascript">

    var ellipsis = function(el)
    {
      if(el.css("overflow") == "hidden")
      {
        var text = el.html();
        var multiline = el.hasClass('multiline');
        var t = el.clone()
        .hide()
        .css('position', 'absolute')
        .css('overflow', 'visible')
        .width(multiline ? el.width() : 'auto')
        .height(multiline ? 'auto' : el.height());
        el.after(t);

        function height() { return t.height() > el.height(); };
        function width() { return t.width() > el.width(); };

        var func = multiline ? height : width;
        while (text.length > 0 && func())
        {
          text = text.substr(0, text.length - 1);
          t.html(text + "..." + "<img class='rightcorner' src='img/cross.png' width='15px' height='15px' onclick='javascript:deleteLine(event);' />" );
        }
        el.html(t.html());
        t.remove();
      }
    };

    function deleteLine(e) {
       var target = (e.target) ? e.target : e.srcElement;
       target.previousSibling.remove();
       target.remove();
    }

    function getBook(title) {
       title = title.replace(/%27/g,"'");
       $.post( "gen-book.php", { title: encodeURIComponent(title), }, function(data) {
         if ( data.indexOf("ERR:") < 0 )
         {
            alertify.alert( "The book has been generated.",
              function () {
                window.open(data, "_blank", "height=1px, width=1px");
              }
            );
         }
         else
         {
           alertify.alert(data.replace("ERR: ",""));
         }
       })
       .fail(function() {
          alertify.alert("Could not generate book.");
       });
    }

    function updateBook() {
       var title = $("#title").val();
       var otitle = $("#otitle").val();
       var neworder = '';
       $("#excerpts").find("li").each(function(i, li){
            var order = li.innerText.substr(0,li.innerText.indexOf(":"));
            if ( order != '' )
            {
               neworder += order+",";
            }
       });
       neworder=neworder.substr(0,neworder.lastIndexOf(","));
       console.log(neworder);
       $.post( "update-book.php", { title: encodeURIComponent(title), 
                                    otitle: encodeURIComponent(otitle),
                                    order: neworder }, function(data) {
         if ( data.indexOf("ERR:") < 0 )
         {
            alertify.alert( "The book has been updated.",
              function () {
                document.location = "manage-books.php?start=<?php echo $start; ?>&size=<?php echo $size; ?>&search=<?php echo $search; ?>";
              }
            );
         }
         else
         {
           alertify.alert(data.replace("ERR: ",""));
         }
       })
       .fail(function() {
          alertify.alert("Could not update book.");
       });
    }

    function editBook(title) {
       title = title.replace(/%27/g,"'");
       $.post( "get-book-data.php", { title: encodeURIComponent(title) }, function(data) {
        if ( data.indexOf("ERR:") < 0 )
        {
          contents = JSON.parse(data);
          $("#modal-book").on("shown.bs.modal", function() {
             $(".ellipsis").remove();
             for ( i=0; i<contents.length; i++)
             {
                $("#title").val(contents[i]["title"]);
                $("#otitle").val(contents[i]["title"]);
                var el = $("<li class='ellipsis'>"+contents[i]["order"]+": "+contents[i]["note"]+"<img class='rightcorner' src='img/cross.png' width='15px' height='15px' onclick='javascript:deleteLine(event);' /></li>");
                $("#excerpts").append(el);
                ellipsis(el);
             }
          });
          $("#modal-book").modal("show");
        }
        else
        {
          alertify.alert(data.replace("ERR: ",""));
        }
       })
       .fail(function() {
          alertify.alert("Could not get book contents.");
       });
    }

    function deleteBook(title) {
       title = title.replace(/%27/g,"'");
       alertify.confirm( "Are you sure that you want to delete this book?",
         function (e) {
           if (e) 
           {
              $.post( "delete-book.php", { title: encodeURIComponent(title), user: '<?php echo $_SESSION['schtroumpf']; ?>' }, function(data) {
                if ( data == "OK" )
                {
                  alertify.alert( "The book has been deleted.",
                    function () {
                      document.location = "manage-books.php?start=<?php echo $start; ?>&size=<?php echo $size; ?>&search=<?php echo $search; ?>";
                    }
                  );
                }
                else
                {
                  alertify.alert( data.replace("ERR: ","") );
                }
              })
              .fail(function() {
                 alertify.alert("Couldn't delete book");
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
<h1>Audio Books</h1>
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
   print "<a href='manage-books.php?start=".($page*$size)."&search=".$search."' >".($page+1)."</a>&nbsp;";
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
print "<th align=left>Title</th><th align=left>Creator</th><th align=center>Edit</th><th align=center>Delete</th><th align=center>Generate</th>";
while ( $rowbook = mysqli_fetch_row( $respagebooks) )
{
   $htitle = preg_replace('/\'/','%27', $rowbook[0] );
   print "<tr><td align=left>".urldecode($rowbook[0])."</td>";
   print "<td align=left>".$rowbook[2]."</td>";
   print "<td align=center><a href='javascript:editBook(\"".$htitle."\");'><img src='img/edit.png' width=20px height=20px /></a></td>";
   print "<td align=center><a href='javascript:deleteBook(\"".$htitle."\");'><img src='img/delete.png' width=20px height=20px /></a></td>";
   print "<td align=center><a href='javascript:getBook(\"".$htitle."\");'><img src='img/generate.png' width=20px height=20px /></a></td>";
   print "</tr>";
   $count++;
}

?>

</table></center>

<div class="modal fade" id="modal-book" role="dialog">
  <div class="modal-dialog">
    <div id="audiobook-div"><img id="audiobook" src="img/audiobook.png" width="30px" height="30px" /></div>
    <div class="modal-content">
      <form role="form" type="post" action="javascript:updateBook()" id="edit" name="edit" style="transition: opacity 300ms linear; margin: 30px 0;">
         <div class="form-group">
             <label for="title">Title</label>
             <input type="text" id="title" class="form-control" name="title" />
             <input type="hidden" id="otitle" class="form-control" name="otitle" />
         </div>
         <div class="form-group">
             <label for="excerpts">Excerpts</label>
             <ul id="excerpts" name="excerpts">
             </ul>
         </div>
         <button type="submit" class="btn btn-success btn-block">Update</button>
      </form>
    </div>
  </div>
</div>

<script type="text/javascript">
$(document).ready( function(){

   $("#excerpts").sortable();

});
</script>

</body>
</html>
