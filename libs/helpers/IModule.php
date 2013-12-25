<?php

namespace WebCMS;

/**
 * Description of IModule
 *
 * @author Tomáš Voslař <tomas.voslar at webcook.cz>
 */
interface IModule {
	
	public function cloneData($entityManager, $oldLanguge, $newLanguage, $transformTable);
}