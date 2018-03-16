#!/usr/bin/env bash

# Check current folder name
# If not "src", then abort
if [ ${PWD##*/} != "src" ]; then
    exit
fi

cd ..
git clone https://github.com/Laradock/laradock.git
cp src/laradock.env laradock/.env
chmod 0600 laradock/workspace/insecure_id_rsa
cd src

echo "Laradock has been configured!"
echo "Running 'docker-up.sh' to build and start the container..."

./docker-up.sh
docker ps

echo "Docker container should be up now!"
echo "Go to http://localhost in your browser"

