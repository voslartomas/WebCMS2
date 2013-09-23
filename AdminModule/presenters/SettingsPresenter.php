<?php

namespace AdminModule;

/**
 * Settings presenter.
 * @author Tomáš Voslař <tomas.voslar at webcook.cz>
 * @package WebCMS2
 */
class SettingsPresenter extends \AdminModule\BasePresenter{
	
	/* @var Thumbnail */
	private $thumbnail;
	
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
		
		// global settings for all languages
		$this->settings->setLanguage(NULL);
		
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
		
		// set back language for further settings in app
		$this->settings->setLanguage($this->state->language);
		
		return $this->createSettingsForm($settings);
	}
	
	public function renderPictures($panel){
		$this->reloadContent();
		
		$this->template->panel = $panel;
	}
	
	public function actionAddThumbnail($id){
		if($id) $this->thumbnail = $this->em->find("AdminModule\Thumbnail", $id);
		else $this->thumbnail = new Thumbnail();
	}
	
	public function actionDeleteThumbnail($id){
		$this->thumbnail = $this->em->find("AdminModule\Thumbnail", $id);
		$this->em->remove($this->thumbnail);
		$this->em->flush();
		
		$this->flashMessage($this->translation['Thumbnail has been removed.'], 'success');
		
		if(!$this->isAjax())
			$this->redirect('Settings:pictures', array('panel' => 'thumbnails'));
	}
	
	public function renderAddThumbnail($id){
		
		$this->reloadModalContent();
		
		$this->template->thumbnail = $this->thumbnail;
	}
	
	public function createComponentThumbnailForm(){
		
		$form = $this->createForm();
		
		$form->addText('key', 'Key');
		$form->addText('x', 'Width');
		$form->addText('y', 'Height');
		$form->addCheckbox('watermark', 'Watermark?');
		
		if(\WebCMS\SystemHelper::isSuperAdmin($this->user))
			$form->addCheckbox('system', 'System?');
		else
			$form->addHidden('system', 'System?');
		
		$form->addSubmit('submit', 'Save');
		
		$form->onSuccess[] = callback($this, 'thumbnailFormSubmitted');
		$form->setDefaults($this->thumbnail->toArray());
		
		return $form;
	}
	
	public function thumbnailFormSubmitted(\Nette\Forms\Form $form){
		$values = $form->getValues();
		
		if(!$this->thumbnail->getId())
			$thumb = new Thumbnail;
		else 
			$thumb = $this->thumbnail;
		
		if(!\WebCMS\SystemHelper::isSuperAdmin($this->user) && $thumb->getSystem()){
			$this->flashMessage('You do not have a permission to do this operation.', 'danger');
			$this->redirect('Settings:pictures', array('panel' => 'thumbnails'));
		}
		
		$thumb->setKey($values->key);
		$thumb->setX($values->x);
		$thumb->setY($values->y);
		$thumb->setWatermark($values->watermark);
		$thumb->setSystem($values->system);
		
		$this->em->persist($thumb);
		$this->em->flush();
		
		$this->flashMessage('Thumbnail setting was added.', 'success');
		
		if(!$this->isAjax())
			$this->redirect('Settings:pictures', array('panel' => 'thumbnails'));
	}
	
	protected function createComponentThumbnailGrid($name){
		
		$grid = $this->createGrid($this, $name, "Thumbnail");
		
		$grid->addColumn('key', 'Key');
		$grid->addColumnText('x', 'Width');
		$grid->addColumnText('y', 'Height');
		
		$grid->addColumn('watermark', 'Watermark')->setReplacement(array(
			1 => 'Yes',
			NULL => 'No'
		));
		
		$grid->addColumn('system', 'System')->setReplacement(array(
			1 => 'Yes',
			NULL => 'No'
		));
		
		$grid->addAction("addThumbnail", 'Edit')->getElementPrototype()->addAttributes(array('class' => 'btn btn-primary ajax', 'data-toggle' => 'modal', 'data-target' => '#myModal', 'data-remote' => 'false'));
		$grid->addAction("deleteThumbnail", 'Delete')->getElementPrototype()->addAttributes(array('class' => 'btn btn-danger', 'data-confirm' => 'Are you sure you want to delete this item?'));

		return $grid;
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