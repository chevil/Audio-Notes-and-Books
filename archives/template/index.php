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
        <script type="text/javascript" src="https://cdn.tiny.cloud/1/fsisf6nug1vh20mrqte7djkhpu0j1umti1udbihiykd71g9w/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script>

    </head>

    <body background="../../img/background.png">
    <a href="../../index.php"><i class="fa fa-chevron-left fa-1x" aria-hidden="true" style="color: #000000; float:left; margin-left:20px; margin-top:-15px;" ></i></a>

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
                <center><h3>Biography</h3></center>
                <div id="biography-edit"></div>
            </div>

            <div id="Description" class="tabcontent">
                <center><h3>Description</h3></center>
                <div id="description-edit"></div>
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

    var jqxhr = $.post( {
       url: '../../get-biography.php',
       data: {
          title: $(document).attr('title'),
       },
       dataType: "text/html" 
    }).fail(function(data) {
       if ( data.status === 200 ) {
          console.log( "getting biography success : " + data.responseText );
          $('#biography-edit').html(data.responseText);
          tinymce.init({
            setup:function(ed) {
               ed.on('change', function(e) {
                 // console.log('tinymce changed : ', ed.getContent());
                 var jqxhr = $.post( {
                   url: '../../save-biography.php',
                   data: {
                     title: $(document).attr('title'),
                     biography: ed.getContent().replaceAll("<p>","<div>").replaceAll("</p>","</div>")
                   },
                   dataType: 'text/plain'
                 }, function() {
                   console.log( "saving biography succeeded" );
                 }).fail(function(error) {
                   if ( error.status === 200 ) {
                      console.log( "saving biography success");
                   } else {
                      console.log("saving biography failed : " + JSON.stringify(error));
                      alertify.alert("saving biography failed : " + JSON.stringify(error));
                   }
                 });
               });
            },
            selector: '#biography-edit',
            plugins: 'advlist autolink lists link image charmap hr pagebreak searchreplace wordcount help insertdatetime emoticons charmap ',
            branding: false,
            elementpath: false,
            toolbar: true,
            height: 750,
            statusbar: false,
            placeholder: 'Type here...',
            contextmenu: 'link image',
            entity_encoding : 'raw',
            menu: {
              file: { title: '', items: '' },
              edit: { title: 'Edit', items: 'undo redo | cut copy paste | selectall | searchreplace' },
              view: { title: '', items: '' },
              insert: { title: 'Insert', items: 'image link media charmap | hr nonbreaking | insertdatetime' },
              format: { title: 'Format', items: 'bold italic underline strikethrough superscript subscript | forecolor backcolor | fontformats fontsizes align | removeformat' },
              tools: { title: 'Tools', items: 'wordcount' },
              help: { title: 'Help', items: 'help' }
            }
          });

       } else {
          console.log("getting biography failed : " + JSON.stringify(data));
          alertify.alert("getting biography failed : " + JSON.stringify(data));
       }
    });

    var jqxhr = $.post( {
       url: '../../get-description.php',
       data: {
          title: $(document).attr('title'),
       },
       dataType: "text/html" 
    }).fail(function(data) {
       if ( data.status === 200 ) {
          console.log( "getting description success : " + data.responseText );
          $('#description-edit').html(data.responseText);
          tinymce.init({
            setup:function(ed) {
               ed.on('change', function(e) {
                 // console.log('tinymce changed : ', ed.getContent());
                 var jqxhr = $.post( {
                   url: '../../save-description.php',
                   data: {
                     title: $(document).attr('title'),
                     description: ed.getContent().replaceAll("<p>","<div>").replaceAll("</p>","</div>")
                   },
                   dataType: 'text/plain'
                 }, function() {
                   console.log( "saving description succeeded" );
                 }).fail(function(error) {
                   if ( error.status === 200 ) {
                      console.log( "saving description success");
                   } else {
                      console.log("saving description failed : " + JSON.stringify(error));
                      alertify.alert("saving description failed : " + JSON.stringify(error));
                   }
                 });
               });
            },
            selector: '#description-edit',
            plugins: 'advlist autolink lists link image charmap hr pagebreak searchreplace wordcount help insertdatetime emoticons charmap ',
            branding: false,
            elementpath: false,
            toolbar: true,
            height: 750,
            statusbar: false,
            placeholder: 'Type here...',
            contextmenu: 'link image',
            entity_encoding : 'raw',
            menu: {
              file: { title: '', items: '' },
              edit: { title: 'Edit', items: 'undo redo | cut copy paste | selectall | searchreplace' },
              view: { title: '', items: '' },
              insert: { title: 'Insert', items: 'image link media charmap | hr nonbreaking | insertdatetime' },
              format: { title: 'Format', items: 'bold italic underline strikethrough superscript subscript | forecolor backcolor | fontformats fontsizes align | removeformat' },
              tools: { title: 'Tools', items: 'wordcount' },
              help: { title: 'Help', items: 'help' }
            }
          });

       } else {
          console.log("getting description failed : " + JSON.stringify(data));
          alertify.alert("getting description failed : " + JSON.stringify(data));
       }
    });

});

</script>

</html>
