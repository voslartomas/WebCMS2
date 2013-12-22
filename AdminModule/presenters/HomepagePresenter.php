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
			$logs[] = $log;
		}
		
		$this->template->logReader = array_reverse($logs);
	}
	
	protected function startup(){		
		parent::startup();
	}
	
}