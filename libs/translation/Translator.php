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
     * @param type $count
     */
    public function translate($message, $count = NULL) {
	return $this->translations[$message];
    }

}
