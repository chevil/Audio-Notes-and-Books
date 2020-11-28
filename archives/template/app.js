/**
 * Create a WaveSurfer instance.
 */
var wavesurfer;
var wzoom=19;
var evid;
var currentRegion;
var soundfile = '__file_url__';

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
    wzoom=Math.max(wzoom-1,0);
    if ( wzoom < 2 )
       $('#zvalue').html("x"+(wzoom+1));
    else
       $('#zvalue').html("x"+(wzoom+2));
    evid = setTimeout( decZoom, 50 );
}

var incZoom = function() {
    wzoom=Math.min(wzoom+1,198);
    if ( wzoom < 2 )
       $('#zvalue').html("x"+(wzoom+1));
    else
       $('#zvalue').html("x"+(wzoom+2));
    evid = setTimeout( incZoom, 50 );
}

var moveSpeech = function() {
    var curx, curxx;
    if ( wzoom > 0 ) {
       curx=wavesurfer.getCurrentTime()*wzoom-1;
       curxx=(wavesurfer.getDuration()-wavesurfer.getCurrentTime())*wzoom+1;
    }
    else
    {
       curx=wavesurfer.getCurrentTime()*$("#waveform").width()/wavesurfer.getDuration()-1;
       curxx=(wavesurfer.getDuration()-wavesurfer.getCurrentTime())*$("#waveform").width()/wavesurfer.getDuration()+1;
    }
    if ( curxx <= $("#waveform").width()/2 ) {
       $(".speech-cursor").css('left', ($("#waveform").width()-curxx)+'px' );
       // $(".speech").css('margin-left', ($("#waveform").width()-curxx-40)+'px' );
    } else {
       if ( curx > $("#waveform").width()/2 ) curx = $("#waveform").width()/2;
       $(".speech-cursor").css('left', curx+'px' );
       $(".speech").css('margin-left', (curx-40)+'px' );
    }
}

/**
 * Init & load.
 */
