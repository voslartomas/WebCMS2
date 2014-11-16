<?php

namespace WebCMS\Translation;

/**
 * @author Tomáš Voslař <tomas.voslar at webcook.cz>
 */
class Translator implements ITranslator
{
    /* @var TranslationArray */
    private $translations;

    /**
     * @param TranslationArray $translations
     */
    public function __construct($translations)
    {
        $this->translations = $translations;
    }

    /**
     * Translates given message.
     * @param type $message
     * @param type $parameters
     */
    public function translate($message, $parameters = array())
    {
        if (count($parameters) === 0) {
            return $this->translations[$message];
        } else {
            return vsprintf($this->translations[$message], $parameters);
        }
    }
}
