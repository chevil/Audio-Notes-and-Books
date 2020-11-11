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
        <title>wavesurfer.js | Annotation tool</title>

        <!-- Bootstrap -->
        <link rel="stylesheet" href="../../css/bootstrap.min.css">
        <link rel="stylesheet" href="../../css/style.css" />
        <link rel="stylesheet" href="../../css/alertify.core.css" />
        <link rel="stylesheet" href="../../css/alertify.default.css" />
        <link rel="stylesheet" href="../../css/app.css" />
        <link rel="stylesheet" href="../../css/speech.css" />
        <link rel="stylesheet" href="../../css/spinner.css" />

        <script type="text/javascript" src="../../js/jquery.min.js"></script>
        <script type="text/javascript" src="../../js/bootstrap.min.js"></script> 
        <script type="text/javascript" src="../../js/wavesurfer.min.js"></script>
        <!-- plugins -->
        <script type="text/javascript" src="../../js/wavesurfer.timeline.min.js"></script>
        <script type="text/javascript" src="../../js/wavesurfer.regions.min.js"></script>
        <script type="text/javascript" src="../../js/wavesurfer.minimap.min.js"></script>

        <script type="text/javascript" src="../../js/trivia.js"></script>
        <script type="text/javascript" src="../../js/alertify.min.js"></script>
        <script type="text/javascript" src="../../js/circular-json.js"></script>
        <script type="text/javascript" src="https://cdn.tiny.cloud/1/fsisf6nug1vh20mrqte7djkhpu0j1umti1udbihiykd71g9w/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script>

        <!-- App -->
        <script type="text/javascript" src="app.js"></script>
    </head>

    <body>
    <a href="../../index.php"><img src="../../img/back.png" style="float:left; margin-left:20px; margin-top:-15px;" width=30px height=30px /></a>
    <img id="help" src="../../img/help.png" style="float:left; margin-left:10px;" width=30px height=30px>
    <div id="zvalue" class="zoom-value"></div>
    <img id="plus" src="../../img/plus.png" width=20px height=20px />
    <img id="minus" src="../../img/minus.png" width=20px height=20px />

        <div class="container">
            <div class="header">
                <h3 itemprop="title" id="title">Title : __title__ (__date__)</h3>
            </div>

            <div id="demo">
		<div id="subtitle" class="speech">
		    <div id="isubtitle" class="ispeech"></div>
                    <div id="speaker" class="speaker">
                    <div id="ispeaker" class="ispeaker">Couleur Caca</div>
                    <div id="sfull" class="sfull" data-action="pause"><img src="../../img/fullscreen.png" width="20px" height="20px" /></div>
                    </div>
                </div>
		<div id="cursor" class="speech-cursor">&nbsp;</div>
                <div id="waveform"></div>
                <div id="wave-timeline"></div>
                <div id="wave-minimap"></div>
                <div class="modal fade" id="modal-form" role="dialog">
                  <div class="modal-dialog">
                    <div id="audiobook-div"><img id="audiobook" src="../../img/audiobook.png" width="30px" height="30px" /></div>
                    <div class="modal-content">
                      <center>
                        <img id="fplay" width=25px height=25px src="../../img/play.png" data-action="play">
                      </center>
                      <form role="form" id="edit" name="edit" style="transition: opacity 300ms linear; margin: 30px 0;">
                         <div class="form-group">
                             <label for="note">Note</label>
                             <textarea id="note" class="form-control" rows="10" name="note"></textarea>
                         </div>
                         <button type="submit" class="btn btn-success btn-block">Save</button>
                         <center><i>or</i></center>
                         <button type="button" class="btn btn-danger btn-block" data-action="delete-region">Delete</button>
                      </form>
                    </div>
                  </div>
                </div>

                <div class="modal fade" id="modal-book" role="dialog">
                  <div class="modal-dialog modal-bdialog">
                    <center><h3>Audiobook</h3></center>
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

                <div class="modal fade" id="modal-sfull" role="dialog">
                  <div class="modal-dialog modal-fdialog">
                    <div id="content-fs" class="modal-content modal-fcontent">
                    </div>
                  </div>
                </div>

                <br/><br/>
                <div class="row" style="width:100%;">
                    <center>
                        <img id="backward" width=40px height=40px src="../../img/backward.png" data-action="back">
                        <img id="play" width=40px height=40px src="../../img/play.png" data-action="play">
                        <img id="forward" width=40px height=40px src="../../img/forward.png" data-action="forth">
                    </center>

                    <div style="margin-left:40%; margin-top:30px;">
                        <button class="btn btn-info btn-block btn-export" data-action="export" title="Export annotations to JSON">
                            <i class="glyphicon glyphicon-file"></i>
                            Export Annotations
                        </button>
                    </div>
                </div>
            </div>

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
