# Common Stuff
cd ..; sh php_putall.sh; cd -

PARENTDIR=$(cd -L ..; readlink -f .)
php $PARENTDIR/design2db.php putall .
