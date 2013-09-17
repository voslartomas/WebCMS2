<?php

namespace WebCMS;

class SystemHelper {
	
	const VERSION_FILE = '../app/webcms2/AdminModule/version';
	
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
	 * 
	 * @param type $user
	 * @param type $permission
	 */
	public static function checkPermissions($user, $permission){
		$roles = $user->getRoles();
		
		if($roles[0] === 'superadmin')
			return true;
		
		$identity = $user->getIdentity();
		
		if(is_object($identity)){
			if(array_key_exists($permission, $identity->data['permissions'])){
				return $identity->data['permissions'][$permission];
			}
		}
		
		return false;
	}
	
	/**
	 * Returns system resources.
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