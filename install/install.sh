#!/bin/bash

while [ "$task" != "q" ]; do
	
	if [ "$1" == "" ]; then

		echo "Choose command:"
		echo "0) First install"
		echo "1) Clear cache"
		echo "2) Fix permission (temp and upload)"
		echo "3) Update database schema"
		echo "q) Quit"

		echo $vypis
		vypis=""

		echo -n "Type command number>"
		read -s -n 1 task

	else task=$1
	fi
	
	if [ "$first" == "" ]; then
		first="nope"
		cd .. # get into the right directory
	fi

	if [ "$task" == "0" ]; then
		
		mkdir www/upload

		chmod -R 777 www/upload

		# sets right group for all files
		chgrp -R developers ./*

		# sets rights for temp directory
		chmod -R 777 temp
		chmod -R g+rwxs temp

		# clear vendor library
		rm -rf ./libs/*

		# install dependencies
		composer install

		# generate DB schema
		php www/index.php --ansi orm:schema-tool:update --force

		# run initial SQL script
		php www/index.php --ansi dbal:import install/initial.sql
		
		vypis="Installation has been executed. Choose another command or type 'q' to quit."
		
	elif [ "$task" == "1" ]; then

		# clear temp dir
		rm -rf ./temp/cache

		vypis="Cache has been cleared."

	elif [ "$task" == "2" ]; then

		# sets rights for temp and upload directory
		chmod -R 777 temp
		chmod -R 777 www/upload

		vypis="Permissions have been fixed."

	elif [ "$task" == "3" ]; then

		# update DB schema
		php www/index.php --ansi orm:schema-tool:update --force

		vypis="Database schema has been updated."

	elif [ "$task" == "q" ]; then

		echo ""	
		echo "Quiting application..."
		sleep 1
		exit 0
	else

	   vypis="Bad parameter given."
	   sleep 1

	fi

if [ "$1" != "" ]; then
 echo $vypis
 sleep 1
 exit 0
else
 #clear
 echo ""
fi

done
