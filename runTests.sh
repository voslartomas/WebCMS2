#!/bin/bash

phpunit --no-globals-backup --bootstrap tests/bootstrap.php tests/AdminModule
phpunit --no-globals-backup --bootstrap tests/bootstrap.php tests/Entity
phpunit --no-globals-backup --bootstrap tests/bootstrap.php tests/libs
