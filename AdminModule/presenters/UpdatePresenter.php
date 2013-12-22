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
		
		$packages = \WebCMS\SystemHelper::getPackages();
		
		foreach($packages as &$package){
			if($package['module']){
				$module = $this->createObject($package['package']);
				
				$package['registered'] = $this->isRegistered($module->getName());
			}
		}
		
		$this->template->packages = $packages;
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
		$this->context->cacheStorage->clean(array(\Nette\Caching\Cache::ALL => TRUE));
		
		$this->flashMessage($this->translation['Mezipaměť byla smazána.'], 'success');
		$this->redirect("Update:");
	}
	
	public function handleBackupDatabase(){
		
		$par = $this->context->getParameters();
		
		if(!file_exists('./upload/backups')){
			mkdir('./upload/backups');
		}
		
		$user = $par['database']['user'];
		$password = $par['database']['password'];
		$database = $par['database']['dbname'];
		
		exec("mysqldump -u $user -p$password $database > ./upload/backups/db-backup-" . time() . ".sql");
		
		$this->flashMessage($this->translation['Backup has been create. You can download this backup in filesystem - backup directory.'], 'success');
	}
	
	// REFACTOR
	public function actionRegister($name){
		$module = $this->createObject($name);

		if(!$this->isRegistered($name)){
		
			$exists = $this->em->getRepository('AdminModule\Module')->findOneBy(array(
				'name' => $module->getName()
			));
			
			if(is_object($exists)){
				$exists->setActive(TRUE);
			}else{
				$mod = new Module;
				$mod->setName($module->getName());
				$mod->setPresenters($module->getPresenters());
				$mod->setActive(TRUE);

				$this->em->persist($mod);
			}
			
			$this->em->flush();
			$this->copyTemplates($name);
			$this->flashMessage('Module has been registered.', 'success');
		}else{
			
			$this->flashMessage('Module is already registered.', 'danger');
		}
		
		if(!$this->isAjax())
			$this->redirect('default');
	}
	
	private function copyTemplates($name){
		if(!file_exists('../app/templates/' . $name)) mkdir('../app/templates/' . $name);
		exec('cp -r ../libs/webcms2/' . $name . '/Frontend/templatesDefault/* ../app/templates/' . $name);
	}
	
	public function actionUnregister($name){
		$module = $this->createObject($name);
		$module = $this->em->getRepository('AdminModule\Module')->findOneBy(array(
			'name' => $module->getName()
		));
		
		$module->setActive(FALSE);
		$this->em->flush();
		
		$this->flashMessage('Module has been unregistered from system.', 'success');
		if(!$this->isAjax())
			$this->redirect('default');
	}
	
	private function isRegistered($name){
		$exists = $this->em->getRepository('AdminModule\Module')->findOneBy(array(
			'name' => $name
		));
		
		if(is_object($exists) && $exists->getActive()){
			return TRUE;
		}else{
			return FALSE;
		}
	}
	
	public function handleCheckUpdates(){
		$client = new \Packagist\Api\Client();

		$packages = \WebCMS\SystemHelper::getPackages();
		
		$needUpdateCount = 0;
		foreach($packages as &$package){
			if($package['module']){
				
				$apiResult = $client->get($package['vendor'] . '/' . $package['package']);
				$versions = $apiResult->getVersions();
				
				$devVersion = $versions['dev-master'];
				if(count($versions) > 1){
					$newestVersion = next($versions);   
				}else{
					$newestVersion = null;
				}
				
				// development or production version?
				if($package['version'] === 'dev-master'){
					if($package['versionHash'] !== mb_substr($devVersion->getSource()->getReference(), 0, 7)){
						$needUpdateCount++;
					}
				}else{
					if($package['version'] !== $newestVersion->getName()){
						$needUpdateCount++;
					}
				}
				
			}
		}
		
		$nuc = $this->settings->get('needUpdateCount', 'system', 'text');
		
		$setting = $this->em->find('AdminModule\Setting', $nuc->getId());
		$setting->setValue($needUpdateCount);
		
		if($needUpdateCount > 0){
			$this->flashMessage($this->translation['Available ' . $needUpdateCount . ' new updates. You can upgrade your system in Update section.'], 'success');
		}else{
			$this->flashMessage($this->translation['Your system is up to date.'], 'success');
		}
		
		$this->em->flush();
	}
}