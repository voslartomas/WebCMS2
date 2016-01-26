#!/bin/bash

mkdir tests/temp
mkdir tests/log
mkdir upload
mkdir thumbnails

if [ "$1" = "coverage" ]; then
    phpunit --coverage-clover=coverage.clover tests;result=$?
    wget https://scrutinizer-ci.com/ocular.phar
    php ocular.phar code-coverage:upload --format=php-clover coverage.clover
fi

if [ "$1" = "html" ]; then
    phpunit --coverage-html ./html tests;result=$?
fi

if [ "$1" = "" ]; then
    phpunit tests;result=$?
fi

exit $result

