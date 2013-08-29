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
		$grid->addColumn('name', 'Název');
		
		return $grid;
	}
}