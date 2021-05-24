/**
 * Create a WaveSurfer instance.
 */
var wavesurfer;
var wavewidth=940;
var nbPeaks=32768;
var wzoom=10;
var wspeed=1.0;
var gotPeaks=false;
var languages = '--';
var language = '--';
var peaks;
var regions;
var evid;
var svid;
var currentRegion;
var soundfile = '__file_url__';

var strstr = function (haystack, needle) {
  if (needle.length === 0) return 0;
  if (needle === haystack) return 0;
  for (let i = 0; i <= haystack.length - needle.length; i++) {
    if (needle === haystack.substring(i, i + needle.length)) {
      return i;
    }
  }
  return -1;
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
    wzoom=Math.max(wzoom-1,1);
    $('#zvalue').html("x"+(wzoom));
    evid = setTimeout( "decZoom();", 500 );
}

var incZoom = function() {
    wzoom=Math.min(wzoom+1,198);
    $('#zvalue').html("x"+(wzoom));
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

/**
 * Init & load.
 */
document.addEventListener('DOMContentLoaded', function() {

    progressColor = $("#progresscolor").html();
    waveColor = $("#wavecolor").html();
    mapProgressColor = $("#mapprogresscolor").html();
    mapWaveColor = $("#mapwavecolor").html();

    var jqxhr = $.post( {
        responseType: 'json',
        url: 'peaks.json'
    }, function(data) {
        peaks = data;
        console.log( "got peaks : " + peaks.length );
        if ( peaks.length == 2*nbPeaks )
        {
           // Init wavesurfer
           wavesurfer = WaveSurfer.create({
              container: '#waveform',
              height: 100,
              pixelRatio: 1,
              scrollParent: true,
              normalize: true,
              minimap: true,
              barRadius: 0,
              forceDecode: false,
              fillParent: true,
              mediaControls: true,
              hideScrollbar: true,
              backend: 'MediaElement',
              minPxPerSec: 50,
              waveColor: waveColor,
              progressColor: progressColor,
              plugins: [
                 WaveSurfer.regions.create(),
              ]
           });

           console.log( "loading with peaks : " + soundfile );
           $('.lds-spinner').css('display','block');
           wavesurfer.load(
              soundfile,
              data
           );
           gotPeaks=true;
        } else {
           // Init wavesurfer
           wavesurfer = WaveSurfer.create({
              container: '#waveform',
              height: 100,
              pixelRatio: 1,
              scrollParent: true,
              normalize: true,
              minimap: true,
              barRadius: 0,
              forceDecode: false,
              fillParent: true,
              mediaControls: true,
              hideScrollbar: true,
              backend: 'WebAudio',
              minPxPerSec: 50,
              waveColor: waveColor,
              progressColor: progressColor,
              plugins: [
                 WaveSurfer.regions.create(),
              ]
           });

           console.log( "loading : " + soundfile );
           $('.lds-spinner').css('display','block');
           wavesurfer.load(
              soundfile
           );
           gotPeaks=false;
        }

        /* Regions */
        wavesurfer.on('ready', function() {

            $('.lds-spinner').css('display','none');
            if ( !gotPeaks )
            {
               aPeaks = wavesurfer.backend.getPeaks(nbPeaks);
               console.log( "saving peaks : " + aPeaks.length );
               var jqxhr = $.post( {
                   url: 'save-peaks.php',
                   data: {
	               'json': JSON.stringify(aPeaks)
                   },
                   dataType: 'application/json'
               }, function() {
                   console.log( "saving peaks succeeded" );
                   location.reload();
               }).fail(function(error) {
                   if ( error.status === 200 ) {
                      console.log( "saving peaks success");
                      location.reload();
                   } else {
                      console.log( "saving peaks failed : status : " + error.status + " message : " + JSON.stringify(error));
                   }
               });
               gotPeaks = true;
            }

            var jqxhr = $.post( {
                responseType: 'json',
                url: 'annotations-linear.json'
            }, function(data) {

                if (data) console.log( "got linear annotations : " + data.length );
                if ( data.length > 0 )
                   regions = data;
                else
                   regions = extractRegions( peaks, wavesurfer.getDuration() );

                $("#linear-notes").html('');
                regions.forEach( function( region) {
                   if ( region.data != undefined && region.data.note != undefined ) {
                       var lines = region.data.note.split("\n");
                       lines.forEach( function(line, index) {
                          if ( line.length > 3 && line[2]==':' )
                          {
                             var lang = line.substring(0,2);
                             if ( strstr( languages, lang ) < 0 ) {
                                languages += ","+lang;
                             } 
                          } 
                       });
                   }
                   wregion = wavesurfer.regions.add({
                       start: region.start,
                       end: region.end,
                       resize: false,
                       drag: false,
                       data: {
                         note: ( region.data != undefined ) ? region.data.note : '',
                         user: user,
                         color: ucolor
                       }
                   });
                   // console.log( wregion.id );
                   var blank = "<br/><br/><div class='linear-bar' id='bar-"+wregion.id+"'>";
                   $("#linear-notes").append(blank);
                   var range = "<p>"+toHHMMSS(region.start)+" - "+toHHMMSS(region.end)+" (" + Math.round(region.end-region.start) + " s) : </p>";
                   $("#bar-"+wregion.id).append(range);
                   var rbook = "<i class='fa fa-book fa-1x linear-book' id='b"+wregion.id+"' onclick='addToBook(\""+wregion.id+"\")'></i>";
                   $("#bar-"+wregion.id).append(rbook);
                   var rplay = "<i class='fa fa-play fa-1x linear-play' id='r"+wregion.id+"' onclick='playRegion(\""+wregion.id+"\")'></i>";
                   $("#bar-"+wregion.id).append(rplay);
                   var ncontent = "<textarea id='"+wregion.id+"' class='note-textarea'>"+wregion.data.note+"</textarea>";
                   $("#linear-notes").append(ncontent);
                   $("#"+wregion.id).on( 'change', function(evt) {
                        var id = $(this).attr('id');
                        wavesurfer.regions.list[id].data.note=evt.target.value;
                        saveRegions();
                   });
                });
                saveRegions();
                console.log( "we have : " + languages );
                var header = "<center><div>Language</div></select>";
                $("#subtitle-left").append(header);
                var blank = "<br/>";
                $("#subtitle-left").append(blank);
                var select = "<center><select id='set-language' class='select-language'></select></center>";
                $("#subtitle-left").append(select);
                var options = languages.split(",");
                options.forEach( function( option, index ) {
                    var option = "<option value='"+option+"'>"+option+"</option>";
                    $("#set-language").append(option);
                });
                $("#set-language").change(function() {
                    language = $("#set-language option:selected").val();
                    console.log("language set to : " + language );
                });

                // zoom is proportional to the number of minutes limited to 10
                wzoom = Math.floor( wavesurfer.getDuration() / 60.0 )+1;
                if ( wzoom > 10 ) wzoom = 10;
                $('#zvalue').html("x"+(wzoom));
                setTimeout( "wavesurfer.zoom(wzoom);", 5000 );
                $('#svalue').html(("x"+wspeed).substring(0,4));

            }).fail(function(error) {
                console.log( "couldn't load annotations : " + JSON.stringify(error) );
            });
        }); // ready

        wavesurfer.on('region-click', propagateClick);
        wavesurfer.on('region-in', showNote);
        wavesurfer.on('region-out', deleteNote);
    
        wavesurfer.on('region-updated', function() {
            wavesurfer.zoom(wzoom);
        });

        wavesurfer.on('audioprocess', function() {
            $(".play-time").html( toHHMMSS(wavesurfer.getCurrentTime()) + " / " + toHHMMSS(wavesurfer.getDuration()) );
        });
    
        wavesurfer.on('pause', function() {
            $(".linear-play").removeClass('fa-pause');
            $(".linear-play").addClass('fa-play');
        });
    
        wavesurfer.responsive=true;

    }).fail(function(error) {
        console.log( "couldn't load peaks : " + JSON.stringify(error) );
    });

    $('#zminus').on('mousedown', function() {
       evid = setTimeout( "decZoom();", 100 );
    });

    $('#zminus').on('mouseup', function() {
       if ( typeof evid != "undefined" ) clearTimeout(evid);
       wavesurfer.zoom(wzoom);
    });

    $('#zminus').on('mouseout', function() {
       if ( typeof evid != "undefined" ) clearTimeout(evid);
       wavesurfer.zoom(wzoom);
    });

    $('#zplus').on('mousedown', function() {
       evid = setTimeout( "incZoom();", 100 );
    });

    $('#zplus').on('mouseup', function() {
       if ( typeof evid != "undefined" ) clearTimeout(evid);
       wavesurfer.zoom(wzoom);
    });

    $('#zplus').on('mouseup', function() {
       if ( typeof evid != "undefined" ) clearTimeout(evid);
       wavesurfer.zoom(wzoom);
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

    $('#help').on('click', function() {
        $("#modal-help").modal("show");
    });

    $('.lds-spinner').css('display','none');

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
        }).fail(function(error) {
           console.log( "getting audiobooks failed : " + JSON.stringify(error));
        });
    });

});

