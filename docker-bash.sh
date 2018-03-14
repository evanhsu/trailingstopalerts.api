#!/usr/bin/env bash

cd ../laradock
docker-compose exec --user laradock workspace /bin/bash
cd ../src