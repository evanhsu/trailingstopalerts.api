#!/usr/bin/env bash

# Check current folder name
# If not "src", then abort
if [ ${PWD##*/} != "src" ]; then
    exit
fi

cd ..
git clone https://github.com/Laradock/laradock.git
cp src/laradock.env laradock/.env
cd src

echo "Laradock has been configured! Now run `./docker-up.sh` !"
