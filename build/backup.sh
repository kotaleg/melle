
BACKUP_DIR=/home/web/work.melle.online/backup/$(date +%F--%N)
WORK_DIR=/home/web/work.melle.online/www/

mkdir $BACKUP_DIR

if [ -d "$WORK_DIR" ]; then
    mv -v $WORK_DIR $BACKUP_DIR
fi

if [ ! -d "$WORK_DIR" ]; then
    mkdir /home/web/work.melle.online/www/
fi
