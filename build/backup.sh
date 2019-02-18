
BACKUP_DIR=/home/web/work.melle.online/backup/$(date +%F--%N)

echo $BACKUP_DIR

mkdir $BACKUP_DIR

mv -v /home/web/work.melle.online/www/* $BACKUP_DIR