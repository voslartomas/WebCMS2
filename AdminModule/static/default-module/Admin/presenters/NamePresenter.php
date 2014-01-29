<?php

namespace AdminModule\#Name#Module;

/**
 * Description of
 *
 * @author #Author# <#AuthorEmail#>
 */
class #Name#Presenter extends BasePresenter {

    protected function startup() {
	parent::startup();
    }

    protected function beforeRender() {
	parent::beforeRender();

    }

    public function actionDefault($idPage){
    }

    public function renderDefault($idPage){
	$this->reloadContent();

	$this->template->idPage = $idPage;
    }
}