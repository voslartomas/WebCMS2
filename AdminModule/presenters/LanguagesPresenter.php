<?php

namespace AdminModule;

/**
 * Languages and translations presenter.
 * @author Tomáš Voslař <tomas.voslar at webcook.cz>
 * @package WebCMS2
 */
class LanguagesPresenter extends \AdminModule\BasePresenter{

	protected function beforeRender(){
		parent::beforeRender();
		
	}
	
	protected function startup(){		
		parent::startup();
	}
	
	public function renderDefault(){
		
	}
	
	protected function createComponentGrid($name){
		
		$qb = $this->em->createQueryBuilder();
		
		$grid = new \Grido\Grid($this, $name);
		$grid->setModel($qb->select('l')->from('AdminModule\Language', 'l'));
		$grid->addColumn('name', 'Název')->setSortable();
		$grid->addColumnText('abbr', "Zkratka")->setSortable();
		$grid->addColumn('default', "Výchozí")->setReplacement(array(
			'1' => 'Ano',
			NULL => 'Ne'
		));
		
		$grid->addFilter('name', "Název");
		$grid->addAction("edit", "Upravit")->getElementPrototype()->addAttributes(array('class' => 'btn btn-primary ajax'));
		$grid->addAction("delete", "Smazat")->getElementPrototype()->addAttributes(array('class' => 'btn btn-danger ajax'));
		$grid->setRememberState();
		$grid->setExporting();
		
		return $grid;
	}
	
	public function renderTranslates(){
		
	}
}