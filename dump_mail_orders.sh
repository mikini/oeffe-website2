MAILDIR=/var/www/øffe.dk/
if realpath $PWD|grep --quiet -- $MAILDIR; then
  MAILDIR=$PWD/
else
  MAILDIR=/var/www/øffe.dk/mails/
fi
echo ${MAILDIR}bestil_* 
#exit
for f in ${MAILDIR}bestil_*; do 
  echo -e $f\\n\\n
  if [ ! "$1" == "--raw" ]; then
    cat $f |grep :| rev | cut -d: -f1 | rev | sed -e s/'^ '/\\t/g
  else
   cat $f
  fi
done | less
