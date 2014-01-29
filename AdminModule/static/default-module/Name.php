<?php

namespace WebCMS\#Name#Module;

/**
 * Description of Page
 *
 * @author #Author# <#AuthorEmail#>
 */
class #Name# extends \WebCMS\Module {
    
    protected $name = '#Name#';

    protected $author = '#Author#';

    protected $presenters = array(
	    array(
		    'name' => '#Name#',
		    'frontend' => TRUE,
		    'parameters' => FALSE
		    ),
	    array(
		    'name' => 'Settings',
		    'frontend' => FALSE
		    )
    );

    public function __construct(){
    }

    public function cloneData($em, $oldLang, $newLang, $transform){
	return false;
    }

    public function translateData($em, $language, $from, $to, \Webcook\Translator\ITranslator $translator){
	return false;
    }
}