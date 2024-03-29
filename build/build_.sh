#!/bin/bash

clear
shopt -s extglob

SSH_ADDRESS=web@91.226.80.187

# GENERATED=$(date +%F--%N)
BACKUP_DIR=/home/web/melle.online/backup/$(date +%F)
WORK_DIR=/home/web/melle.online/www
WEBADDRESS=melle.online

echo "-- PREPARE CONFIG FILES --"
rm -f ./config.php ./admin/config.php
rm -f ./config.local.php ./admin/config.local.php
mv ./config.work.php ./config.php
mv ./admin/config.work.php ./admin/config.php

echo "-- NPM RUN --"

function is_npm_fail()
{
    if [ "$(grep -c 'Compiled Successfully in' "$1")" -ne 1 ];then
        rm -rf "$1"
        echo "ERR: NPM SCRIPT FAIL"
        exit 1
    fi
}

npm install > .npm-install-result

npm run prod:scripts > .npm-result-scripts
npm run prod:styles > .npm-result-styles
is_npm_fail .npm-result-scripts
is_npm_fail .npm-result-styles

echo "-- CLEAR AFTER --"
rm -Rf ./.git/
rm -Rf ./node_modules/
rm -Rf ./checkurl/
rm -Rf ./catalog/view/javascript/melle/src/
rm -Rf ./opt/
rm -f README.md

echo "-- REMOVE ALL BUT --"
rm -v !("config.php"|"index.php"|"resize.php"|".htaccess"|".user.ini"|".php.ini"|"robots.txt")

sshpass -V
export SSHPASS=$K8S_SECRET_SSH

echo "-- BACKUP --"
sshpass -e ssh -o stricthostkeychecking=no "$SSH_ADDRESS" "bash -s" < ./build/backup.sh "$BACKUP_DIR" "$WORK_DIR"

echo "-- COMPRESS FILES --"
7z a -tzip foo.zip -x\!build -x\!.git

echo "-- CLONE FILES TO SERVER --"
sshpass -e scp -o stricthostkeychecking=no -r ./foo.zip $SSH_ADDRESS:$WORK_DIR

echo "-- UNPACK FILES --"
sshpass -e ssh -o stricthostkeychecking=no "$SSH_ADDRESS" "7z -y x $WORK_DIR/foo.zip -o$WORK_DIR/"

echo "-- REMOVE ARCHIVE FROM SERVER --"
sshpass -e ssh -o stricthostkeychecking=no "$SSH_ADDRESS" "rm -f $WORK_DIR/foo.zip"

echo "-- REFRESH MODIFICATIONS --"
wget "$WEBADDRESS/admin/index.php?route=common/login&git_token=$K8S_SECRET_TOKEN" -O /dev/null --delete-after -qq
