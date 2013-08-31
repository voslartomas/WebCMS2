<?php

namespace InstallModule;

use Nette\Application\UI;

/**
 * Description of InstallPresenter
 *
 * @author Tomáš Voslař <tomas.voslar at webcook.cz>
 */
class InstallPresenter extends UI\Presenter{
	
	public function beforeRender() {
		parent::beforeRender();
		
		$this->setLayout('install');
		
		if($this->isAjax()){
			$this->invalidateControl('content');
		}
	}
	
	public function actionCompleteInstall(){
		
		$response = system('sh ../install/install.sh > ../log/install.log');
		
		$this->flashMessage('Výstup: ' . $response);
		$this->redirect('Install:');
	}
	
}