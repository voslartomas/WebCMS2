<?php

namespace WebCMS;

/**
 * This class stands for handling js and css files and minimize them into one.
 *
 * @author Tomáš Voslař <tomas.voslar at webcook.cz>
 */
class ScriptHandler {
	
	const SCRIPT_PATH = '/admin-module/js/minified.js';
	
	private $scripts = array();
	
	public function addScript($script){
		$this->scripts[] = $script;
		
	}
	
	/**
	 * Returns minified all scripts.
	 * @return type
	 */
	public function getAll(){
		$js = '';
		foreach($this->scripts as $script){
			$js .= file_get_contents($script);
		}
		
		$minified = \JSMinPlus::minify($js);
		return $minified;
	}
}
