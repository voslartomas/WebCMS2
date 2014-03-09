#!/bin/bash

# variables
action=$1

name=$2
Name=$3
author=$4
email=$5
description=$6

# validation

if [ "$name" = "" ]; then
	echo 'Name parameter is required.'
	exit 0
fi

if [ "$action" = "create" ]; then
	
	if [ -d ../../$name-module ]; then
		echo "Module '$name' already exists."
		exit 0
	fi

	# copy default module directory
	tar -xf ../AdminModule/static/default-module.tar -C ../../
	mv ../../default-module ../../$name-module
	
	# rename files
	find ../../$name-module -name 'Name*' -type f -exec bash -c "mv \"\$1\" \"\${1/Name/$Name}\"" -- {} \;
	find ../../$name-module -name 'Name*' -type d -exec bash -c "mv \"\$1\" \"\${1/Name/$Name}\"" -- {} \;

	# replace variables
	grep -rl '#Name#' ../../$name-module | xargs sed -i "s/#Name#/$Name/g"
	grep -rl '#name#' ../../$name-module | xargs sed -i "s/#name#/$name/g"
	grep -rl '#Author#' ../../$name-module | xargs sed -i "s/#Author#/$author/g"
	grep -rl '#AuthorEmail#' ../../$name-module | xargs sed -i "s/#AuthorEmail#/$email/g"
	grep -rl '#Description#' ../../$name-module | xargs sed -i "s/#Description#/$description/g"

else
	echo "Non existing action."
	exit 0
fi 