/**
 * Save annotations to the server.
 */
function saveRegions() {
    var counter=4096;
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
            var leyenda = "";
            if ( typeof region.data.note != "undefined" )
               leyenda = region.data.note.replaceAll("<div>","").replaceAll("</div>","").substring(0,20)+"...";
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
    console.log( "saving : " + (counter-4096) + " linear annotations" );

    anotes = JSON.parse(localStorage.regions);
    var jqxhr = $.post( {
      url: 'save-annotations-linear.php',
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
    localStorage.regions = regions;
    // saveRegions();
}

/**
 * Extract regions separated by silence.
 */
function extractRegions(peaks, duration) {
    // Silence params
    var minValue = 0.025;
    var minSeconds = 1.00;

    var length = peaks.length;
    var coef = duration / length;
    var minLen = minSeconds / coef;

    console.log( "slice : " + coef );
    console.log( "min length : " + minLen );

    // Gather silence indexes
    var silences = [];
    Array.prototype.forEach.call(peaks, function(val, index) {
        if (Math.abs(val) <= minValue) {
            silences.push(index);
        }
    });

    // Cluster silence values
    var clusters = [];
    silences.forEach(function(val, index) {
        if (clusters.length && val == silences[index - 1] + 1) {
            clusters[clusters.length - 1].push(val);
        } else {
            clusters.push([val]);
        }
    });

    // Filter silence clusters by minimum length
    var fClusters = clusters.filter(function(cluster) {
        return cluster.length >= minLen;
    });

    // Create regions on the edges of silences
    var regions = fClusters.map(function(cluster, index) {
        var next = fClusters[index + 1];
        return {
            start: cluster[cluster.length - 1],
            end: next ? next[0] : length - 1
        };
    });

    // Add an initial region if the audio doesn't start with silence
    var firstCluster = fClusters[0];
    if (firstCluster && firstCluster[0] != 0) {
        regions.unshift({
            start: 0,
            end: firstCluster[firstCluster.length - 1]
        });
    }

    // Filter regions by minimum length
    var fRegions = regions.filter(function(reg) {
        return reg.end - reg.start >= minLen;
    });

    // Return time-based regions
    cRegions =  fRegions.map(function(reg) {
        return {
            start: Math.round(reg.start * coef * 10) / 10,
            end: Math.round(reg.end * coef * 10) / 10
        };
    });

    // regions must be continuous
    rcounter=0;
    maxLen=0;
    maxWhen=0;
    cRegions.forEach(function(creg) {
       if ( rcounter == 0 ) creg.start = 0.0;
       if ( rcounter >= 1 ) {
          cRegions[rcounter-1].end = creg.start-0.1;
          rlen = cRegions[rcounter-1].end - cRegions[rcounter-1].start;
          if ( rlen > maxLen ) { maxLen = rlen; maxWhen = cRegions[rcounter-1].start; }
       }
       rcounter++;
    });

    console.log("region max length : " + maxLen + " at : " + toHHMMSS(maxWhen) );

    return cRegions;
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
 * When a region is cliked, pass the click to the waveform.
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

var sorta = function( notea, noteb ) {
    if ( notea["start"] < noteb["start"] ) {
      return -1;
    } else if ( notea["start"] > noteb["start"] ) {
      return 1;
    } else {
      return 0;
    }
}

var addToBook = function(regid) {
    $("#modal-book").modal("show");
    addbook.onsubmit = function(e) {
       var form = document.forms.edit;
       var regionId = regid;
       var order = -1;
       var counter = 0;
       e.preventDefault();
       Object.keys(wavesurfer.regions.list).map(function(id) {
          ++counter;
          if ( regionId === id ) order=counter+4096;
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
       }).fail(function(error) {
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
}

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

/**
 * Display annotation.
 */
function showNote(region) {
    // console.log( "show note");
    if (!showNote.el) {
        showNote.el = document.querySelector('#subtitle');
    }
    var snote = '';
    var lines = region.data.note.split("\n");
    lines.forEach( function( line, index ) {
        if ( strstr( line, ":" ) > 0 ) {
           if ( language === '--' || language === line.substring(0,2) ) {
              snote += line.substring(3)+"<br/>";
           } 
        } else {
           snote += line+"<br/>";
        }
    });
    showNote.el.innerHTML = snote;
}

/**
 * Delete annotation.
 */
function deleteNote(region) {
    // console.log( "delete note");
    if (!deleteNote.el) {
       deleteNote.el = document.querySelector('#subtitle');
    }
    showNote.el.innerHTML = '';
    if ( !region.data.note ) return;
}


var playAt = function(position) {
    wavesurfer.seekTo( position/wavesurfer.getDuration() );
    wavesurfer.play();
}

var exportSRT = function() {
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
          if ( strstr( line, ":" ) > 0 ) {
             if ( language === '--' || language === line.substring(0,2) ) {
                subtitles += line.substring(3)+"\n";
             } 
          } else {
             subtitles += line+'\n';
          }
       });
       subtitles += '\n';
    });

    // force subtitles download
    var element = document.createElement('a');
    var rlanguage = language;
    if ( language == '--' ) rlanguage='all';
    var filename = $("#title").html().toString().substring(8)+"-"+rlanguage+'.srt';
    element.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(subtitles));
    element.setAttribute('download', filename);
    element.style.display = 'none';
    document.body.appendChild(element);
    element.click();
    document.body.removeChild(element);
};

