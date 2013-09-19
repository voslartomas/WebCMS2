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
		$packages = self::getPackages();
		
		return $packages['webcms2/webcms2'];
	}
	
	public static function getPackages(){
		$handle = @fopen(self::VERSION_FILE, "r");
		$packages = array();
		if ($handle) {
			while (($buffer = fgets($handle, 4096)) !== false) {
				$parsed = explode(' ', preg_replace('!\s+!', ' ', $buffer));
				$vendorPackage = explode('/', $parsed[0]);
				
				$vendor = $vendorPackage[0];
				$package = $vendorPackage[1];
				$version = $parsed[1];
				$versionHash = ($parsed[1] == 'dev-master' ? $parsed[2] : '');
				
				$description = implode(' ', str_replace(array(
					$parsed[0],
					$version,
					$versionHash
				), '', $parsed));
				
				$packages[$parsed[0]] = array(
							'vendor' => $vendorPackage[0],
							'package' => $vendorPackage[1],
							'versionHash' => $parsed[1] == 'dev-master' ? $parsed[2] : '',
							'version' => $parsed[1],
							'system' => $vendorPackage[0] == 'webcms2' ? FALSE : TRUE,
							'description' => $description
						);
			}
			
			if (!feof($handle)) {
				echo "Error: unexpected fgets() fail\n";
			}
			fclose($handle);
		}
				
		return $packages;
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
	 * Checks whether user has role superadmin or not.
	 * @param \Nette\Security\User $user
	 * @return Boolean
	 */
	public static function isSuperAdmin(\Nette\Security\User $user){
		$roles = $user->getIdentity()->getRoles();
		
		return in_array('superadmin', $roles);
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