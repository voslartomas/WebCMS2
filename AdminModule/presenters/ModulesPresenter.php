<?php

namespace AdminModule;

/**
 * Models presenter.
 * @author Tomáš Voslař <tomas.voslar at webcook.cz>
 * @package WebCMS2
 */
class ModulesPresenter extends \AdminModule\BasePresenter{

	protected function beforeRender(){
		parent::beforeRender();
		
	}
	
	protected function startup(){		
		parent::startup();
	}
	
	public function renderDefault(){
		$this->reloadContent();
		
		$packages = \WebCMS\SystemHelper::getPackages();
		
		$modules = array();
		foreach($packages as $package){
			if($package['module']){
				$module = $this->createObject($package['package']);
				
				$package['registered'] = $this->isRegistered($module);
				$modules[] = $package;
			}
		}
		
		$this->template->modules = $modules;
	}
	
	public function actionRegister($name){
		$module = $this->createObject($name);

		if(!$this->isRegistered($module)){
		
			$mod = new Module;
			$mod->setName($module->getName());
			$mod->setPresenters($module->getPresenters());
			$mod->setActive(TRUE);

			$this->em->persist($mod);
			$this->em->flush();

			$this->flashMessage('Module has been registered.', 'success');
		
		}else{
			$this->flashMessage('Module is already registered.', 'danger');
		}
		
		if(!$this->isAjax())
			$this->redirect('default');
	}
	
	public function actionUnregister($name){
		$module = $this->createObject($name);
		$module = $this->em->getRepository('AdminModule\Module')->findOneBy(array(
			'name' => $module->getName()
		));
		
		// TODO we should remove pages with this module from routes or just set module to null or something default
		$this->em->remove($module);
		$this->em->flush();
		
		$this->flashMessage('Module has been unregistered from system.', 'success');
		if(!$this->isAjax())
			$this->redirect('default');
	}
	
	private function isRegistered($module){
		$exists = $this->em->getRepository('AdminModule\Module')->findOneBy(array(
			'name' => $module->getName()
		));
		
		return is_object($exists) ? TRUE : FALSE;
	}
	
	private function createObject($name){
		$expl = explode('-', $name);

		$objectName = ucfirst($expl[0]);
		$objectName = "\\WebCMS\\$objectName" . "Module\\" . $objectName;
		
		return new $objectName;
	}
}