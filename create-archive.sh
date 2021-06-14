#!/bin/bash

#set -x

strstr() {
  [ "${1#*$2*}" = "$1" ] && echo "0"
  echo "1"
}

if [ $# -ne 1 ]; then
   echo "ERR: Usage: $0 <file_url>"
   exit 1
fi

tmpfile=`tempfile`
echo -n "Downloading to $tmpfile ..." 1>&2
wget -O $tmpfile --no-check-certificate "$1" 2>/dev/null
if [ $? -ne 0 ]
then
   /bin/rm $tmpfile
   echo '' 1>&2
   echo "ERR: Could not download file : $1"
   exit -1
fi
echo "done." 1>&2

mimetype=`/usr/bin/mimetype --output-format %m $tmpfile`
echo $mimetype 1>&2
echo  ${mimetype#*audio*} 1>&2
if [ ${mimetype#*audio*} = $mimetype ]
then
  /bin/rm $tmpfile
  echo "ERR: This url is not an audio archive, please !!!"
  exit -1
fi

artist=`/usr/bin/ffprobe $tmpfile 2>&1 | grep -iw -m1 artist | cut -f2 -d':' | sed 's/ //g' -`
sartist=`/usr/bin/ffprobe $tmpfile 2>&1 | grep -iw -m1 artist | cut -f2 -d':'`
echo "artist : $artist" 1>&2
date=`/usr/bin/ffprobe $tmpfile 2>&1 | grep -iw -m1 date | cut -f2 -d':' | xargs | sed 's/ //g' - | sed 's/\//-/g' -`
echo "date : $date" 1>&2
title=`/usr/bin/ffprobe $tmpfile 2>&1 | grep -iw -m1 title | cut -f2-3 -d':' | sed 's/^ //g'`
dtitle=`/usr/bin/ffprobe $tmpfile 2>&1 | grep -iw -m1 title | cut -f2 -d':' | awk '{print $1 " " $2 " " $3 " " $4 " " $5}' | sed 's/ /-/g' | sed 's/--/-/g'`
echo "title : $title" 1>&2
collection=`/usr/bin/ffprobe $tmpfile 2>&1 | grep -iw -m1 album | grep -v replaygain | cut -f2 -d':' | awk '{print $1 $2 $3}'`
echo "collection : $collection" 1>&2

if [ "x"$dtitle == "x" ]
then
   title='Unknown'
   dtitle='Unknown'
fi

if [ "x"$date == "x" ]
then
   date='xxxx'
   sdate='Unknown'
else
   sdate=$date
fi

if [ "x"$artist != "x" ]
then
   dirname=$dtitle"-"$date
else
   sartist='Unknown'
   dirname=`echo $1 | rev | cut -d'/' -f 1 | rev | sed 's/.mp3//g' - | sed 's/.ogg//g' - | sed 's/.wav//g' - | sed 's/.webm//g' - | sed 's/.aiff//g' -`
   nbfiles=`ls -1d archives/* | wc -l`
   nbfiles=$((nbfiles+1))
   dirname="archive-$nbfiles"
fi


echo "New directory : $dirname"
if [ -d "archives/$dirname" ]
then
   echo "Directory exists!! : $dirname : redirecting..." 1>&2
   echo "archives/$dirname/index.php√$sartist√$title√$collection√$sdate"
   exit 0
   notok=1
   num=0
   while [ $notok -eq 1 ]
   do
     num=$((num+1))
     if [ -d $dirname-$num ]
     then
       notok=1
     else
       notok=0
       dirname=$dirname-$num
     fi
   done
fi

cp -rf archives/template "archives/$dirname"
chmod -R 777 "archives/$dirname"
sed -i "s#__file_url__#$1#g" "archives/$dirname/app.js"
sed -i "s#__file_url__#$1#g" "archives/$dirname/appl.js"
sed -i "s#__file_url__#$1#g" "archives/$dirname/index.php"

echo "archives/$dirname/index.php√$sartist√$title√$collection√$sdate"

/bin/rm $tmpfile
