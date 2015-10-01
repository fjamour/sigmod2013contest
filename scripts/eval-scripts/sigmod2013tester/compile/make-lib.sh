#!/usr/bin/bash
set -e
# $1 : path of WORK_DIR to put the produced shared library in
# $2 : path of the submission (should be named impl.tar.gz)

#1. copy impl.tar.gz to WORK_DIR
#2. extract impl.tar.gz
#3. copy make-makefile.sh and Makefile.template to WORK_DIR
#4. create a make file
#5. run make


IMPL_TAR=$(basename $2)
IMPL="impl"
cp $2 $1
cd $1 > /dev/null
echo "Uncompressing the submission..."
mkdir $IMPL
tar -xzf $IMPL_TAR -C $IMPL
cd - > /dev/null
cp Makefile.template make-makefile.sh $1
cp  ../include/core.h $1
cd $1
bash make-makefile.sh $IMPL > Makefile
if [ -f $IMPL/flags ];
then
  USER_FLAGS=`cat $IMPL/flags | tr " " "\n" | egrep '^\-[a-km-zA-Z0-9+\.\-\_][a-zA-Z0-9+\.\-\_]*(=[a-zA-Z0-9+\.\-\_]*)?$' | tr "\n" " "`
  USER_LIBS=`cat $IMPL/flags | tr " " "\n" | egrep '^\-l[a-zA-Z0-9+\.\-\_]*(=[a-zA-Z0-9+\.\-\_]*)?$' | tr "\n" " "`
  sed -i "/^USER_FLAGS=/c USER_FLAGS=$USER_FLAGS" Makefile
  sed -i "/^USER_LIBS=/c USER_LIBS=$USER_LIBS" Makefile
fi
echo "Compiling..."
make
find . -not -name 'libcore.so' -not -name '.' | xargs rm -Rf
echo "Compiled libcore.so sucessfully!"

