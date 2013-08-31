<?php

namespace AdminModule;

use Nette\Application\UI;

/**
 * Languages and translations presenter.
 * @author Tomáš Voslař <tomas.voslar at webcook.cz>
 * @package WebCMS2
 */
class LanguagesPresenter extends \AdminModule\BasePresenter{
	
	/* @var Language */
	private $language;
	
	protected function beforeRender(){
		parent::beforeRender();
		
	}
	
	protected function startup(){		
		parent::startup();
	}
	
	public function renderDefault(){
		
		$this->reloadContent();
	}
	
	protected function createComponentLanguageForm(){

		$form = $this->createForm();
		$form->addText('name', $this->translation['adminModule_languages_form_name'])->setAttribute('class', 'form-control');
		$form->addText('abbr', $this->translation['adminModule_languages_form_abbr'])->setAttribute('class', 'form-control');
		$form->addCheckbox('defaultFrontend', $this->translation['adminModule_languages_form_default_fe'])->setAttribute('class', 'form-control');
		$form->addCheckbox('defaultBackend', $this->translation['adminModule_languages_form_default_be'])->setAttribute('class', 'form-control');
		$form->addSubmit('save', $this->translation['adminModule_languages_form_save'])->setAttribute('class', 'btn btn-success');
		
		$form->onSuccess[] = callback($this, 'languageFormSubmitted');
		
		if($this->language) 
			$form->setDefaults($this->language->toArray());

		return $form;
	}
	
	protected function createComponentGrid($name){
		
		$grid = $this->createGrid($this, $name, "Language");
		
		$grid->addColumn('name', $this->translation['adminModule_languages_form_name'])->setSortable();
		$grid->addColumnText('abbr', $this->translation['adminModule_languages_form_abbr'])->setSortable();
		$grid->addColumn('defaultFrontend', $this->translation['adminModule_languages_form_backend_fe'])->setReplacement(array(
			'1' => $this->translation['adminModule_yes'],
			NULL => $this->translation['adminModule_no']
		));
		$grid->addColumn('defaultBackend', $this->translation['adminModule_languages_form_backend_be'])->setReplacement(array(
			'1' => $this->translation['adminModule_yes'],
			NULL => $this->translation['adminModule_no']
		));
		
		$grid->addAction("updateLanguage", $this->translation['adminModule_button_edit'])->getElementPrototype()->addAttributes(array('class' => 'btn btn-primary ajax', 'data-toggle' => 'modal', 'data-target' => '#myModal', 'data-remote' => 'false'));
		$grid->addAction("deleteLanguage", $this->translation['adminModule_button_delete'])->getElementPrototype()->addAttributes(array('class' => 'btn btn-danger', 'data-confirm' => $this->translation['adminModule_button_delete_confirm']));

		return $grid;
	}
	
	public function actionUpdateLanguage($id){
		if($id) $this->language = $this->em->find("AdminModule\Language", $id);
		else $this->language = new Language();
	}
	
	public function actionDeleteLanguage($id){
		$this->language = $this->em->find("AdminModule\Language", $id);
		$this->em->remove($this->language);
		$this->em->flush();
		
		$this->flashMessage('Jazyk odstraněn.', 'success');
		
		if(!$this->isAjax())
			$this->redirect('Languages:default');
	}
	
	public function renderUpdateLanguage($id){
		
		$this->reloadModalContent();
		
		$this->template->language = $this->language;
	}
	
	public function languageFormSubmitted(UI\Form $form){
		$values = $form->getValues();
		
		$this->language->setName($values->name);
		$this->language->setAbbr($values->abbr);
		$this->language->setDefaultFrontend($values->defaultFrontend);
		$this->language->setDefaultBackend($values->defaultBackend);
		
		$this->em->persist($this->language);
		$this->em->flush();
		
		// only one item can be default
		if($values->defaultFrontend){
			$qb = $this->em->createQueryBuilder();
			$qb->update('AdminModule\Language', 'l')
					->set('l.defaultFrontend', 0)
					->where('l.id <> ?1')
					->setParameter(1, $this->language->getId())
					->getQuery()
					->execute();
			$this->em->flush();
		}
		
		if($values->defaultBackend){
			$qb = $this->em->createQueryBuilder();
			$qb->update('AdminModule\Language', 'l')
					->set('l.defaultBackend', 0)
					 ->where('l.id <> ?1')
					->setParameter(1, $this->language->getId())
					->getQuery()
					->execute();
			$this->em->flush();
		}

		$this->flashMessage('Jazyk byl upraven.', 'success');
		
		if(!$this->isAjax())
			$this->redirect('Languages:default');
	} 
	
	public function renderTranslates(){
		$this->reloadContent();
	}
}