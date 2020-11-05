#!/bin/bash

if [ $# -ne 4 ]; then
   echo "ERR: Usage: $0 <start> <duration> <infile> <outfile>"
   exit 1
fi

tmpfile=`tempfile`
#echo -n "Downloading to $tmpfile..." 1>&2
wget -O $tmpfile --no-check-certificate "$3" 2>/dev/null
if [ $? -ne 0 ]
then
   /bin/rm $tmpfile
   #echo ''
   echo "ERR: Could not download file : $3"
   exit -1
fi
#echo "done." 1>&2

cmd="ffmpeg -y -ss $1 -t $2 -i $tmpfile $4"
echo $cmd 1>&2
exec $cmd
if [ $? -ne 0 ]
then
   /bin/rm $tmpfile
   echo "ERR: Could not create excerpt : $4"
   exit -1
fi

/bin/rm $tmpfile
echo "OK"
