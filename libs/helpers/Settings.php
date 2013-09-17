<?php

namespace WebCMS;

/**
 * Description of Settings
 *
 * @author Tomáš Voslař <tomas.voslar at webcook.cz>
 */
class Settings {
		
	/* @var Array('sections' => array(0 => Setting, ...)) */
	private $settings;
	
	/**
	 * Gets settings by key and section.
	 * @param String $key
	 * @param String $section
	 * @return String
	 * @throws Exception
	 */
	public function get($key, $section = 'basic'){
		if(array_key_exists($section, $this->settings)){
			if(array_key_exists($key, $this->settings[$section])){
				return $this->settings[$section][$key];
			}
		}
		
		return FALSE;
	}
	
	/**
	 * 
	 * @param type $section
	 * @return type
	 * @throws Exception
	 */
	public function getSection($section){
		if(array_key_exists($section, $this->settings)){
			return $this->settings[$section];
		}
		
		return FALSE;
	}
		
	public function getSettings() {
		return $this->settings;
	}

	public function setSettings($settings) {
		$this->settings = $settings;
	}

}