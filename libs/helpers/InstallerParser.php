<?php

namespace WebCMS;

class InstallerParser{
	const DATABASE_UPDATE_NO_CHANGES = 'Nothing to update - your database is already in sync with the current entity metadata.',
			DATABASE_UPDATE_OK = 'Updating database schema... Database schema updated successfully!',
			DATABASE_UPDATE_ERROR = 'ERROR: the server encountered an internal error and was unable to complete your request.',
			COMPOSER_UPDATE_NO_CHANGES = 'Loading composer repositories with package information Updating dependencies (including require-dev) Nothing to install or update Generating autoload files';
	
	public static function parse($string){
		
	}
	
}