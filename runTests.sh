#!/bin/bash

mkdir tests/temp
mkdir tests/log
mkdir upload
mkdir thumbnails

phpunit --no-globals-backup --bootstrap tests/bootstrap.php tests/AdminModule
phpunit --no-globals-backup --bootstrap tests/bootstrap.php tests/Entity
phpunit --no-globals-backup --bootstrap tests/bootstrap.php tests/libs
