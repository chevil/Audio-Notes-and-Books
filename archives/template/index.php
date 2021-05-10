<?php

session_start();

if ( !isset($_SESSION['schtroumpf']) || !isset($_SESSION['papa']) )
{
    header( "Location: ../../index.php" );
    exit();
}

?>

<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <title>__title__</title>

        <!-- Bootstrap -->
        <link rel="stylesheet" href="../../css/bootstrap.min.css">
        <link rel="stylesheet" href="../../css/style.css" />
        <link rel="stylesheet" href="../../css/alertify.core.css" />
        <link rel="stylesheet" href="../../css/alertify.default.css" />
        <link rel="stylesheet" href="../../css/spinner.css" />
        <link rel="stylesheet" href="../../css/tabs.css" />
        <link rel="stylesheet" href="../../css/app.css" />
        <link rel="stylesheet" href="../../css/font-awesome.min.css" />

        <script type="text/javascript" src="../../js/jquery.min.js"></script>
        <script type="text/javascript" src="../../js/bootstrap.min.js"></script> 

        <script type="text/javascript" src="../../js/trivia.js"></script>
        <script type="text/javascript" src="../../js/alertify.min.js"></script>
        <script type="text/javascript" src="../../js/circular-json.js"></script>

    </head>

    <body background="../../img/background.png">
    <a href="../../index.php"><i class="fa fa-chevron-left fa-2x" aria-hidden="true" style="color: #000000; float:left; margin-left:20px; margin-top:-15px;" ></i></a>

        <center>
        <button id="biography" class="tablinks" onclick="openTab('Biography')">Biography</button>
        <button id="description" class="tablinks" onclick="openTab('Description')">Description</button>
        <button id="free" class="tablinks" onclick="openTab('Free')">Free Notes</button>
        <button id="linear" class="tablinks" onclick="openTab('Linear')">Linear Notes</button>
        <button id="documents" class="tablinks" onclick="openTab('Documents')">Documents</button>
        <table width=80%><hr/></table>
        </center>

        <div class="contents-tab">

            <div id="Biography" class="tabcontent">
                <h3>Biography</h3>
            </div>

            <div id="Description" class="tabcontent">
                <h3>Description</h3>
            </div>

            <div id="Free" class="tabcontent">
                <iframe src="free-notes.php" width=100% height=800px></iframe>
            </div>

            <div id="Linear" class="tabcontent">
                <iframe src="linear-notes.php" width=100% height=800px></iframe>
            </div>

            <div id="Documents" class="tabcontent">
                <h3>Documents</h3>
            </div>

        </div>
    </body>

<script type="text/javascript">


var openTab = function(name) {
  $(".tabcontent").css("display","none");
  $(".tablinks").removeClass("active");
  $("#"+name).css("display","block");
  $("#"+name.toLowerCase()).addClass("active");
} 

function getParameterByName(name) {
    var url = window.location.href;
    name = name.replace(/[\[\]]/g, '\\$&');
    var regex = new RegExp('[?&]' + name + '(=([^&#]*)|&|#|$)'),
        results = regex.exec(url);
    if (!results) return 0;
    if (!results[2]) return 0;
    return decodeURIComponent(results[2].replace(/\+/g, ' '));
}

var sstart = getParameterByName( "start" );
var user = '<?php echo $_SESSION['schtroumpf']; ?>';
var ucolor = '<?php echo $_SESSION['color']; ?>';

$(document).ready( function(){
    openTab("Free");
});

</script>

</html>
