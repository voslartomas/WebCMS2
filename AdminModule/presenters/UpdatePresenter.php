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
		
		$this->reloadContent();
	}
	
	protected function startup(){		
		parent::startup();
	}
	
	public function renderDefault(){
		$this->template->packages = \WebCMS\SystemHelper::getPackages();
	}
	
	public function handleUpdateSystem(){
		
		$installLog = './log/install.log';
		$installErrorLog = './log/install-error.log';
		
		putenv("COMPOSER_HOME=/usr/bin/.composer");
		
		exec("cd ../;git pull;composer update > $installLog 2> $installErrorLog");
		
		$this->flashMessage($this->getMessageFromFile('.' . $installLog), 'success');
		$this->flashMessage($this->getMessageFromFile('.' . $installErrorLog), 'danger');
		
		if(!$this->isAjax())
			$this->redirect('Update:');
		else{
			$this->invalidateControl('footer');
		}
	}
	
	private function getMessageFromFile($file){
		if(file_exists($file)){
			$message = file_get_contents($file);
		}else{
			$message = 'error';
		}
		
		return $message;
	}
	
	public function actionClearCache(){
		// pomoci skriptu jen hned po skriptu ukoncit pomoc terminate, ajaxove
		$this->context->cacheStorage->clean(array(\Nette\Caching\Cache::ALL => TRUE));
		
		$this->flashMessage('Mezipaměť byla smazána.', 'success');
		$this->redirect("Update:");
	}
}