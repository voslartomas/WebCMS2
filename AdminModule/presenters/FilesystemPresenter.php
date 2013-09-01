<?php

namespace AdminModule;

/**
 * Filesystem presenter.
 * @author Tomáš Voslař <tomas.voslar at webcook.cz>
 * @package WebCMS2
 */
class FilesystemPresenter extends \AdminModule\BasePresenter{
	
	const DESTINATION_BASE = 'upload/';
	
	private $path;
	
	protected function beforeRender(){
		parent::beforeRender();
		
		$this->reloadContent();
	}
	
	protected function startup(){		
		parent::startup();
		
	}
	
	public function actionDefault($path){
		if(!empty($path)) $this->path = $path . '/';
		else $this->path = realpath(self::DESTINATION_BASE) . '/';
		
	}
	
	public function renderDefault($path){
		$finder = new \Nette\Utils\Finder();
		$files = $finder->findFiles('*')->in($this->path);
		$directories = $finder->findDirectories('*')->in($this->path);

		$this->template->backLink = strpos($this->createBackLink($this->path), self::DESTINATION_BASE) === false ? realpath(self::DESTINATION_BASE) : $this->createBackLink($this->path);
		$this->template->files = $files;
		$this->template->directories = $directories;
	}
	
	private function createBackLink($path){
		$exploded = explode('/', $path);

		array_pop($exploded);
		array_pop($exploded);

		return implode("/", $exploded);
	}
	
	public function handleMakeDirectory($name){
		
		mkdir($this->path . $name);
		
		$this->flashMessage($this->translation['Directory has been created.'], 'success');
	}
	
	public function handleUploadFile(){
		$file = new \Nette\Http\FileUpload($_FILES['files']);
		
		$file->move($this->path . '' . $file->getSanitizedName());
		
		$this->reloadContent();
		$this->flashMessage($this->translation['File has been uploaded']);
		
		$this->sendPayload();
	}
	// TODO odladit vyjimky
	public function handleDeleteFile($pathToRemove){
		if(is_file($pathToRemove))
			unlink($pathToRemove);
			
		if(is_dir($pathToRemove))
			rmdir($pathToRemove);
			// pokud neni prazdna vratit v odpovedi a potvrdit smazani veskereho obsahu
		$this->flashMessage($this->translation['File has been removed.'], 'success');
		$this->redirect('this');
	}
	
	public function handleDownloadFile($path){
		
	}
	
}