<?php
include("config.php");
include("functions.php");
require("html2text.php");

session_start();

if ( !isset($_SESSION['schtroumpf']) || !isset($_SESSION['papa']) )
{
    die("ERR: Unauthorized access.");
}

if ( !isset($_POST['title']) )
{
    die("ERR: The title of the book must be set.");
}
else
{
    $title=$_POST['title'];
}

$dtitle = preg_replace('/ /','-',urldecode($title));
$dtitle = preg_replace("/[^A-Za-z0-9]/", '', $dtitle);
if ( ( $result=exec("rm -rf 'audiobooks/".$dtitle."'; echo $?") ) != 0 )
{
   error_log("ERR: Could not erase directory.");
}

if ( ( $result=exec("mkdir 'audiobooks/".$dtitle."'; echo $?") ) != 0 )
{
   die("ERR: Could not create directory.");
}

if (!$ncc_header=file_get_contents("audiobooks/template/ncc-header.html"))
{
   die("ERR: Could not generate audio book.");
}

if (!$ncc_footer=file_get_contents("audiobooks/template/ncc-footer.html"))
{
   die("ERR: Could not generate audio book.");
}

if (!$excerpt_html=file_get_contents("audiobooks/template/excerpt.html"))
{
   die("ERR: Could not generate audio book.");
}

if (!$excerpt_smil=file_get_contents("audiobooks/template/excerpt.smil"))
{
   die("ERR: Could not generate audio book.");
}

$resultdb=db_query("SELECT audiobook.title, data, audiobook.norder, source, excerpt, audiobook.aoid FROM audiobook, annotation WHERE audiobook.aoid=annotation.id AND audiobook.title='".addslashes($title)."' ORDER BY audiobook.norder" );
$nbexcerpts=mysqli_num_rows($resultdb);
if ( $nbexcerpts == 0 )
{
   die("ERR: This book is empty.");
}
else
{
   $ttime=0;
   $counter=1;
   $a_excerpt_html='';
   while ($row=mysqli_fetch_row($resultdb))
   {
     $excerpt_id="anno_".$row[5];
     $excerpt_title=convert_html_to_text($row[1]);
     $excerpt_title=ellipse($excerpt_title,40);
     $excerpt_source=basename(urldecode($row[3]));
     // copy excerpt
     if ( ( $result=exec("cp '".$row[4]."' 'audiobooks/".$dtitle."'; echo $?") ) != 0 )
     {
       die("ERR: Could not copy samples.");
     }

     // get duration ( decimal in seconds )
     if ( ( $duration=exec("ffprobe -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 '".$row[4]."'") ) < 0 )  
     {
        die("ERR: Could not get sample duration.");
     }

     // error_log( "sample : ".$duration." total: ".$ttime );

     // generate smil file
     $e_excerpt_smil = $excerpt_smil;
     $e_excerpt_smil = preg_replace( "/__excerpt_duration__/", $duration, $e_excerpt_smil );
     $e_excerpt_smil = preg_replace( "/__elapsed_time__/", $ttime, $e_excerpt_smil );
     $e_excerpt_smil = preg_replace( "/__excerpt_id__/", $excerpt_id, $e_excerpt_smil );
     $e_excerpt_smil = preg_replace( "/__excerpt_title__/", $excerpt_title, $e_excerpt_smil );
     $e_excerpt_smil = preg_replace( "/__excerpt_source__/", $excerpt_source, $e_excerpt_smil );
     if (!$result=file_put_contents("audiobooks/".$dtitle."/".$excerpt_id.".smil", $e_excerpt_smil))
     {
        die("ERR: Could not create sample file.");
     }

     $e_excerpt_html = $excerpt_html;
     $e_excerpt_html = preg_replace( "/__excerpt_id__/", $excerpt_id, $e_excerpt_html );
     $e_excerpt_html = preg_replace( "/__excerpt_title__/", $excerpt_title, $e_excerpt_html );
     $e_excerpt_html = preg_replace( "/__excerpt_index__/", $counter, $e_excerpt_html );
     $a_excerpt_html .= $e_excerpt_html;

     $ttime += $duration;
     $counter++;
   }

   // set header
   $date = date("Y-m-d");
   $hour = round( $ttime/3600 );
   $min = round( ($ttime-$hour*3600) / 60 );
   $sec = round( $ttime-$hour*3600-$min*60 );
   if ( ( $esize=exec("du -b 'audiobooks/".$dtitle."' | cut -f1") ) < 0 )  
   {
       die("ERR: Could not get book size.");
   }
   $ncc_header = preg_replace( "/__book_title__/", urldecode($title), $ncc_header );
   $ncc_header = preg_replace( "/__book_creator__/", $_SESSION['schtroumpf'], $ncc_header );
   $ncc_header = preg_replace( "/__book_date__/", $date, $ncc_header );
   $ncc_header = preg_replace( "/__book_keywords__/", '', $ncc_header );
   $ncc_header = preg_replace( "/__book_nb_excerpts__/", ($counter-1), $ncc_header );
   $ncc_header = preg_replace( "/__book_duration__/", sprintf("%02d:%02d:%02d", $hour, $min, $sec ), $ncc_header );
   $ncc_header = preg_replace( "/__book_size__/", $esize, $ncc_header );
   if (!$result=file_put_contents("audiobooks/".$dtitle."/ncc.html", $ncc_header))
   {
      die("ERR: Could not create book description.");
   }

   if (!$result=file_put_contents("audiobooks/".$dtitle."/ncc.html", $a_excerpt_html, FILE_APPEND))
   {
      die("ERR: Could not create book description.");
   }

   if (!$result=file_put_contents("audiobooks/".$dtitle."/ncc.html", $ncc_footer, FILE_APPEND))
   {
      die("ERR: Could not create book description.");
   }

   if ( ( $result=exec("cd audiobooks; zip -r ".$dtitle.".zip ".$dtitle."; echo $?") ) < 0 )  
   {
       die("ERR: Could not compress book.");
   }

   print "audiobooks/".$dtitle.".zip";
}

?>