document.addEventListener('DOMContentLoaded', function() {

    // Init wavesurfer
    wavesurfer = WaveSurfer.create({
        container: '#waveform',
        height: 200,
        pixelRatio: 1,
        scrollParent: true,
        normalize: true,
        minimap: true,
        barRadius: 10,
        forceDecode: true,
        progressColor: '#60f373',
        waveColor: '#67549f',
        backend: 'MediaElement',
        plugins: [
            WaveSurfer.regions.create(),
            WaveSurfer.minimap.create({
                container: '#wave-minimap',
                height: 50,
                showRegions: true,
                showOverview: true,
                waveColor: '#987f45',
                progressColor: '#899f67',
                cursorColor: '#60f373'
            }),
            WaveSurfer.timeline.create({
                container: '#wave-timeline'
            })
        ]
    });

    wavesurfer.util
        .fetchFile({
            responseType: 'json',
            url: 'rashomon.json'
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
                    wavesurfer.zoom(wzoom);
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
        console.log( 'got region play : ' + region.id );
        region.on('out', function() {
            console.log( 'restart playing');
            wavesurfer.play(region.start);
        });
    });

    wavesurfer.on('audioprocess', function() {
        wavesurfer.zoom(wzoom);
        moveSpeech();
    });

    /* Toggle play/pause buttons. */
    var playButton = document.querySelector('#play');
    var fplayButton = document.querySelector('#fplay');

    wavesurfer.on('play', function() {
        playButton.src = '../../img/pause.png';
        fplayButton.src = '../../img/pause.png';
    });

    wavesurfer.on('pause', function() {
        playButton.src = '../../img/play.png';
        fplayButton.src = '../../img/play.png';
    });

    wavesurfer.responsive=true;

    $('#minus').on('mousedown', function() {
       evid = setTimeout( decZoom, 50 );
    });

    $('#minus').on('mouseup', function() {
       clearTimeout(evid);
       wavesurfer.zoom(wzoom);
       moveSpeech();
    });

    $('#plus').on('mousedown', function() {
       evid = setTimeout( incZoom, 50 );
    });

    $('#plus').on('mouseup', function() {
       clearTimeout(evid);
       wavesurfer.zoom(wzoom);
       moveSpeech();
    });

    $('#sfull').on('click', function() {
       var ih = document.querySelector('#isubtitle').innerHTML;
       document.querySelector('#content-fs').innerHTML = ih;
       $("#modal-sfull").modal("show");
    });

    $('#audiobook').on('click', function() {
       var form = document.forms.edit;
       // console.log( "audio book click : pause" );
       // wavesurfer.pause();
       $("#modal-form").off("hidden.bs.modal");
       $("#modal-form").modal("hide");
       $("#modal-book").modal("show");
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
           var form = document.forms.edit;
           var regionId = form.dataset.region;
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
                oldbook: encodeURIComponent(oldbook),
                newbook: encodeURIComponent(newbook),
                order: order,
                user: user,
                source: encodeURIComponent(soundfile),
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
    });

    $('#help').on('click', function() {
        $("#modal-help").modal("show");
    });

    $('#zvalue').html("x"+(wzoom+1));

    $('#subtitle').css('opacity',0.0);

    tinymce.init({
      selector: '#note',
      plugins: 'advlist autolink lists link image charmap hr pagebreak searchreplace wordcount help insertdatetime emoticons charmap ',
      branding: false,
      elementpath: false,
      toolbar: false,
      statusbar: false,
      placeholder: 'Type here...',
      contextmenu: 'link image',
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
    localStorage.regions = JSON.stringify(
        Object.keys(wavesurfer.regions.list).map(function(id) {
            var region = wavesurfer.regions.list[id];
            var burl = document.location.href;
            if ( burl.indexOf('?') >= 0 )
            {
               burl = burl.substr( 0, burl.indexOf('?') );
            } 
            counter++;
            return {
                order: counter,
                start: region.start,
                end: region.end,
                baseurl: encodeURIComponent(burl),
                source: encodeURIComponent(soundfile),
                title: encodeURIComponent(document.querySelector('#title').innerHTML.toString().substr(8)),
                url: encodeURIComponent(burl+'?start='+region.start),
                attributes: region.attributes,
                data: region.data
            };
        })
    );
    // console.log( "saving : " + counter + " annotations" );

    var jqxhr = $.post( {
      url: '../../save-annotations.php',
      data: {
	'json': localStorage.regions
      },
      dataType: 'application/json'
    }, function() {
       console.log( "saving annotations succeeded" );
    })
    .fail(function(error) {
       if ( error.status === 200 ) {
          // console.log( "saving annotations success");
       } else {
          console.log( "saving annotations failed : " + JSON.stringify(error));
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
}

/**
 * Extract regions separated by silence.
 */
function extractRegions(peaks, duration) {
    // Silence params
    var minValue = 0.0015;
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

/**
 * Edit annotation for a region.
 */
function editAnnotation(region, e) {
    e.stopPropagation();
    console.log( "edit : play region" );
    currentRegion=region;
    currentRegion.playLoop();
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
        wavesurfer.play();
    });
}

/**
 * Display annotation.
 */
function showNote(region) {
    console.log( "show note");
    if (!showNote.el || !showNote.uel) {
        showNote.uel = document.querySelector('#subtitle');
        showNote.el = document.querySelector('#isubtitle');
        showNote.speaker = document.querySelector('#ispeaker');
        showNote.sfull = document.querySelector('#sfull');
    }
    showNote.el.innerHTML = region.data.note || '';
    showNote.speaker.innerHTML = region.data.user || '';
    showNote.speaker.style.background = region.data.color || '';
    showNote.sfull.style.background = region.data.color || '';
    // showNote.el.innerHTML = showNote.el.innerHTML.replace(/\n/g,'<br>');
    if ( showNote.el.innerHTML != '' )
       showNote.uel.style.opacity = 1.0;
    else
       showNote.uel.style.opacity = 0.0;
}

/**
 * Delete annotation.
 */
function deleteNote(region) {
    console.log( "delete note");
    if (!deleteNote.el || !deleteNote.uel) {
       deleteNote.el = document.querySelector('#isubtitle');
       deleteNote.uel = document.querySelector('#subtitle');
       deleteNote.speaker = document.querySelector('#ispeaker');
    }
    deleteNote.uel.style.opacity = 0.0;
    if ( !region.data.note ) return;
    if ( deleteNote.el.innerHTML === region.data.note.replace(" />",">") )
       deleteNote.el.innerHTML = '';
    else
       deleteNote.uel.style.opacity = 1.0;
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
            source: encodeURIComponent(soundfile)
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

window.GLOBAL_ACTIONS['export'] = function() {
    anotes = JSON.parse(localStorage.regions);
    window.open(
        'data:application/json;charset=utf-8,' +
            JSON.stringify(anotes.sort(sorta))
    );
};
