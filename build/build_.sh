#!/bin/bash
clear
shopt -s extglob

SSH_ADDRESS=web@91.226.80.187

GENERATED=$(date +%F--%N)
BACKUP_DIR=/home/web/melle.online/backup/$(date +%F)
WORK_DIR=/home/web/melle.online/www
WEBADDRESS=melle.online

# CONFIG FILES
rm -f ./config.php ./admin/config.php
rm -f ./config.local.php ./admin/config.local.php
mv ./config.work.php ./config.php
mv ./admin/config.work.php ./admin/config.php

# YARN RUN
# rm -f ./yarn.lock
yarn install
yarn run prod
yarn run prod-import1c
yarn run prod-offersadmin
yarn run prod-offers
yarn run prod-discount
yarn run prod-sizelist

# CLEAR AFTER
rm -Rf ./.git/
rm -Rf ./node_modules/
rm -Rf ./checkurl/
rm -Rf ./catalog/view/javascript/melle/src/
rm -Rf ./admin/view/javascript/import_1c/src/
rm -Rf ./admin/view/javascript/super_offers/src/
rm -Rf ./admin/view/javascript/super_offers_admin/src/
rm -Rf ./admin/view/javascript/pro_discount/src/
rm -Rf ./admin/view/javascript/size_list/src/
rm -Rf ./opt/
rm -f README.md

# REMOVE ALL BUT
rm -v !("config.php"|"index.php"|".htaccess"|"robots.txt")

# BACKUP
sshpass -V
export SSHPASS=$K8S_SECRET_SSH
sshpass -e ssh -o stricthostkeychecking=no "$SSH_ADDRESS" "bash -s" < ./build/backup.sh $BACKUP_DIR $WORK_DIR

# CLONE TO PROD
7z a -tzip foo.zip -x\!build -x\!.git
sshpass -e scp -o stricthostkeychecking=no -r ./foo.zip $SSH_ADDRESS:$WORK_DIR
sshpass -e ssh -o stricthostkeychecking=no "$SSH_ADDRESS" "7z -y x $WORK_DIR/foo.zip -o$WORK_DIR/"
sshpass -e ssh -o stricthostkeychecking=no "$SSH_ADDRESS" "rm -f $WORK_DIR/foo.zip"
wget "$WEBADDRESS/admin/index.php?route=common/login&git_token=$K8S_SECRET_TOKEN" -O /dev/null --delete-after -qq