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

        <script type="text/javascript" src="../../js/trivia.js"></script>
        <script type="text/javascript" src="../../js/alertify.min.js"></script>
        <script type="text/javascript" src="../../js/circular-json.js"></script>
        <script type="text/javascript" src="https://cdn.tiny.cloud/1/fsisf6nug1vh20mrqte7djkhpu0j1umti1udbihiykd71g9w/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script>

        <!-- App -->
        <script type="text/javascript" src="appl.js"></script>
    </head>

    <body>

        <div class="modal fade" id="modal-help" role="dialog">
            <div class="modal-dialog modal-hdialog">
                <center><h3>Mini help</h3></center>
                <div class="modal-content modal-hcontent">
                    <p>
                     In this mode, the archive is automatically divided in sections divided by silence.<br /><br />
                     You can enter a note for each section below. The note will show up when file is played.<br /><br />
                     If you want to translate to different languages, you have to start your line with the abbreviation of the language. For example : "en: This conference tackles the issue of ..."<br /><br />
                     You can enter multiple translations this way and the user can choose his language on the right side.<br /><br /> 
                     You can also export the notes of a specific language to a subtitles file (SRT).<br /><br /> 
                     As in free mode, you can add a note to an audiobook using the book icon next to it.<br /><br /> 
                     Note : If the sections created here automatically do not fit your archive, we recommend to use the free mode and create your regions yourself.
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
                    <div id="zlabel" class="zoom-label">Pxs/s</div>
                    <div id="slabel" class="speed-label">Speed</div>
                </div>
		<div class="lower-toolbar">
		    <div id="ptime" class="play-time"></div>
                    <div id="zvalue" class="zoom-value"></div>
                    <i id="zplus" class="fa fa-plus-square-o fa-2x" width=20px height=20px ></i>
                    <i id="zminus" class="fa fa-minus-square-o fa-2x" width=20px height=20px ></i>
                    <div id="svalue" class="speed-value"></div>
                    <i id="splus" class="fa fa-plus-square-o fa-2x" width=20px height=20px ></i>  
                    <i id="sminus" class="fa fa-minus-square-o fa-2x" width=20px height=20px ></i>  
                </div>
                <div class="lds-spinner"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>
                <div id="waveform"></div>
                <div id="subtitle" class="linear-subtitle"></div>
                <div id="subtitle-left" class="linear-subtitle-left"></div>
                <div class="export-notes" id="export-subtitles" onclick="exportSRT()">
                     <button class="btn btn-info btn-block" data-action="export" title="Export annotations to SRT">
                         <i class="glyphicon glyphicon-file"></i>
                         Export 
                     </button>
                </div>
                <br/><br/>

                <div class="modal fade" id="modal-book" role="dialog">
                  <div class="modal-dialog modal-bdialog">
                    <center><b>Add to audiobook</b></center><br/>
                    <div class="modal-content modal-bcontent">
                      <center>
                         <div class="lds-spinner"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>
                      </center>
                      <form role="form" id="addbook" name="addbook" style="transition: opacity 300ms linear; margin: 30px 0;">
                         <div class="form-group">
                             <label for="oldbook">Add To Existing Book</label>
                             <select id="oldbook" name="oldbook">
                                <option value="none">None</option>
                             </select>
                         </div>
                         <div class="form-group">
                             <label for="newbook">Create New Book</label>
                             <input class="form-control" id="newbook" name="newbook" />
                         </div>
                         <button type="submit" class="btn btn-success btn-block">Add</button>
                      </form>
                    </div>
                  </div>
                </div>
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
