#!/bin/bash
##################################################
# Author: young_liu@vip.sina.com
# Created Time: 2016-09-06 15:39:20
##################################################
PNAME=zeus_thread_mis
VERSION="2.2.5"

echo $VERSION
SCRATCH_DIR=$PNAME-$VERSION

mkdir $SCRATCH_DIR 
cp -r backend frontend common init requirements.php environments console yii $SCRATCH_DIR/

fpm --rpm-auto-add-directories -s dir -t rpm -n $PNAME -v $VERSION --epoch=`date +%s` --rpm-user zeus --rpm-group deploy \
--rpm-defattrfile=0755 --prefix=/usr/local/xxx/prog.d $SCRATCH_DIR

rm -rf target 2>/dev/null
mkdir target
mv *.rpm target/

rm $SCRATCH_DIR -rf
# vim: set noexpandtab ts=4 sts=4 sw=4 :
