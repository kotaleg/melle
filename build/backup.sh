#!/bin/bash

shopt -s extglob

if [ ! -d "$1" ]; then
    echo "creating backup dir"
    mkdir "$1"
fi

ZIP_NAME=backup

for i in {1..1000}
do
    if [ ! -f "$1/$ZIP_NAME${i}.zip" ]
    then
        ZIP_NAME="$ZIP_NAME${i}"
        echo "backup name = $ZIP_NAME"
        break
    fi
done

if [ -d "$2" ]; then
    cd "$2" || echo "ERR: CAN'T CD TO \`$2\`" && exit 1

    if [ "$(pwd)" = "$2" ]; then
        echo "creating backup archive"
        7z a -tzip "$ZIP_NAME.zip" . -x\!image -x\!protected -x\!system/storage -x\!opt

        if [ ! -f "$1/$ZIP_NAME.zip" ]; then
            echo "moving backup to the backup folder"
            mv -v "$2/$ZIP_NAME.zip" "$1"
        else
            echo "backup archive do not exist"
        fi

        echo "removing old shit"
        rm -rf .*
        rm -rf !(image|protected|opt)
    else
        echo "we are not in the working dir"
    fi

else
    echo "work dir not exist"
fi

if [ ! -d "$2" ]; then
    echo "creating working dir"
    mkdir "$2"
fi