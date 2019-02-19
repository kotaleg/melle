#!/bin/bash

if [ ! -d "$1" ]; then
    mkdir "$1"
fi

ZIP_NAME=backup

for i in {1..1000}
do
    if [ ! -f "$1$ZIP_NAME${i}.zip" ]
    then
        ZIP_NAME="$ZIP_NAME${i}"
        echo "$ZIP_NAME"
        break
    fi
done

if [ -d "$2" ]; then
    cd "$2"

    7z a -tzip "$2/$ZIP_NAME.zip" "$2" -x\!image -x\!protected

    if [ ! -f "$1$ZIP_NAME.zip" ]
        mv -v "$2/$ZIP_NAME.zip" "$1"
    fi

    rm -rf "$2/.*"
    rm -rf \!(image|protected)
fi

if [ ! -d "$2" ]; then
    mkdir "$2"
fi