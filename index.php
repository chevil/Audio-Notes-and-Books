<?php
include("config.php");
include("functions.php");

session_start();
?>

<html>
<head>
  <meta charset="UTF-8">
  <script src="js/jquery.min.js"></script>
  <script src="js/alertify.min.js"></script>
  <link href="css/bootstrap.min.css" rel="stylesheet">
  <link href="css/alertify.core.css" rel="stylesheet">
  <link href="css/alertify.default.css" rel="stylesheet">
  <link href="css/app.css" rel="stylesheet">
  <link href="css/all.css" rel="stylesheet">

  <script type="text/javascript">

   function checkLogin() {
       user=$("#loginform :input[name='user']").val();
       password=$("#loginform :input[name='password']").val();
       if ( user == "" )
       {
          alertify.alert("User name cannot be empty");
          return;
       }
       if ( password == "" )
       {
          alertify.alert("Password cannot be empty");
          return;
       }
       $.post( "check-login.php", { user: user, password: password }, function(data) {
        if ( data === "OK" )
        {
          document.location = "index.php";
        }
        else
        {
          alertify.alert(data.replace("ERR: ",""));
        }
       })
       .fail(function() {
          alertify.alert("Login script error");
       });
   }

   function logout() {
       $.post( "logout.php", function(data) {
        if ( data === "OK" )
        {
          document.location = "index.php";
        }
        else
        {
          alertify.alert(data);
        }
       })
       .fail(function() {
          alertify.alert("Logout error");
       });
   }

   function createUser() {
     document.location="create-user.php";
   }

   function deleteUser() {
      $.post( "user-delete-list.php", function(data) {
        $("#user-delete").html(data); 
      })
      .fail(function() {
        alertify.alert("Couldn't get user list");
      });
    }

   function doDeleteUser(id) {
      $.post( "delete-user.php", { _id: id }, function(data) {
        if ( data == "OK" )
        {
          $("#user-delete").html("The user has been deleted"); 
        }
        else
        {
          $("#user-delete").html(data.replace("ERR: ","")); 
        }
      })
      .fail(function() {
        alertify.alert("Couldn't delete user");
      });
   }

   function createArchive() {
      document.location="create-archive-form.php";
   }

   function manageArchives() {
      document.location="manage-archives.php";
   }

   function editSettings() {
     document.location="edit-settings.php";
   }

   function manageBooks() {
      document.location="manage-books.php";
   }

  </script>

</head>

<body background="img/background.png">
<center><table width=90%>
<tr><td align=right>
</td><td align=center>
<h1><?php echo $config['project-name']; ?></h1>
</td</tr>
</table></center>

<?php if (isset($_SESSION['schtroumpf']) && isset($_SESSION['papa']) )
{
print "
<table width=100% align=center>
<tr><td align=center colspan=2>
</td></tr>";

if (isset($_SESSION['schtroumpf']) && $_SESSION['schtroumpf']==$config['owner'] )
print "
<tr><td align=center>
<button class='bluebutton' onclick='javascript:createUser()' align=center>Create User</button>
</td></tr>
<tr><td align=center>
<button class='bluebutton' onclick='javascript:deleteUser()' align=center>Delete User</button>
<div id='user-delete'></div>
</td></tr>
<tr><td align=center>
<button class='bluebutton' onclick='javascript:editSettings()' align=center>Settings</button>
</td></tr>
";

print "
<tr><td align=center>
<button class='bluebutton' onclick='javascript:createArchive()' align=center>Create Archive</button>
</td></tr>
<tr><td align=center>
<button class='bluebutton' onclick='javascript:manageArchives()' align=center>Manage Archives</button>
</td></tr>
<tr><td align=center>
<button class='bluebutton' onclick='javascript:manageBooks()' align=center>Manage Audio Books</button>
</td></tr>
<tr><td align=center>
<button class='bluebutton' onclick='javascript:logout()' align=center>Logout</button>
</td></tr>
</table>
";
}
else
{
print "
<form action='javascript:checkLogin()' id='loginform' name='loginform' action=post>
<table width=100% align=center>
<tr><td align=center colspan=2 width=100%></td></tr>
<tr><td align=right width=50%>
<label for='user'>User</label>
</td><td width=50%>
<input type='text' id='user' name='user' />
</td></tr>
<tr><td align=right width=50%>
<label for='password'>Password</label>
</td><td width=50%>
<input type='password' id='password' name='password' />
</td></tr>
<tr><td align=center colspan=2 width=100%></td></tr>
<tr><td align=center colspan=2 width=100%></td></tr>
<tr><td align=center width=100% colspan=2>
<input type='submit' class='bluebutton' value='Login' />
</td></tr>
</table>
</form>
";
}
?>

<script type="text/javascript">
$(document).ready( function(){
});
</script>

</body>
</html>
