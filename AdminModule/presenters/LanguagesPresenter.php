<?php

namespace AdminModule;

/**
 * Languages and translations presenter.
 * @author Tomáš Voslař <tomas.voslar at webcook.cz>
 * @package WebCMS2
 */
class LanguagesPresenter extends \AdminModule\BasePresenter{
	
	/* @var Language */
	private $language;
	
	/* LANGUAGES */
	
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
		$form->addText('name', 'Name')->setAttribute('class', 'form-control');
		$form->addText('abbr', 'Abbreviation')->setAttribute('class', 'form-control');
		$form->addCheckbox('defaultFrontend', 'Default fe')->setAttribute('class', 'form-control');
		$form->addCheckbox('defaultBackend', 'Default be')->setAttribute('class', 'form-control');
		$form->addUpload('import', 'Import lang');
		$form->addSubmit('save', 'Save')->setAttribute('class', 'btn btn-success');
		
		$form->onSuccess[] = callback($this, 'languageFormSubmitted');
		
		if($this->language) 
			$form->setDefaults($this->language->toArray());

		return $form;
	}
	
	protected function createComponentGrid($name){
		
		$grid = $this->createGrid($this, $name, "Language");
		
		$grid->addColumn('name', 'Name')->setSortable();
		$grid->addColumnText('abbr', 'Abbreviation')->setSortable();
		$grid->addColumn('defaultFrontend', 'Default fe')->setReplacement(array(
			'1' => 'Yes',
			NULL => 'No'
		));
		$grid->addColumn('defaultBackend', 'Default be')->setReplacement(array(
			'1' => 'Yes',
			NULL => 'No'
		));
		
		$grid->addAction("exportLanguage", 'Export')->getElementPrototype()->addAttributes(array('class' => 'btn btn-primary'));
		$grid->addAction("updateLanguage", 'Edit')->getElementPrototype()->addAttributes(array('class' => 'btn btn-primary ajax', 'data-toggle' => 'modal', 'data-target' => '#myModal', 'data-remote' => 'false'));
		$grid->addAction("deleteLanguage", 'Delete')->getElementPrototype()->addAttributes(array('class' => 'btn btn-danger', 'data-confirm' => 'Are you sure you want to delete the item?'));

		return $grid;
	}
	
	/**
	 * Export language into JSON file and terminate response for download it.
	 * @param Int $id 
	 */
	public function actionExportLanguage($id){
		$language = $this->em->find("AdminModule\Language", $id);
		
		$export = array(
			'name' => $language->getName(),
			'abbr' => $language->getAbbr(),
			'translations' => array()
		);
		
		foreach($language->getTranslations() as $translation){
			$export['translations'][] = array(
				'key' => $translation->getKey(),
				'translation' => $translation->getTranslation(),
				'backend' => $translation->getBackend()
			);
		}
		
		$export = json_encode($export);
		$filename = $language->getAbbr() . '.json';

		$response = $this->getHttpResponse();
		$response->setHeader('Content-Description', 'File Transfer');
		$response->setContentType('text/plain', 'UTF-8');
		$response->setHeader('Content-Disposition', 'attachment; filename=' . $filename);
		$response->setHeader('Content-Transfer-Encoding', 'binary');
		$response->setHeader('Expires', 0);
		$response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0');
		$response->setHeader('Pragma', 'public');
		$response->setHeader('Content-Length', strlen($export));

		ob_clean();
		flush();
		echo $export;

		$this->terminate();
	}
	
	public function importLanguage($fileData, $language){
		$data = json_decode($fileData, TRUE);
		
		$name = $language->getName();
		if(empty($name))
			$language->setName($data['name']);
		
		$translations = array();
		foreach($data['translations'] as $translation){
			$t = new Translation();
			$t->setLanguage($language);
			$t->setKey($translation['key']);
			$t->setTranslation($translation['translation']);
			$t->setBackend($translation['backend']);
			
			if(!$this->translationExists($t)){
				$this->em->persist($t);
				$translations[] = $t;
			}
		}

		$this->em->persist($language);	
		$this->em->flush();
	}
	
	private function translationExists($translation){
		$exists = $this->em->getRepository('AdminModule\Translation')->findBy(array(
			'language' => $translation->getLanguage(),
			'key' => $translation->getKey()
		));
		
		return $exists ? TRUE : FALSE;
	}
	
	public function actionUpdateLanguage($id){
		if($id) $this->language = $this->em->find("AdminModule\Language", $id);
		else $this->language = new Language();
	}
	
	public function actionDeleteLanguage($id){
		$this->language = $this->em->find("AdminModule\Language", $id);
		$this->em->remove($this->language);
		$this->em->flush();
		
		$this->flashMessage($this->translation['Language has been removed.'], 'success');
		
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
		
		if($values->import->getTemporaryFile()){
			$qb = $this->em->createQueryBuilder();
			$qb->delete('AdminModule\Translation', 'l')
					->where('l.language = ?1')
					->setParameter(1, $this->language)
					->getQuery()
					->execute();
			$this->em->flush();
			
			$content = file_get_contents($values->import->getTemporaryFile());
			$this->importLanguage($content, $this->language);
		}
			
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

		$this->flashMessage($this->translation['Language has been added.'], 'success');
		
		if(!$this->isAjax())
			$this->redirect('Languages:default');
		else{
			$this->invalidateControl('header');
		}
	} 
	
	/* TRANSLATIONS */
	
	public function renderTranslates(){
		$this->reloadContent();
	}
	
	protected function createComponentTranslationGrid($name){
		
		$grid = $this->createGrid($this, $name, "Translation");
		
		$languages = $this->em->getRepository('AdminModule\Language')->findAll();
		
		$langs = array('' => $this->translation['Pick a language']);
		foreach($languages as $l){
			$langs[$l->getId()] = $l->getName();
		}
		
		$grid->addColumn('id', 'ID')->setSortable()->setFilter();
		$grid->addColumn('key', 'Key')->setSortable()->setFilter();
		$grid->addColumnText('translation', 'Value')->setSortable()->setCustomRender(function($item){
			return '<div class="translation" contentEditable>' . $item->getTranslation() . '</div>';
		});
		$grid->addColumnText('language', 'Language')->setCustomRender(function($item){
			return $item->getLanguage()->getName();
		})->setSortable()->setFilterSelect($langs);
		$grid->setFilterRenderType(\Grido\Components\Filters\Filter::RENDER_OUTER);
		return $grid;
	}
	
	public function handleUpdateTranslation($idTranslation, $value){
		
		$translation = $this->em->find('AdminModule\Translation', trim($idTranslation));
		$translation->setTranslation(trim($value));
		
		$this->em->persist($translation);
		$this->em->flush();
		
		$this->flashMessage($this->translation['Translation has been added.'], 'success');
		
		$this->invalidateControl('flashMessages');
		
		if(!$this->isAjax())
			$this->redirect('Languages:Translates');
	}
}