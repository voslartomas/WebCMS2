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
		
		exec("cd ../;git pull --no-interaction;composer update --no-interaction --prefer-dist > $installLog 2> $installErrorLog");
		
		$successMessage = $this->getMessageFromFile('.' . $installLog);
		
		if($this->user->isInRole('superadmin')){
			$this->flashMessage($successMessage, 'success');
		}
		
		if(strpos($successMessage, 'System has been updated.') !== FALSE){
			$this->flashMessage($this->translation['System has been udpated.'], 'success');
		}
		
		$errorMessage = $this->getMessageFromFile('.' . $installErrorLog);
		if(!empty($errorMessage) && $this->user->isInRole('superadmin')){
			$this->flashMessage($errorMessage, 'danger');
		}elseif(!empty($errorMessage)){
			$this->flashMessage($this->translation['Error while updating system. Please contact administrator.'], 'danger');
		}
		
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
	
	public function handleBackupDatabase(){
		
		$par = $this->context->getParameters();
		
		if(!file_exists('./upload/backups')){
			mkdir('./upload/backups');
		}
		
		$user = $par['database']['user'];
		$password = $par['database']['password'];
		$password = $par['database']['password'];
		$database = $par['database']['dbname'];
		
		exec("mysqldump -u $user -p $password $database > ./upload/backups/db-backup-" . time() . ".sql");
		
		$this->flashMessage($this->translation['Backup has been create. You can download this backup in filesystem - backup directory.'], 'success');
	}
}