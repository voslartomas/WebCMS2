<?php

namespace WebCMS\Translation;

/**
 *
 */
class Translation extends \ArrayObject
{
    private $translations = null;
    private $em;
    private $language;
    private $backend;

    /**
     * A constructor
     * Prevents direct creation of object
     * @param integer $backend
     */
    public function __construct($em, $language, $backend)
    {
        $this->translations = new TranslationArray($this);

        $translations = $this->loadFromDb($em, $language, $backend);

        foreach ($translations as $t) {
            $this->translations[$t->getKey()] = $t->getTranslation();
        }

        $this->em = $em;
        $this->language = $language;
        $this->backend = $backend;
    }

    public function getTranslations()
    {
        return $this->translations;
    }

    public function addTranslation($key, $value = "")
    {
        $translation = new \WebCMS\Entity\Translation();
        if ($key) {
            $translation->setKey($key);
            $translation->setTranslation($value);
            $translation->setLanguage($this->language);
            $translation->setBackend($this->backend);
            $translation->setHash();

            $this->em->persist($translation);
            $this->em->flush();

            $this->translations[$key] = $value;
        }
    }

    public function getTranslationByKey($key)
    {
        $translations = $this->getTranslations()->getData();

        foreach ($translations as $k => $value) {
            if ($k == $key) {
            return $value;
            }
        }

        // save translation if not exists
        $this->addTranslation($key, $key);

        return $key;
    }

    private function loadFromDb($em, $language, $backend)
    {
        return $em->getRepository('WebCMS\Entity\Translation')->findBy(array(
            'language' => $language,
            'backend' => $backend
        ));
    }
}
