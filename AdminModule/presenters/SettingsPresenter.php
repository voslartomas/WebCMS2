<?php

namespace AdminModule;

/**
 * Settings presenter.
 * @author Tomáš Voslař <tomas.voslar at webcook.cz>
 * @package WebCMS2
 */
class SettingsPresenter extends \AdminModule\BasePresenter{

	protected function beforeRender(){
		parent::beforeRender();
	}
	
	protected function startup(){		
		parent::startup();
	}
	
	private function createSettingsForm($settings){
		$form = $this->createForm();
		
		if(!$settings){
			return $form;
		}
		
		foreach($settings as $s){
			$ident = $s->getId();
			
			if($s->getType() === 'text')
				$form->addText($ident, $s->getName())->setDefaultValue($s->getValue())->setAttribute('class', 'form-control');
			elseif($s->getType() === 'textarea')
				$form->addTextArea($ident, $s->getName())->setDefaultValue($s->getValue())->setAttribute('class', 'editor');
		}
		
		$form->addSubmit('submit', 'Save settings');
		$form->onSuccess[] = callback($this, 'settingsFormSubmitted');
		
		return $form;
	}
	
	public function settingsFormSubmitted(\Nette\Application\UI\Form $form){
		$values = $form->getValues();
		
		foreach($values as $key => $v){
			$setting = $this->em->find('AdminModule\Setting', $key);
			$setting->setValue($v);
		}
		
		$this->em->flush();
		
		$this->flashMessage($this->translation['Settings has been saved.'], 'success');
		$this->redirect('this');
	}
	
	/* BASIC */
	
	public function createComponentBasicSettingsForm(){
		return $this->createSettingsForm($this->settings->getSection('basic'));
	}
	
	public function renderDefault(){
		$this->reloadContent();
	}
	
	/* PICTURES */
	
	public function createComponentPicturesSettingsForm(){
		return $this->createSettingsForm($this->settings->getSection('pictures'));
	}
	
	public function renderPictures(){
		$this->reloadContent();
	}
	
	/* EMAILS */
	
	public function createComponentEmailsSettingsForm(){
		return $this->createSettingsForm($this->settings->getSection('emails'));
	}
	
	public function renderEmails(){
		$this->reloadContent();
	}
}