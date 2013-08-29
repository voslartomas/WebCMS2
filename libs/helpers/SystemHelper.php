<?php

namespace WebCMS;

class SystemHelper {
	
	const VERSION_FILE = '../libs/webcms2/webcms2/AdminModule/version';
	
	/**
	 * Gets actual version of the system.
	 * @return Array[revision, date]
	 */
	public static function getVersion(){
		$versionContent = self::getFileContent(self::VERSION_FILE);
		$version = explode(";", $versionContent);
		
		if(count($version) == 2) 
			return array(
			'revision' => $version[0],
			'date' => $version[1]
		);
		else
			return array(
				'revision' => '0',
				'date' => '0'
			);
	}
	
	/**
	 * Gets content of the defined file, if not exists its create empty file and returns it.
	 * @param String $filename
	 * @return String
	 */
	private static function getFileContent($filename){
		if (!file_exists($filename)) {
			file_put_contents($filename, '');
		}
		
		return file_get_contents($filename);
	}
}