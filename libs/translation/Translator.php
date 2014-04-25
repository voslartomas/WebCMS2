<?php

namespace WebCMS\Translation;

/**
 * @author Tomáš Voslař <tomas.voslar at webcook.cz>
 */
class Translator implements \Nette\Localization\ITranslator {
    
    /* @var TranslationArray */
    private $translations;

    public function __construct($translations) {
	$this->translations = $translations;
    }

    /**
     * Translates given message.
     * @param type $message
     * @param type $parameters
     */
    public function translate($message, $parameters = array()) {
	return vsprintf($this->translations[$message], $parameters);
    }

}
