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
		$form->addText('name', 'Název');
		$form->addText('abbr', 'Zkratka');
		$form->addCheckbox('defaultFrontend', 'Výchozí fe');
		$form->addCheckbox('defaultBackend', 'Výchozí be');
		$form->addSubmit('save', 'Uložit')->setAttribute('class', 'btn btn-success');
		
		$form->onSuccess[] = callback($this, 'languageFormSubmitted');
		
		if($this->language) 
			$form->setDefaults($this->language->toArray());

		return $form;
	}
	
	protected function createComponentGrid($name){
		
		$grid = $this->createGrid($this, $name, "Language");
		
		$grid->addColumn('name', 'Název')->setSortable();
		$grid->addColumnText('abbr', "Zkratka")->setSortable();
		$grid->addColumn('defaultFrontend', "Výchozí fe")->setReplacement(array(
			'1' => 'Ano',
			NULL => 'Ne'
		));
		$grid->addColumn('defaultBackend', "Výchozí be")->setReplacement(array(
			'1' => 'Ano',
			NULL => 'Ne'
		));
		
		$grid->addAction("updateLanguage", "Upravit")->getElementPrototype()->addAttributes(array('class' => 'btn btn-primary ajax', 'data-toggle' => 'modal', 'data-target' => '#myModal', 'data-remote' => 'false'));
		$grid->addAction("deleteLanguage", "Smazat")->getElementPrototype()->addAttributes(array('class' => 'btn btn-danger', 'data-confirm' => 'Opravdu smazat položku?'));

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