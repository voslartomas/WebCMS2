<?php

namespace AdminModule;

use Nette\Application\UI;
use Nette\Mail;

/**
 * Users presenter.
 * @author Tomáš Voslař <tomas.voslar at webcook.cz>
 * @package WebCMS2
 */
class UsersPresenter extends \AdminModule\BasePresenter {
    /* @var User */

    private $user;

    /* @var Role */
    private $role;

    protected function beforeRender() {
	//parent::beforeRender();
    }

    protected function startup() {
	parent::startup();
    }

    public function renderDefault() {
	$this->reloadContent();
    }

    protected function createComponentUserForm() {

	$roles = $this->em->getRepository("WebCMS\Entity\Role")->findAll();
	$tmp = array();
	foreach ($roles as $r) {
	    $tmp[$r->getId()] = $r->getName();
	}
	$roles = $tmp;

	if ($this->getUser()->getRoles()[0] !== 'superadmin') {
	    unset($roles[1]);
	}

	$form = $this->createForm();
	$form->addText('username', 'Username')->setAttribute('class', 'form-control');
	$form->addSelect('role', 'Role')->setTranslator(NULL)->setItems($roles)->setAttribute('class', 'form-control');
	$form->addText('name', 'Name')->setAttribute('class', 'form-control');
	$form->addText('email', 'Email')->setAttribute('class', 'form-control');
	$form->addPassword('password', 'Password')->setAttribute('class', 'form-control');
	$form->addSubmit('save', 'Save')->setAttribute('class', 'btn btn-success');

	$form->onSuccess[] = callback($this, 'userFormSubmitted');

	if ($this->user)
	    $form->setDefaults($this->user->toArray());

	return $form;
    }

    protected function createComponentGrid($name) {

	$grid = $this->createGrid($this, $name, "User", NULL, array(
	    'id <> 1'
	));

	$grid->addColumnText('username', 'Name')->setSortable();

	$grid->addActionHref("updateUser", 'Edit')->getElementPrototype()->addAttributes(array('class' => array('btn', 'btn-primary', 'ajax'), 'data-toggle' => 'modal', 'data-target' => '#myModal', 'data-remote' => 'false'));
	$grid->addActionHref("deleteUser", 'Delete')->getElementPrototype()->addAttributes(array('class' => array('btn', 'btn-danger'), 'data-confirm' => 'Are you sure you want to delete the item?'));

	return $grid;
    }

    public function actionUpdateUser($id) {
	if ($id)
	    $this->user = $this->em->find("WebCMS\Entity\User", $id);
	else
	    $this->user = new \WebCMS\Entity\User();
    }

    public function actionDeleteUser($id) {
	$this->user = $this->em->find("WebCMS\Entity\User", $id);
	$this->em->remove($this->user);
	$this->em->flush();

	$this->flashMessage('User has been removed.', 'success');

	if (!$this->isAjax())
	    $this->redirect('Users:default');
    }

    public function actionDeleteLanguage($id) {
	$this->language = $this->em->find("WebCMS\Entity\Language", $id);
	$this->em->remove($this->language);
	$this->em->flush();

	$this->flashMessage('Language has been removed.', 'success');

	if (!$this->isAjax())
	    $this->redirect('Languages:default');
    }

    public function renderUpdateUser($id) {

	$this->reloadModalContent();

	$this->template->user = $this->user;
    }

    public function userFormSubmitted(UI\Form $form) {
	$values = $form->getValues();

	$role = $this->em->find("WebCMS\Entity\Role", $values->role);
	$password = $this->getContext()->authenticator->calculateHash($values->password);

	$this->user->setName($values->name);
	$this->user->setEmail($values->email);
	if (!empty($values->password)) {
	    $this->user->setPassword($password);

	    // send mail with new password
	    $email = new Mail\Message;
	    $email->setFrom($this->settings->get('Info email', \WebCMS\Settings::SECTION_BASIC)->getValue());
	    $email->addTo($this->user->getEmail());
	    $email->setSubject($this->settings->get('User new password subject', \WebCMS\Settings::SECTION_EMAIL)->getValue(FALSE));
	    $email->setHtmlBody($this->settings->get('User new password', \WebCMS\Settings::SECTION_EMAIL)->getValue(FALSE, array(
		    '[PASSWORD]',
		    '[LOGIN]'
		    ), array(
		    $values->password,
		    $values->username
	    )));
	    $email->send();

	    $this->flashMessage('Info email with new password has been sent.', 'success');
	}

	$this->user->setUsername($values->username);
	$this->user->setRole($role);

	$this->em->persist($this->user);
	$this->em->flush();

	$this->flashMessage('User has been updated.', 'success');

	if (!$this->isAjax())
	    $this->redirect('Users:default');
    }

