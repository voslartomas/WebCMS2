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
		
		system("../install/install.sh 4 > ../log/install.log");

		$this->flashMessage('System aktualizován na nejnovější verzi.');

		$this->redirect('Update:');
	}
}

?>
