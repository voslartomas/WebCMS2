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
				$form->addText($ident, $s->getKey())->setDefaultValue($s->getValue())->setAttribute('class', 'form-control');
			elseif($s->getType() === 'textarea')
				$form->addTextArea($ident, $s->getKey())->setDefaultValue($s->getValue())->setAttribute('class', 'editor');
			elseif($s->getType() === 'radio')				
				$form->addRadioList($ident, $s->getKey(), $s->getOptions())->setDefaultValue($s->getValue());
			elseif($s->getType() === 'select')
				$form->addSelect($ident, $s->getKey(), $s->getOptions())->setDefaultValue($s->getValue());
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
		
		$settings = array();
		$settings[] = $this->settings->get('Info email', \WebCMS\Settings::SECTION_BASIC, 'text');
		
		return $this->createSettingsForm($settings);
	}
	
	public function renderDefault(){
		$this->reloadContent();
	}
	
	/* PICTURES */
	
	public function createComponentPicturesSettingsForm(){
		
		$settings = array();
		$settings[] = $this->settings->get('Apply watermark', \WebCMS\Settings::SECTION_IMAGE, 'radio', array(
			0 => 'Do not apply watermark',
			1 => 'Use picture as watermark',
			2 => 'Use text as watermark'
		));
		
		$settings[] = $this->settings->get('Watermark text', \WebCMS\Settings::SECTION_IMAGE, 'text');
		$settings[] = $this->settings->get('Watermark text size', \WebCMS\Settings::SECTION_IMAGE, 'text');
		$settings[] = $this->settings->get('Watermark text font', \WebCMS\Settings::SECTION_IMAGE, 'select', array(
			0 => 'Comic sans',
			1 => 'Arial',
			2 => 'Times new roman'
		));
		$settings[] = $this->settings->get('Watermark text color', \WebCMS\Settings::SECTION_IMAGE, 'text');
		
		$settings[] = $this->settings->get('Watermark position', \WebCMS\Settings::SECTION_IMAGE, 'radio', array(
			0 => 'Top left',
			1 => 'Top right',
			2 => 'Center',
			3 => 'Bottom left',
			4 => 'Bottom right'
		));
		
		return $this->createSettingsForm($settings);
	}
	
	public function renderPictures(){
		$this->reloadContent();
	}
	
	/* EMAILS */
	
	public function createComponentEmailsSettingsForm(){
		
		$settings = array();
		$settings[] = $this->settings->get('User new password', \WebCMS\Settings::SECTION_EMAIL, 'textarea');
		
		return $this->createSettingsForm($settings);
	}
	
	public function renderEmails(){
		$this->reloadContent();
	}
}