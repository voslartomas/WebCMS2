#!/bin/bash

function parseVersion {
	
	touch ./libs/webcms2/webcms2/AdminModule/version

	composer show --installed > ./libs/webcms2/webcms2/AdminModule/version
}

while [ "$task" != "q" ]; do
	
	if [ "$1" == "" ]; then

		echo "Choose command:"
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
		mkdir ./log
		mkdir ./temp
		mkdir ./temp/sessions
		
		# sets rights for temp directory
		chmod -R 777 ./www/upload ./www/thumbnails ./temp ./log ./app/proxies ./composer.lock ./libs/composer ./libs/autoload.php
		chmod -R g+rwxs temp
		
		rm -f ./libs/nette/nette/Nette/Application/UI/Form.php
		cp ./libs/webcms2/webcms2/install/Form.php ./libs/nette/nette/Nette/Application/UI/Form.php

		# generate DB schema
		php www/index.php --ansi orm:schema-tool:create

		# generate proxies
		php www/index.php --ansi orm:generate-proxies

		# run initial SQL script
		php www/index.php --ansi dbal:import ./libs/webcms2/webcms2/install/initial.sql

		parseVersion

		vypis="Installation has been executed. Choose another command or type 'q' to quit."
	
	elif [ "$task" == "2" ]; then
						
		# generate DB schema
		php www/index.php --ansi orm:schema-tool:update --force

		# generate proxies
		php www/index.php --ansi orm:generate-proxies

		# run initial SQL script
		php www/index.php --ansi dbal:import ./libs/webcms2/webcms2/install/initial.sql
		
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
