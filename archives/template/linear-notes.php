<?php

include("../../config.php");
include("../../functions.php");

session_start();

if ( !isset($_SESSION['schtroumpf']) || !isset($_SESSION['papa']) )
{
    header( "Location: ../../index.php" );
    exit();
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
        <link rel="stylesheet" href="../../css/app.css" />
        <link rel="stylesheet" href="../../css/speech.css" />
        <link rel="stylesheet" href="../../css/spinner.css" />
        <link rel="stylesheet" href="../../css/font-awesome.min.css" />

        <script type="text/javascript" src="../../js/jquery.min.js"></script>
        <script type="text/javascript" src="../../js/bootstrap.min.js"></script> 
        <script type="text/javascript" src="../../js/wavesurfer.min.js"></script>
        <script type="text/javascript" src="../../js/wavesurfer.regions.min.js"></script>

        <!-- no global actions -->
        <!-- <script type="text/javascript" src="../../js/trivia.js"></script> -->
        <script type="text/javascript" src="../../js/alertify.min.js"></script>
        <script type="text/javascript" src="../../js/circular-json.js"></script>
        <script type="text/javascript" src="https://cdn.tiny.cloud/1/fsisf6nug1vh20mrqte7djkhpu0j1umti1udbihiykd71g9w/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script>

        <!-- App -->
        <script type="text/javascript" src="appl.js"></script>
    </head>

    <body>

        <div class="modal fade" id="modal-help" role="dialog">
            <div class="modal-dialog modal-hdialog">
                <div class="modal-content modal-hcontent">
                    <p>
                    <center><b>Mini help :</b></center><br />
                     Select a part of the file to create a region.<br /><br />
                     Double Click on a region to play it and enter a transcription or an annotation.<br /><br />
                     To resume playing the file normally, close the annotation form.<br /><br />
                     When a region is edited, you can add it to an audio book clicking on the audiobook icon.
                 </p>
                 </div>
             </div>
        </div>

        <div class="container">
            <div class="header">
                <h3 itemprop="title" id="title">Title : __title__ (__date__)</h3>
                <i id="help" class="fa fa-question-circle fa-2x" aria-hidden="true" ></i>
            </div>

            <div id="demo" class="outer-wave-full">
		<div class="upper-toolbar">
                    <div id="slabel" class="speed-label-solo">Speed</div>
                </div>
		<div class="lower-toolbar">
		    <div id="ptime" class="play-time"></div>
                    <div id="svalue" class="speed-value"></div>
                    <i id="splus" class="fa fa-plus-square-o fa-2x" width=20px height=20px ></i>  
                    <i id="sminus" class="fa fa-minus-square-o fa-2x" width=20px height=20px ></i>  
                </div>
                <div class="lds-spinner"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>
                <div id="waveform"></div>
                <br/><br/>
            </div>
            <div id="linear-notes" class="linear-outer-notes">
            </div>
        </div>
        <div id="wavecolor" style="display:none;"><?php echo $waveColor; ?></div>
        <div id="progresscolor" style="display:none;"><?php echo $progressColor; ?></div>
        <div id="mapwavecolor" style="display:none;"><?php echo $mapWaveColor; ?></div>
        <div id="mapprogresscolor" style="display:none;"><?php echo $mapProgressColor; ?></div>
    </body>

<script type="text/javascript" >

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

</script>

</html>
