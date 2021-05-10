/**
 * Create a WaveSurfer instance.
 */
var wavesurfer;
var wavewidth=940;
var nbPeaks=32768;
var wzoom=10;
var wspeed=1.0;
var gotPeaks=false;
var peaks;
var regions;
var evid;
var sevid;
var currentRegion;
var soundfile = '__title__';

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

var toMMSS = function(duration)
{
   // console.log(duration);
   var min = Math.floor(duration/60);
   var duration = duration - min*60;
   var sduration = duration.toLocaleString("en-US", {
       minimumFractionDigits: 2,
       maximumFractionDigits: 2
   });
   return min+":"+sduration;
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

                if (data) console.log( "got annotations : " + data.length );
                if ( data.length > 0 )
                   regions = data;
                else
                   regions = extractRegions( peaks, wavesurfer.getDuration() );

                regions.forEach( function( region) {
                   wregion = wavesurfer.regions.add({
                       start: region.start,
                       end: region.end,
                       data: {
                         note: region.data.note,
                         user: user,
                         color: ucolor
                       }
                   });
                   // console.log( wregion.id );
                   var range = "<p>"+toMMSS(region.start)+" - "+toMMSS(region.end)+" : </p>";
                   $("#linear-notes").append(range);
                   var rplay = "<i class='fa fa-play fa-1x linear-play' id='r"+wregion.id+"' onclick='playRegion(\""+wregion.id+"\")'></i>";
                   $("#linear-notes").append(rplay);
                   var ncontent = "<textarea id='"+wregion.id+"' class='note-textarea'>"+wregion.data.note+"</textarea>";
                   $("#linear-notes").append(ncontent);
                   $("#"+wregion.id).on( 'change', function(evt) {
                        var id = $(this).attr('id');
                        wavesurfer.regions.list[id].data.note=evt.target.value;
                        saveRegions();
                   });
                });
                saveRegions();
            }).fail(function(error) {
                console.log( "couldn't load aanotaions : " + JSON.stringify(error) );
            });
        }); // ready

        wavesurfer.on('region-click', propagateClick);
    
        wavesurfer.on('audioprocess', function() {
            $(".play-time").html( toMMSS(wavesurfer.getCurrentTime()) + " / " + toMMSS(wavesurfer.getDuration()) );
        });
    
        wavesurfer.on('pause', function() {
            $(".linear-play").removeClass('fa-pause');
            $(".linear-play").addClass('fa-play');
        });
    
        wavesurfer.responsive=true;

    }).fail(function(error) {
        console.log( "couldn't load peaks : " + JSON.stringify(error) );
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
    // console.log( "saving : " + (counter-4096) + " annotations (linear)" );

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
    var minValue = 0.015;
    var minSeconds = 0.25;

    var length = peaks.length;
    var coef = duration / length;
    var minLen = minSeconds / coef;

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
    return fRegions.map(function(reg) {
        return {
            start: Math.round(reg.start * coef * 10) / 10,
            end: Math.round(reg.end * coef * 10) / 10
        };
    });
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

var playAt = function(position) {
    wavesurfer.seekTo( position/wavesurfer.getDuration() );
    wavesurfer.play();
}