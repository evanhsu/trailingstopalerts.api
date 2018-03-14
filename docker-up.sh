#!/usr/bin/env bash

cd ../laradock
docker-compose up -d workspace nginx mariadb
cd ../src