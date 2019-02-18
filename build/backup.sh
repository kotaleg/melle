
BACKUP_DIR=/home/web/work.melle.online/backup/$(date +%F--%N)
WORK_DIR=/home/web/work.melle.online/www/
ZIP_NAME=www.zip

mkdir $BACKUP_DIR

if [ -d "$WORK_DIR" ]; then
    zip -r $ZIP_NAME $WORK_DIR
    mv -v $WORK_DIR$ZIP_NAME $BACKUP_DIR
    rm -rf "$WORK_DIR"{*,.*}
fi

if [ ! -d "$WORK_DIR" ]; then
    mkdir /home/web/work.melle.online/www/
fi
