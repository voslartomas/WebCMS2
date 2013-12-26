<?php

namespace WebCMS;

/**
 * Module adapter.
 *
 * @author Tomáš Voslař <tomas.voslar at webcook.cz>
 */
interface IModule {
	
	/**
	 * Clone modules data.
	 * @param \Doctrine\ORM\EntityManager $entityManager
	 * @param \AdminModule\Language $oldLanguge
	 * @param \AdminModule\Language $newLanguage
	 * @param Array $transformTable
	 */
	public function cloneData($entityManager, $oldLanguge, $newLanguage, $transformTable);
	
	/**
	 * Translates module data.
	 * @param \Doctrine\ORM\EntityManager $entityManager
	 * @param \AdminModule\Language $language
	 * @param abbr $from
         * @param abbr $to
         * @param \Webcook\Translator\ITranslator $translator
	 */
	public function translateData($entityManager, $language, $from, $to, \Webcook\Translator\ITranslator $translator);
}