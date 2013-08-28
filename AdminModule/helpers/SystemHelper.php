<?php

namespace AdminModule;

class SystemHelper {
	
	public static function getFileContent($filename){
		
		if (!file_exists($filename)) {
			file_put_contents($filename, '');
		}
		
		return file_get_contents($filename);
	}
}