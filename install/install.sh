#!/bin/bash

function postUpdate {
	rm -rf ./app/webcms2
	rm -rf ./www/admin-module

	mkdir ./app/webcms2/
	cp -r ./libs/webcms2/webcms2/* ./app/webcms2/
	mkdir ./www/admin-module
	cp -r ./libs/webcms2/webcms2/AdminModule/client-side/* ./www/admin-module/

	chmod -R 777 ./app/webcms2 ./www/admin-module
}

function parseVersion {
	
	touch ./app/webcms2/AdminModule/version

	composer show --installed > ./app/webcms2/AdminModule/version
}

while [ "$task" != "q" ]; do
	
	if [ "$1" == "" ]; then

		echo "Choose command:"
		echo "0) Pre update"
		echo "1) First install"
		echo "2) Update"
		echo "3) Load versions"
		echo "q) Quit"

		echo $vypis
		vypis=""

		echo -n "Type command number>"
		read -s -n 1 task

	else task=$1
	fi
	
	if [ "$task" == "1" ]; then
		
		# creates directories
		mkdir ./www/upload
		mkdir ./www/thumbnails
		
		# sets rights for temp directory
		chmod -R 777 ./www/upload ./www/thumbnails ./temp ./log ./app/proxies ./composer.lock ./libs/composer ./libs/webcms2 ./libs/autoload.php
		chmod -R g+rwxs temp
		
		postUpdate

		# generate DB schema
		php www/index.php --ansi orm:schema-tool:create

		# run initial SQL script
		php www/index.php --ansi dbal:import ./app/webcms2/install/initial.sql

		parseVersion

		vypis="Installation has been executed. Choose another command or type 'q' to quit."
	
	elif [ "$task" == "2" ]; then
		
		postUpdate
				
		# generate DB schema
		php www/index.php --ansi orm:schema-tool:update --force

		# generate proxies
		php www/index.php --ansi orm:generate-proxies

		# run initial SQL script
		php www/index.php --ansi dbal:import ./app/webcms2/install/initial.sql
		
		parseVersion

		vypis="System has been updated."

	elif [ "$task" == "3" ]; then
		
		parseVersion

	elif [ "$task" == "q" ]; then

		echo ""	
		echo "Quitting application. Good bye!"
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
