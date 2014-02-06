<?php

namespace AdminModule;
use Dubture\Monolog\Reader\LogReader;


/**
 * Admin presenter.
 * @author Tomáš Voslař <tomas.voslar at webcook.cz>
 * @package WebCMS2
 */
class HomepagePresenter extends \AdminModule\BasePresenter{

	protected function beforeRender(){
		parent::beforeRender();
		
		$this->reloadContent();

		$logFile = '../log/webcms.log';
		$reader = new LogReader($logFile, 2);

		$logs = array();
		foreach($reader as $log){
		    if(!empty($log) && $log['level'] === 'INFO'){
			$logs[] = $log;
		    }
		}
		
		// favourite links
		$user = $this->em->getRepository('WebCMS\Entity\User')->find($this->getUser()->getId());
		$favourites = $this->em->getRepository('WebCMS\Entity\Favourites')->findBy(array(
			'user' => $user
		));
		
		$this->template->logReader = array_reverse($logs);
		$this->template->links = $favourites;
	}
	
	protected function startup(){		
		parent::startup();
	}
	
}