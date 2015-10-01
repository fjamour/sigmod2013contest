#!/usr/bin/bash

# $1 should be a folder name that exists besides the script
# $1 should contain the source code of the implementation

FILE_NAMES=$(find $1 -name '*.cpp' -o -name '*.cc' -o -name '*.c' | sed 's/\//\\\//g')
FILE_NAMES=$(echo $FILE_NAMES)
INCLUDES=$(find $1 -type d | sed 's/^/-I.\//' | sed 's/\//\\\//g')
INCLUDES=$(echo $INCLUDES)
sed "/IMPL_O=/ s/$/$FILE_NAMES/" Makefile.template | sed "/CFLAGS=/ s/$/ $INCLUDES/"
