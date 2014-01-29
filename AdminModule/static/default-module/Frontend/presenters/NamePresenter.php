<?php

namespace FrontendModule\#Name#Module;

/**
 * Description of
 *
 * @author #Author# <#AuthorEmail#>
 */
class #Name#Presenter extends \FrontendModule\BasePresenter{
	
    protected function startup() {
	parent::startup();
    }

    protected function beforeRender() {
	parent::beforeRender();	
    }
	
    public function actionDefault($id){

    }
	
    public function renderDefault($id){

	$this->template->id = $id;
    }
}