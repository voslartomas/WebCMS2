#!/bin/bash

mkdir tests/temp
mkdir tests/log
mkdir upload
mkdir thumbnails

if [ "$1" = "coverage" ]; then
    phpunit --coverage-clover=coverage.clover tests
    wget https://scrutinizer-ci.com/ocular.phar
    php ocular.phar code-coverage:upload --format=php-clover coverage.clover
    exit 0
fi

phpunit tests