    /* ROLES */

    public function renderRoles() {
	$this->reloadContent();
    }

    public function actionUpdateRole($id) {
	if ($id)
	    $this->role = $this->em->find("WebCMS\Entity\Role", $id);
	else
	    $this->role = new \WebCMS\Entity\Role();
    }

    public function actionDeleteRole($id) {
	$this->role = $this->em->find("WebCMS\Entity\Role", $id);
	$this->em->remove($this->role);
	$this->em->flush();

	$this->flashMessage('Role has been removed.', 'success');

	if (!$this->isAjax())
	    $this->redirect('Users:roles');
    }

    public function renderUpdateRole($id) {

	$this->reloadContent();

	$this->template->role = $this->role;
    }

    protected function createComponentRoleForm() {

	$resources = \WebCMS\Helpers\SystemHelper::getResources();

	$pages = $this->em->getRepository('WebCMS\Entity\Page')->findAll();

	foreach ($pages as $page) {

	    if ($page->getParent() != NULL) {

		$module = $this->createObject($page->getModuleName());

		foreach ($module->getPresenters() as $presenter) {

		    $suffix = $presenter['name'] == $page->getModuleName() ? '' : ' ' . $presenter['name'];

		    $key = 'admin:' . $page->getModuleName() . ':' . $presenter['name'] . $page->getId();
		    $resources[$key] = $page->getTitle() . $suffix . ' (' . $page->getLanguage()->getName() . ')';
		}
	    }
	}

	$form = $this->createForm();
	$form->addCheckbox('automaticEnable', 'Automatic enable');
	$form->addText('name', 'Name')->setAttribute('class', 'form-control');

	$c = 0;
	foreach ($resources as $key => $r) {

	    if (strpos('$r', ':') !== FALSE)
		$form->addCheckbox('res' . str_replace(':', '', $key), $r)->setAttribute('class', 'check');
	    else
		$form->addCheckbox('res' . str_replace(':', '', $key), $r)->setTranslator(NULL)->setAttribute('class', 'check');

	    $c++;
	}

	// defaults setting
	$new = $this->role->getName();
	if (!empty($new)) {
	    $defaultsPermissions = array();
	    foreach ($this->role->getPermissions() as $key => $per) {
		$defaultsPermissions['res' . str_replace(':', '', $per->getResource())] = $per->getRead();
	    }

	    $form->setDefaults($this->role->toArray() + $defaultsPermissions);
	}

	$form->onSuccess[] = callback($this, 'roleFormSubmitted');
	$form->addSubmit('save', 'Save')->setAttribute('class', 'btn btn-success');

	return $form;
    }

    protected function createComponentRolesGrid($name) {

	$grid = $this->createGrid($this, $name, "Role", NULL, array(
	    'id <> 1'
	));

	$grid->addColumnText('name', 'Name')->setSortable();

	$grid->addActionHref("updateRole", 'Edit')->getElementPrototype()->addAttributes(array('class' => array('btn', 'btn-primary', 'ajax')));
	$grid->addActionHref("deleteRole", 'Delete')->getElementPrototype()->addAttributes(array('class' => array('btn', 'btn-danger'), 'data-confirm' => 'Are you sure you want to delete the item?'));

	return $grid;
    }

    public function roleFormSubmitted(UI\Form $form) {
	$values = $form->getValues();

	$this->role->setName($values->name);
	$this->role->setAutomaticEnable($values->automaticEnable);

	if (!$this->role->getId()) {
	    $this->em->persist($this->role);
	}

	$this->flashMessage('Role has been added.', 'success');

	// delete permissions
	$permissions = $this->role->getPermissions();
	foreach ($permissions as $per) {
	    $this->em->remove($per);
	}

	// save permissions
	$perArray = array();
	foreach ($values as $key => $val) {
	    if (strpos($key, 'res') !== FALSE) {
		$permission = new \WebCMS\Entity\Permission();

		$pageId = filter_var($key, FILTER_SANITIZE_NUMBER_INT);
		$page = $this->em->getRepository('WebCMS\Entity\Page')->find($pageId);

		$resource = 'admin:' . str_replace('resadmin', '', $key);
		$permission->setResource($resource);
		$permission->setPage($page);
		$permission->setRead($val);

		$perArray[] = $permission;
	    }
	}

	$this->role->setPermissions($perArray);

	$this->em->flush(); // persist all changes

	if (!$this->isAjax())
	    $this->redirect('Users:roles');
    }

}
