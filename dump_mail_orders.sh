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
  cat $f |grep :| cut -d: -f2 | sed -e s/'^ '/\\t/g
done | less
