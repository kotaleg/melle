shopt -s extglob

if [ ! -d "$1" ]; then
    mkdir "$1"
fi

ZIP_NAME=backup

for i in {1..1000}
do
    if [ ! -f "$1/$ZIP_NAME${i}.zip" ]
    then
        ZIP_NAME="$ZIP_NAME${i}"
        echo "$ZIP_NAME"
        break
    fi
done

if [ -d "$2" ]; then
    cd "$2"

    if [ "$(pwd)" = "$2" ]; then
        7z a -tzip "$ZIP_NAME.zip" . -x\!image -x\!protected -x\!system/storage -x\!opt

        if [ ! -f "$1/$ZIP_NAME.zip" ]; then
            mv -v "$2/$ZIP_NAME.zip" "$1"
        fi

        rm -rf .*
        rm -rf !(image|protected)
    fi
fi

if [ ! -d "$2" ]; then
    mkdir "$2"
fi