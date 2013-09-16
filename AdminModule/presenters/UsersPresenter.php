<?php

namespace AdminModule;

use Nette\Application\UI;

/**
 * Users presenter.
 * @author Tomáš Voslař <tomas.voslar at webcook.cz>
 * @package WebCMS2
 */
class UsersPresenter extends \AdminModule\BasePresenter{
	
	/* @var User */
	private $user;
	
	/* @var Role */
	private $role;
	
	protected function beforeRender(){
		parent::beforeRender();
	}
	
	protected function startup(){		
		parent::startup();
	}
	
	public function renderDefault(){
		$this->reloadContent();
	}
	
	protected function createComponentUserForm(){
		
		$roles = $this->em->getRepository("AdminModule\Role")->findAll();
		$tmp = array();
		foreach($roles as $r){
			$tmp[$r->getId()] = $r->getName();
		}
		$roles = $tmp;
		
		$form = $this->createForm();
		$form->addText('username', 'Username')->setAttribute('class', 'form-control');
		$form->addSelect('role', 'Role')->setTranslator(NULL)->setItems($roles)->setAttribute('class', 'form-control');
		$form->addText('name', 'Name')->setAttribute('class', 'form-control');
		$form->addText('email', 'Email')->setAttribute('class', 'form-control');
		$form->addPassword('password', 'Password')->setAttribute('class', 'form-control');
		$form->addSubmit('save', 'Save')->setAttribute('class', 'btn btn-success');
		
		$form->onSuccess[] = callback($this, 'userFormSubmitted');
		
		if($this->user) 
			$form->setDefaults($this->user->toArray());

		return $form;
	}
	
	protected function createComponentGrid($name){
		
		$grid = $this->createGrid($this, $name, "User");
		
		$grid->addColumn('username', 'Name')->setSortable();
		
		$grid->addAction("updateUser", 'Edit')->getElementPrototype()->addAttributes(array('class' => 'btn btn-primary ajax', 'data-toggle' => 'modal', 'data-target' => '#myModal', 'data-remote' => 'false'));
		$grid->addAction("deleteUser", 'Delete')->getElementPrototype()->addAttributes(array('class' => 'btn btn-danger', 'data-confirm' => 'Are you sure you want to delete the item?'));

		return $grid;
	}
	
	public function actionUpdateUser($id){
		if($id) $this->user = $this->em->find("AdminModule\User", $id);
		else $this->user = new User();
	}
	
	public function actionDeleteLanguage($id){
		$this->language = $this->em->find("AdminModule\Language", $id);
		$this->em->remove($this->language);
		$this->em->flush();
		
		$this->flashMessage($this->translation['Language has been removed.'], 'success');
		
		if(!$this->isAjax())
			$this->redirect('Languages:default');
	}
	
	public function renderUpdateUser($id){
		
		$this->reloadModalContent();
		
		$this->template->user = $this->user;
	}
	
	public function userFormSubmitted(UI\Form $form){
		$values = $form->getValues();
		
		$role = $this->em->find("AdminModule\Role", $values->role);
		$password = $this->getContext()->authenticator->calculateHash($values->password);
		
		$this->user->setName($values->name);
		$this->user->setEmail($values->email);
		if(!empty($values->password)) $this->user->setPassword($password);
		$this->user->setUsername($values->username);
		$this->user->setRole($role);
		
		$this->em->persist($this->user);
		$this->em->flush();
		
		$this->flashMessage($this->translation['User has been added.'], 'success');
		
		if(!$this->isAjax())
			$this->redirect('Users:default');
	}
	
	/* ROLES */
	
	public function renderRoles(){
		$this->reloadContent();
	}
	
	public function actionUpdateRole($id){
		if($id) $this->role = $this->em->find("AdminModule\Role", $id);
		else $this->role = new Role();
	}
	
	public function actionDeleteRole($id){
		$this->role = $this->em->find("AdminModule\Role", $id);
		$this->em->remove($this->role);
		$this->em->flush();
		
		$this->flashMessage($this->translation['Role has been removed.'], 'success');
		
		if(!$this->isAjax())
			$this->redirect('Users:roles');
	}
	
	public function renderUpdateRole($id){
		
		$this->reloadModalContent();
		
		$this->template->role = $this->role;
	}
	
	protected function createComponentRoleForm(){
		
		$resources = \WebCMS\SystemHelper::getResources();
		
		$form = $this->createForm();
		$form->addText('name', 'Name')->setAttribute('class', 'form-control');
		$form->addSubmit('save', 'Save')->setAttribute('class', 'btn btn-success');
		
		$c = 0;
		foreach($resources as $r){
			
			$form->addCheckbox('res' . str_replace(':', '', $r), $r);
			$c++;
		}
		
		$form->onSuccess[] = callback($this, 'roleFormSubmitted');
		
		$new = $this->role->getName();
		if(!empty($new)){
			$defaultsPermissions = array();
			foreach($this->role->getPermissions() as $key => $per){
				$defaultsPermissions['res' . str_replace(':', '', $per->getResource())] = $per->getRead();
			}
		
			$form->setDefaults($this->role->toArray() + $defaultsPermissions);
		}

		return $form;
	}
	
	protected function createComponentRolesGrid($name){
		
		$grid = $this->createGrid($this, $name, "Role");
		
		$grid->addColumn('name', 'Name')->setSortable();
		
		$grid->addAction("updateRole", 'Edit')->getElementPrototype()->addAttributes(array('class' => 'btn btn-primary ajax', 'data-toggle' => 'modal', 'data-target' => '#myModal', 'data-remote' => 'false'));
		$grid->addAction("deleteRole", 'Delete')->getElementPrototype()->addAttributes(array('class' => 'btn btn-danger', 'data-confirm' => 'Are you sure you want to delete the item?'));

		return $grid;
	}
	
	public function roleFormSubmitted(UI\Form $form){
		$values = $form->getValues();
		
		$this->role->setName($values->name);
		
		$this->em->persist($this->role); // TODO persist only if its new

		$this->flashMessage($this->translation['Role has been added.'], 'success');
		
		// delete permissions
		$permissions = $this->role->getPermissions();
		foreach($permissions as $per){
			$this->em->remove($per);
		}
		
		// save permissions
		$perArray = array();
		foreach($values as $key => $val){
			if(strpos($key, 'res') !== FALSE){
				$permission = new Permission();
				
				$resource = 'admin:' . str_replace('resadmin', '', $key);
				$permission->setResource($resource);
				$permission->setRead($val);
				
				//$this->em->persist($permission);
				$perArray[] = $permission;
			}
		}
		
		$this->role->setPermissions($perArray);
		
		$this->em->flush(); // persist all changes
		
		if(!$this->isAjax())
			$this->redirect('Users:roles');
	}
}