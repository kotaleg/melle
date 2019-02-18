#!/bin/bash
shopt -s extglob

SSH_ADDRESS=web@91.226.80.187

GENERATED=$(date +%F--%N)
BACKUP_DIR=/home/web/work.melle.online/backup/$(date +%F)
WORK_DIR=/home/web/work.melle.online/www

# CONFIG FILES
rm -f ./config.php ./admin/config.php
mv ./config.work.php ./config.php
mv ./admin/config.work.php ./admin/config.php

# YARN RUN
rm -f ./yarn.lock
yarn install
yarn run prod
yarn run prod-import1c
yarn run prod-offersadmin
yarn run prod-offers

# CLEAR AFTER YARN
rm -Rf ./.git/
rm -Rf ./node_modules/
rm -Rf ./catalog/view/javascript/melle/src/
rm -Rf ./admin/view/javascript/import_1c/src/
rm -Rf ./admin/view/javascript/super_offers/src/
rm -Rf ./admin/view/javascript/super_offers_admin/src/

# REMOVE ALL BUT
rm -v !("config.php"|"index.php"|".htaccess"|"robots.txt"|"build.sh")


# BACKUP
sshpass -V
export SSHPASS=$K8S_SECRET_SSH
sshpass -e ssh -o stricthostkeychecking=no $SSH_ADDRESS "bash -s
    if [ ! -d "$BACKUP_DIR" ]; then
        mkdir $BACKUP_DIR
    fi

    ZIP_NAME=backup

    for i in {1..1000}
    do
        if [ ! -f "$BACKUP_DIR$ZIP_NAME${i}.zip" ]
        then
            ZIP_NAME = "$ZIP_NAME${i}"
            echo $ZIP_NAME
            break
        fi
    done

    if [ -d "$WORK_DIR" ]; then
        cd $WORK_DIR

        7z a -tzip $WORK_DIR/$ZIP_NAME.zip $WORK_DIR -x\!image -x\!protected

        if [ ! -f "$BACKUP_DIR$ZIP_NAME.zip" ]
            mv -v $WORK_DIR/$ZIP_NAME.zip $BACKUP_DIR
        fi

        rm -rf $WORK_DIR/.*
        rm -rf \!(image|protected)
    fi

    if [ ! -d "$WORK_DIR" ]; then
        mkdir $WORK_DIR
    fi
"

# CLONE TO PROD
7z a -tzip foo.zip -x\!build.sh
sshpass -e scp -o stricthostkeychecking=no -r ./foo.zip $SSH_ADDRESS:$WORK_DIR
sshpass -e ssh -o stricthostkeychecking=no $SSH_ADDRESS "7z -y x $WORK_DIR/foo.zip -o$WORK_DIR/"
sshpass -e ssh -o stricthostkeychecking=no $SSH_ADDRESS "rm -f $WORK_DIR/foo.zip"
