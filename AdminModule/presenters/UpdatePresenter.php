<?php

namespace AdminModule;

/**
 * Description of UpdatePresenter
 *
 * @author Tomáš Voslař <tomas.voslar at webcook.cz>
 */
class UpdatePresenter extends \AdminModule\BasePresenter{
	protected function beforeRender(){
		parent::beforeRender();
		
	}
	
	protected function startup(){		
		parent::startup();
	}
	
	public function actionClearTemp(){
		
	}
	
	public function actionUpdateSystem(){
		
		putenv("COMPOSER_HOME=/usr/bin/.composer");
		
		system("../install/install.sh 4 > ../log/install.log");
		system("../install/install.sh 4 2> ../log/install-error.log");

		$this->flashMessage('System aktualizován na nejnovější verzi.');
		
		$res = file_get_contents('../log/install.log');
		$resError = file_get_contents('../log/install-error.log');
		$this->flashMessage($res);
		$this->flashMessage($resError);
		$this->redirect('Update:');
	}
}

?>
