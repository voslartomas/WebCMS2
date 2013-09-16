<?php

namespace WebCMS;

class SystemHelper {
	
	const VERSION_FILE = '../libs/webcms2/webcms2/AdminModule/version';
	
	/**
	 * Helper loader.
	 * @param type $helper
	 * @return type
	 */
	 public static function loader($helper){
        if (method_exists(__CLASS__, $helper)){
            return callback(__CLASS__, $helper);
        }
	 }
	
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
	
	/**
	 * Returns formatted size.
	 * @param Int $size size in bytes
	 * @return String - formatted size
	 */
	public static function formatSize($size) {
		$sizes = array(" Bytes", " KB", " MB", " GB", " TB", " PB", " EB", " ZB", " YB");
		if ($size == 0) {
			return('n/a');
		} else {
			return (round($size/pow(1024, ($i = floor(log($size, 1024)))), 2) . $sizes[$i]);
		}
	}
	
	/**
	 * Returns systems resources.
	 * @return Array
	 */
	public static function getResources(){
		return array(
			'admin:Settings',
			'admin:Users',
			'admin:Languages',
			'admin:Modules',
			'admin:Pages',
			'admin:Filesystem',
			'admin:Update'
		);
	}
}