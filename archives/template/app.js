/**
 * Create a WaveSurfer instance.
 */
var wavesurfer;
var wzoom;
var wspeed=1.0;
var evid;
var svid;
var currentRegion;
var bRegionId=-1;
var soundfile = '__file_url__';

var strstr = function (haystack, needle) {
  if (needle.length === 0) return 0;
  if (needle === haystack) return 0;
  for (let i = 0; i <= haystack.length - needle.length; i++) {
    if (needle === haystack.substring(i, i + needle.length)) {
      return i;
    }
  }
  return 0;
};

var fullEncode = function(w)
{
 var map=
 {
          '&': '%26',
          '<': '%3c',
          '>': '%3e',
          '"': '%22',
          "'": '%27'
 };

 var encodedW = encodeURI(w);
 return encodedW.replace(/[&<>"']/g, function(m) { return map[m];});
}

var toHHMMSS = function(duration)
{  
   // console.log(duration);
   var hours = Math.floor(duration/3600);
   duration = duration-hours*3600;
   var mins = Math.floor(duration/60);
   duration = duration-mins*60;
   var secs = Math.floor(duration);
   duration = duration-secs;
   var millis = Math.floor(duration*100);
   return ("0"+hours).slice(-2)+":"+("0"+mins).slice(-2)+":"+("0"+secs).slice(-2)+"."+("0"+millis).slice(-2);
}

var getPosition = function(e)
{
   var x = 0;
   var y = 0;
   var es = e.style;
   var el = e;
   if (el.getBoundingClientRect) { // IE
      var box = el.getBoundingClientRect();
      x = box.left + Math.max(document.documentElement.scrollLeft, document.body.scrollLeft) - 2;
      y = box.top + Math.max(document.documentElement.scrollTop, document.body.scrollTop) - 2;
   } else {
      x = el.offsetLeft;
      y = el.offsetTop;
      el = el.offsetParent;
      if (e != el) {
         while (el) {
           x += el.offsetLeft;
           y += el.offsetTop;
           el = el.offsetParent;
         }
      }
      el = e.parentNode;
      while (el && el.tagName.toUpperCase() != 'BODY' && el.tagName.toUpperCase() != 'HTML')
      {
         if (el.style.display != 'inline') {
            x -= el.scrollLeft;
            y -= el.scrollTop;
         }
         el = el.parentNode;
      }
    }
    return {x:x, y:y};
}

var decZoom = function() {
    if ( wzoom <= 1.0 ) 
       wzoom=Math.max(wzoom-0.1,0.1);
    else
       wzoom=Math.max(wzoom-1,1);
    console.log( "wzoom = " + wzoom );
    $('#zvalue').html(("x"+wzoom).substring(0,4));
    evid = setTimeout( "decZoom();", 500 );
}

var incZoom = function() {
    if ( wzoom < 1 ) 
       wzoom=Math.min(wzoom+0.1,1.0);
    else
       wzoom=Math.min(wzoom+1,100);
    $('#zvalue').html(("x"+wzoom).substring(0,4));
    evid = setTimeout( "incZoom();", 500 );
}

var decSpeed = function() {
    wspeed=Math.max(wspeed-0.1,0.1);
    $('#svalue').html(("x"+wspeed).substring(0,4));
    svid = setTimeout( "decSpeed();", 500 );
}

var incSpeed = function() {
    wspeed=Math.min(wspeed+0.1,5.0);
    $('#svalue').html(("x"+wspeed).substring(0,4));
    svid = setTimeout( "incSpeed();", 500 );
}

var moveSpeech = function() {
    var curx, curxx;
    // trick to get cursor position
    $("wave").each(function(i) {
       if (i===1 ) curx = $(this).width();
    });
    if ( curx > $("#waveform").width()/2 ) curx = $("#waveform").width()/2;
    $(".speech").css('margin-left', (curx-40)+'px' );
    $(".play-time").html( toHHMMSS(wavesurfer.getCurrentTime()) + " / " + toHHMMSS(wavesurfer.getDuration()) );
}

var addToBook = function(regid) {
    bRegionId = regid;
    $("#modal-book").modal("show");
}


/**
 * Init & load.
 */
document.addEventListener('DOMContentLoaded', function() {

    progressColor = $("#progresscolor").html();
    waveColor = $("#wavecolor").html();
    mapProgressColor = $("#mapprogresscolor").html();
    mapWaveColor = $("#mapwavecolor").html();
    // Init wavesurfer
    wavesurfer = WaveSurfer.create({
        container: '#waveform',
        height: 200,
        pixelRatio: 1,
        scrollParent: true,
        normalize: true,
        minimap: true,
        mediaControls: true,
        fillParent: true,
        hideScrollbar: true,
        barRadius: 0,
        forceDecode: false,
        waveColor: waveColor,
        progressColor: progressColor,
        backend: 'MediaElement',
        plugins: [
            WaveSurfer.regions.create(),
            WaveSurfer.minimap.create({
                 container: '#wave-minimap',
                 height: 50,
                 showRegions: true,
                 showOverview: true,
                 waveColor: mapWaveColor,
                 progressColor: mapProgressColor,
                 cursorColor: '#000000' // black
            }),
            WaveSurfer.timeline.create({
                container: '#wave-timeline'
            })
        ]
    });

    wavesurfer.util
        .fetchFile({
            responseType: 'json',
            url: 'peaks.json'
        })
        .on('success', function(data) {
            wavesurfer.load(
                soundfile,
                data
            );
        });

    /* Regions */
    wavesurfer.on('ready', function() {

        wavesurfer.enableDragSelection({
            color: randomColor(0.1)
        });

        if (0) {
            loadRegions(JSON.parse(localStorage.regions));
        } else {
            wavesurfer.util
                .ajax({
                    responseType: 'json',
                    url: 'annotations.json'
                })
                .on('success', function(data) {
                    loadRegions(data);
                    // zoom is the number of minutes limited to 10
                    wzoom = Math.floor( wavesurfer.getDuration() / 60.0 )+1;
                    if ( wzoom > 10 ) wzoom = 10;
                    $('#zvalue').html(("x"+wzoom).substring(0,4));
                    wavesurfer.zoom(wzoom);
                    $('#svalue').html(("x"+wspeed).substring(0,4));
		    if ( sstart !== null )
                    {
                       if ( ( wavesurfer.getDuration() > 0 ) && ( sstart >= 0 ) && ( sstart <= wavesurfer.getDuration() ) )
                       {
		          wavesurfer.seekTo( sstart/wavesurfer.getDuration() );
                       }
                    }
                    moveSpeech();
                });
        }
    });

    wavesurfer.on('region-click', propagateClick);
    wavesurfer.on('region-dblclick', editAnnotation);
    wavesurfer.on('region-updated', saveRegions);
    wavesurfer.on('region-removed', saveRegions);
    wavesurfer.on('region-in', showNote);
    wavesurfer.on('region-out', deleteNote);
    wavesurfer.on('region-play', function(region) {
        // console.log( 'got region play : ' + region.id );
        region.on('out', function() {
            console.log( 'restart playing');
            wavesurfer.play(region.start);
        });
    });

    wavesurfer.on('audioprocess', function() {
        // wavesurfer.zoom(wzoom);
        moveSpeech();
    });

    wavesurfer.on('play', function() {
        $("#fplay").removeClass('fa-play');
        $("#fplay").addClass('fa-pause');
        $("#fplay").attr('data-action','pause');
    });

    wavesurfer.on('pause', function() {
        $("#fplay").removeClass('fa-pause');
        $("#fplay").addClass('fa-play');
        $("#fplay").attr('data-action','play-region');
    });

    wavesurfer.responsive=true;

    $('#zminus').on('mousedown', function() {
       evid = setTimeout( "decZoom();", 100 );
    });

    $('#zminus').on('mouseup', function() {
       if ( typeof evid != "undefined" ) clearTimeout(evid);
       wavesurfer.zoom(wzoom);
       moveSpeech();
    });

    $('#zminus').on('mouseout', function() {
       if ( typeof evid != "undefined" ) clearTimeout(evid);
       wavesurfer.zoom(wzoom);
       moveSpeech();
    });

    $('#zplus').on('mousedown', function() {
       evid = setTimeout( "incZoom();", 100 );
    });

    $('#zplus').on('mouseup', function() {
       if ( typeof evid != "undefined" ) clearTimeout(evid);
       wavesurfer.zoom(wzoom);
       moveSpeech();
    });

    $('#zplus').on('mouseout', function() {
       if ( typeof evid != "undefined" ) clearTimeout(evid);
       wavesurfer.zoom(wzoom);
       moveSpeech();
    });

    $('#sminus').on('mousedown', function() {
       evid = setTimeout( "decSpeed();", 100 );
    });

    $('#sminus').on('mouseup', function() {
       if ( typeof svid != "undefined" ) clearTimeout(svid);
       wavesurfer.setPlaybackRate(wspeed);
    });

    $('#sminus').on('mouseout', function() {
       if ( typeof svid != "undefined" ) clearTimeout(svid);
       wavesurfer.setPlaybackRate(wspeed);
    });

    $('#splus').on('mousedown', function() {
       evid = setTimeout( "incSpeed();", 100 );
    });

    $('#splus').on('mouseup', function() {
       if ( typeof svid != "undefined" ) clearTimeout(svid);
       wavesurfer.setPlaybackRate(wspeed);
    });

    $('#splus').on('mouseout', function() {
       if ( typeof svid != "undefined" ) clearTimeout(svid);
       wavesurfer.setPlaybackRate(wspeed);
    });

    $('#sfull').on('click', function() {
       var ih = document.querySelector('#isubtitle').innerHTML;
       document.querySelector('#content-fs').innerHTML = ih;
       $("#modal-sfull").modal("show");
    });

    $('#help').on('click', function() {
        $("#modal-help").modal("show");
    });

    $("#modal-book").on("hidden.bs.modal", function() {
        console.log( "modal book hide" );
        // stop playing in a loop
        if ( currentRegion != null ) {
           console.log( "hide books : stopping loop");
           currentRegion.setLoop(false);
           currentRegion.un("out");
           currentRegion = null;
        }
        wavesurfer.play();
    });

    $("#modal-book").on("shown.bs.modal", function() {
        var jqxhr = $.post( {
           url: '../../get-audiobooks.php',
        }, function(data) {
           console.log( "got audiobooks : " + JSON.stringify(data));
           var books = JSON.parse(data);
           $('#oldbook option').each(function() {
              $(this).remove();
           });
           $('#oldbook').append($('<option>', {
              value: 'none',
              text: 'none'
           }));
           $('#oldbook').val('none').trigger('chosen:updated'); //refreshes the drop down list
           $.each(books, function (id, book) {
              $('#oldbook').append($('<option>', {
                 value: decodeURI(book),
                 text: decodeURI(book)
              }));
           });
        })
        .fail(function(error) {
           console.log( "getting audiobooks failed : " + JSON.stringify(error));
        });
    });

    addbook.onsubmit = function(e) {
        var regionId = bRegionId;
        var order = -1;
        var counter = 0;
        e.preventDefault();
        Object.keys(wavesurfer.regions.list).map(function(id) {
           ++counter;
           if ( regionId === id ) order=counter;
        });
        var oldbook = $('#oldbook').val();
        var newbook = $('#newbook').val();
        if ( newbook === '' && oldbook === 'none' )
        {
           alertify.alert( "Please, choose an existing book or create a new one!" );
           return;
        }
        $('.lds-spinner').css('display','block');
        var jqxhr = $.post( {
           url: '../../add-to-book.php',
           data: {
             oldbook: fullEncode(oldbook),
             newbook: fullEncode(newbook),
             order: order,
             user: user,
             source: fullEncode(soundfile),
           },
           dataType: 'application/json'
        }, function() {
           console.log( "add to book succeeded" );
           $('.lds-spinner').css('display','none');
           $("#modal-book").modal("hide");
        })
        .fail(function(error) {
           $('.lds-spinner').css('display','none');
           $("#modal-book").modal("hide");
           if ( error.status === 200 ) {
              console.log( "add to book success");
           } else {
              console.log( "adding to book failed : " + JSON.stringify(error));
              alertify.alert( "Adding to book failed : " + error.statusText );
           }
        });
    };

    $('#audiobook').on('click', function() {
       var form = document.forms.edit;
       bRegionId = form.dataset.region;
       // console.log( "audio book click : pause" );
       // wavesurfer.pause();
       $("#modal-form").off("hidden.bs.modal");
       $("#modal-form").modal("hide");
       $("#modal-book").modal("show");
    });

    $('#help').on('click', function() {
        $("#modal-help").modal("show");
    });

    // $('#subtitle').css('display','block');

    tinymce.init({
      selector: '#note',
      plugins: 'advlist autolink lists link image charmap hr pagebreak searchreplace wordcount help insertdatetime emoticons charmap ',
      branding: false,
      elementpath: false,
      toolbar: false,
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

    $('.lds-spinner').css('display','none');
});

/**
 * Save annotations to the server.
 */
function saveRegions() {
    var counter=0;
    var navigation="<center><b>Navigate</b></center><br/><br/>";
    $("#linear-notes").html('');
    localStorage.regions = JSON.stringify(
        Object.keys(wavesurfer.regions.list).map(function(id) {
            var region = wavesurfer.regions.list[id];
            var burl = document.location.href;
            if ( burl.indexOf('?') >= 0 )
            {
               burl = burl.substr( 0, burl.indexOf('?') );
            } 
            counter++;
            // console.log(region.data.note);
            var leyenda = "...";
            if ( typeof region.data.note != "undefined" )
               leyenda = region.data.note.replaceAll("<div>","").replaceAll("</div>","").substring(0,20)+"...";
            navigation+="<a href='javascript: playAt("+region.start+")'>"+counter+" - "+leyenda+"<br/></a>";
            var blank = "<br/><br/><div class='linear-bar' id='bar-"+region.id+"'>";
            $("#linear-notes").append(blank);
            var range = "<p>"+toHHMMSS(region.start)+" - "+toHHMMSS(region.end)+" (" + Math.round(region.end-region.start) + " s) : </p>";
            $("#bar-"+region.id).append(range);
            var rplay = "<i class='fa fa-play fa-1x linear-play' id='r"+region.id+"' onclick='playRegion(\""+region.id+"\")'></i>";
            $("#bar-"+region.id).append(rplay);
            var rbook = "<i class='fa fa-book fa-1x linear-book' id='b"+region.id+"' onclick='addToBook(\""+region.id+"\")'></i>";
            $("#bar-"+region.id).append(rbook);
            var wnote = '';
            if ( region.data != undefined && region.data.note != undefined ) {
               wnote = region.data.note.replaceAll("<div>","").replaceAll("</div>","");
            }
            var ncontent = "<textarea id='"+region.id+"' class='note-textarea'>"+wnote+"</textarea>";
            $("#linear-notes").append(ncontent);
            $("#"+region.id).on( 'change', function(evt) {
                var id = $(this).attr('id');
                wavesurfer.regions.list[id].data.note=evt.target.value;
                saveRegions();
            });
            return {
                order: counter,
                start: region.start,
                end: region.end,
                baseurl: fullEncode(burl),
                source: fullEncode(soundfile),
                title: fullEncode(document.querySelector('#title').innerHTML.toString().substr(8)),
                url: fullEncode(burl+'?start='+region.start),
                attributes: region.attributes,
                data: region.data
            };
        })
    );
    // console.log( "saving : " + counter + " annotations" );
    $("#notes").html(navigation);

    anotes = JSON.parse(localStorage.regions);
    var jqxhr = $.post( {
      url: 'save-annotations.php',
      data: {
	'json': JSON.stringify(anotes.sort(sorta))
      },
      dataType: 'application/json'
    }, function() {
       // console.log( "Saving annotations succeeded" );
    })
    .fail(function(error) {
       if ( error.status === 200 ) {
          // console.log( "saving annotations success");
       } else {
          console.log( "Saving annotations failed : status : " + error.status + " message : " + JSON.stringify(error));
          alertify.alert(  "Saving annotations failed : status : " + error.status + " message : " + JSON.stringify(error) );
       }
    });
}

/**
 * Load regions from ajax request.
 */
function loadRegions(regions) {
    wavesurfer.un('region-updated');
    wavesurfer.un('region-removed');
    wavesurfer.clearRegions();
    regions.forEach(function(region) {
        region.color = randomColor(0.1);
        wavesurfer.addRegion(region);
    });
    wavesurfer.on('region-updated', saveRegions);
    wavesurfer.on('region-removed', saveRegions);
    saveRegions();
}

/**
 * Random RGBA color.
 */
function randomColor(alpha) {
    return (
        'rgba(' +
        [
            ~~(Math.random() * 255),
            ~~(Math.random() * 255),
            ~~(Math.random() * 255),
            alpha || 1
        ] +
        ')'
    );
}

/**
 * When a region is clicked, pass the click to the waveform.
 */
function propagateClick(region, e) {
    var clickEvent = new MouseEvent("click", {
        bubbles: true,
        cancelable: true,
        clientX: e.clientX,
        clientY: e.clientY
    });
    document.querySelector('wave').dispatchEvent(clickEvent);
}

/**
 * Edit annotation for a region.
 */
function editAnnotation(region, e) {
    e.stopPropagation();
    console.log( "edit : play region" );
    currentRegion=region;
    // currentRegion.playLoop();
    currentRegion.setLoop(true);
    var form = document.forms.edit;
    form.dataset.region = currentRegion.id;
    tinyMCE.activeEditor.setContent(currentRegion.data.note || '');
    showNote(currentRegion);
    form.onsubmit = function(e) {
        e.preventDefault();
        // console.log( 'saving : ' + form.elements.note.value);
        var newnote = form.elements.note.value;
        newnote = newnote.replaceAll("<p", "<div" );
        newnote = newnote.replaceAll("</p>", "</div>" );
        currentRegion.update({
            start: region.start,
            end: region.end,
            data: {
                note: newnote,
                user: user, 
                color: ucolor
            }
        });
        $("#modal-form").modal("hide");
        showNote(currentRegion);
    };
    form.onreset = function() {
        $("#modal-form").modal("hide");
    };
    $("#modal-form").modal("show");
    $("#modal-form").on("hidden.bs.modal", function() {
        if ( currentRegion ) {
           console.log( "hide annotations : stopping loop");
           currentRegion.setLoop(false);
           currentRegion.un("out");
           currentRegion = null;
        }
        // wavesurfer.play();
    });
}

/**
 * Display annotation.
 */
function showNote(region) {
    // console.log( "show note");
    if (!showNote.el || !showNote.uel) {
        showNote.uel = document.querySelector('#subtitle');
        showNote.el = document.querySelector('#isubtitle');
        showNote.speaker = document.querySelector('#ispeaker');
        showNote.sfull = document.querySelector('#sfull');
    }
    var snote = '';
    if ( region.data && region.data.note ) {
       var lines = region.data.note.split("\n");
       lines.forEach( function( line, index ) {
          // console.log(line.substring(2,3) + " " + line);
          if ( line.substring(2,3) ==  ":" ) {
             if ( language === '--' || language === line.substring(0,2) ) {
                snote += line.substring(3);
             }
          } else {
             snote += line;
          }
          // check if it's html or normal text
          if ( !strstr( line, "<" ) && !strstr( line, ">" ) ) {
             snote += "<br/>";
          }
       });
    }
    showNote.el.innerHTML = snote;
    showNote.speaker.innerHTML = region.data.user || '';
    // showNote.speaker.style.background = region.data.color || '';
    // showNote.sfull.style.background = region.data.color || '';
    // showNote.el.innerHTML = showNote.el.innerHTML.replace(/\n/g,'<br>');
    if ( showNote.el.innerHTML != '' )
       showNote.uel.style.display = 'block';
    else
       showNote.uel.style.display = 'none';
}

/**
 * Delete annotation.
 */
function deleteNote(region) {
    // console.log( "delete note");
    if (!deleteNote.el || !deleteNote.uel) {
       deleteNote.el = document.querySelector('#isubtitle');
       deleteNote.uel = document.querySelector('#subtitle');
       deleteNote.speaker = document.querySelector('#ispeaker');
    }
    deleteNote.uel.style.display = 'none';
    if ( !region.data.note ) return;
    if ( deleteNote.el.innerHTML === region.data.note.replace(" />",">") )
       deleteNote.el.innerHTML = '';
    else
       deleteNote.uel.style.display = 'block';
}

/**
 * Bind controls.
 */
window.GLOBAL_ACTIONS['delete-region'] = function() {
    var form = document.forms.edit;
    var regionId = form.dataset.region;
    var order = -1;
    var counter = 0;

    Object.keys(wavesurfer.regions.list).map(function(id) {
        ++counter;
        if ( regionId === id ) order=counter;
    });

    console.log( 'deleting region : ' + counter );

    if (regionId) {
        deleteNote(wavesurfer.regions.list[regionId]);
        wavesurfer.regions.list[regionId].remove();
        saveRegions();
        form.reset();

        var jqxhr = $.post( {
          url: '../../delete-annotation.php',
          data: {
            order: order ,
            source: fullEncode(soundfile)
          },
          dataType: 'application/json'
        }, function() {
          console.log( "deleting annotation succeeded" );
        })
        .fail(function(error) {
          if ( error.status === 200 ) {
             console.log( "deleting annotation success");
          } else {
             console.log( "deleting annotation failed : " + JSON.stringify(error));
          }
        });
    }
};

var sorta = function( notea, noteb ) {
    if ( notea["start"] < noteb["start"] ) {
      return -1;
    } else if ( notea["start"] > noteb["start"] ) {
      return 1;
    } else {
      return 0;
    }
}

var playAt = function(position) {
    wavesurfer.seekTo( position/wavesurfer.getDuration() );
    wavesurfer.play();
}

window.GLOBAL_ACTIONS['export'] = function() {

    anotes = JSON.parse(localStorage.regions);
    anotes = anotes.sort(sorta);
    if ( anotes.length === 0 )
    {
       alertify.alert( "There is nothing to export!" );
       return;
    }
    var subtitles = '';
    var counter = 1;
    anotes.forEach( function(note, index) {
       subtitles += counter+'\n';
       counter++;
       subtitles += toHHMMSS(note.start)+' --> '+toHHMMSS(note.end)+'\n';
       var lines = note.data.note.split("\n");
       lines.forEach( function( line, index ) {
           subtitles += $('<div>').html(line).text()+'\n';
       });
       subtitles += '\n';
    });

    // force subtitles download
    var element = document.createElement('a');
    var filename = $("#title").html().toString().substring(8)+'-free.srt';
    element.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(subtitles));
    element.setAttribute('download', filename);
    element.style.display = 'none';
    document.body.appendChild(element);
    element.click();
    document.body.removeChild(element);
};

var playRegion = function(regid) {
    var region = wavesurfer.regions.list[regid];

    console.log( "play region" );
    if ( !wavesurfer.isPlaying() )
    {
       region.setLoop(true);
       region.playLoop();
       region.setLoop(false);
       $("#r"+regid).removeClass("fa-play");
       $("#r"+regid).addClass("fa-pause");
    } else {
       wavesurfer.pause();
       $("#r"+regid).removeClass("fa-pause");
       $("#r"+regid).addClass("fa-play");
    }
}
