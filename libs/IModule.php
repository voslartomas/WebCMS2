<?php

namespace WebCMS;

/**
 * Module adapter.
 *
 * @author Tomáš Voslař <tomas.voslar at webcook.cz>
 */
interface IModule
{
    /**
     * Clone modules data.
     * @param \Doctrine\ORM\EntityManager $entityManager
     * @param \WebCMS\Entity\Language     $oldLanguge
     * @param \WebCMS\Entity\Language     $newLanguage
     * @param Array                       $transformTable
     *
     * @return false|null
     */
    public function cloneData($entityManager, $oldLanguge, $newLanguage, $transformTable);

    /**
     * Translates module data.
     * @param \Doctrine\ORM\EntityManager     $entityManager
     * @param \WebCMS\Entity\Language         $language
     * @param abbr                            $from
     * @param abbr                            $to
     * @param \Nette\Localization\ITranslator $translator
     *
     * @return false|null
     */
    public function translateData($entityManager, $language, $from, $to, \Webcook\Translator\ITranslator $translator);

    /**
     * @param \Doctrine\ORM\EntityManager $entityManager
     * @param String phrase
     * @param \WebCMS\Entity\Language     $language
     *
     * @return false|null
     */
    public function search(\Doctrine\ORM\EntityManager $entityManager, $phrase, \WebCMS\Entity\Language $language);
}
