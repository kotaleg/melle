# bash
shopt -s extglob

GENERATED=$(date +%F--%N)
BACKUP_DIR=/home/web/work.melle.online/backup/$GENERATED
WORK_DIR=/home/web/work.melle.online/www
ZIP_NAME=www.zip
SSH_ADDRESS=web@91.226.80.187

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
    mkdir $BACKUP_DIR

    if [ -d "$WORK_DIR" ]; then
        zip -qq -r $ZIP_NAME $WORK_DIR -x "$WORK_DIR/image/*" -x "$WORK_DIR/protected/*" -x "$WORK_DIR/build.sh"
        mv -v $WORK_DIR/$ZIP_NAME $BACKUP_DIR

        if [ -d "$WORK_DIR/image" ]; then
            mv $WORK_DIR/image /tmp/$GENERATED
        fi

        if [ -d "$WORK_DIR/protected" ]; then
            mv $WORK_DIR/protected /tmp/$GENERATED
        fi

        rm -rf "$WORK_DIR/"{*,.*}
    fi

    if [ ! -d "$WORK_DIR" ]; then
        mkdir $WORK_DIR
    fi

    if [ -d "/tmp/$GENERATED/image" ]; then
        mv /tmp/$GENERATED/image $WORK_DIR/
    fi

    if [ -d "/tmp/$GENERATED/protected" ]; then
        mv /tmp/$GENERATED/protected $WORK_DIR/
    fi"

# CLONE TO PROD
zip -r foo.zip .
sshpass -e scp -o stricthostkeychecking=no -r ./foo.zip $SSH_ADDRESS:$WORK_DIR
sshpass -e ssh -o stricthostkeychecking=no $SSH_ADDRESS "unzip -qq $WORK_DIR/foo.zip -d $WORK_DIR/"
sshpass -e ssh -o stricthostkeychecking=no $SSH_ADDRESS "rm -f $WORK_DIR/foo.zip"
