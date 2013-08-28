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
		
		system("../install/install.sh 4 > ../log/install.log 2> ../log/install-error.log");
		$res = file_get_contents('../log/install.log');
		
		$this->flashMessage($res);
		if(file_exists('../log/install-error.log')){
			$resError = file_get_contents('../log/install-error.log');
			$this->flashMessage($resError);
			unlink('../log/install-error.log');
		}
		
		$version = system('cd ../libs/webcms2/webcms2;git log --pretty=format:%h -1;echo ";";git log --format="%at" -1');
		
		file_put_contents('../libs/webcms2/webcms2/AdminModule/version', $version);
		
		unlink('../log/install.log');
		
		$this->redirect('Update:');
	}
}

?>
