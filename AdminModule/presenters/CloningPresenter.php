<?php

/**
 * Webcms2 admin module package.
 */

namespace AdminModule;

use Nette\Application\UI;

/**
 * @author Tomáš Voslař <tomas.voslar at webcook.cz>
 * @package WebCMS2
 */
class CloningPresenter extends BasePresenter
{
	public function renderDefault()
    {
        $this->reloadContent();
    }

    public function createComponentCloningForm()
    {
        $form = $this->createForm();

        $langs = $this->getAllLanguages();
        $packages = \WebCMS\Helpers\SystemHelper::getPackages();

        $form->addGroup('Copy structures');

        $form->addSelect('languageFrom', 'Copy from', $langs)->setRequired('Please pick up language.')->setAttribute('class', 'form-control');
        $form->addSelect('languageTo', 'Copy to', $langs)->setRequired('Please pick up language.')->setAttribute('class', 'form-control');
        $form->addCheckbox('removeData', 'Remove data?');

        $form->addGroup('Copy data from modules');

        foreach ($packages as $key => $package) {

            if ($package['vendor'] === 'webcms2' && $package['package'] !== 'webcms2') {
            $object = $this->createObject($package['package']);

            if ($object->isCloneable()) {
                $form->addCheckbox(str_replace('-', '_', $package['package']), $package['package']);
            } else {
                $form->addCheckbox(str_replace('-', '_', $package['package']), $package['package'] . ' not clonable.')->setDisabled(true);
            }
            }
        }

        $form->onSuccess[] = callback($this, 'cloningFormSubmitted');
        $form->addSubmit('send', 'Clone');

        return $form;
    }

    public function cloningFormSubmitted(UI\Form $form)
    {
        $values = $form->getValues();

        $languageFrom = $this->em->getRepository('WebCMS\Entity\Language')->find($values->languageFrom);
        $languageTo = $this->em->getRepository('WebCMS\Entity\Language')->find($values->languageTo);
        $removeData = $values->removeData;
        unset($values->languageFrom);
        unset($values->languageTo);
        unset($values->removeData);

        // remove data first
        if ($removeData) {
            $this->removeData();
        }

        // clone page structure
        $transformTable = array();

        $pages = $this->em->getRepository('WebCMS\Entity\Page')->findBy(array(
            'language' => $languageFrom
            ), array('lft' => 'asc'));

        foreach ($pages as $page) {
            $new = $this->createNewPage($languageTo, $page);

            if ($page->getParent()) {
            	$new->setParent($transformTable[$page->getParent()->getId()]);
            }

            $this->em->persist($new);
            $this->em->flush();

            $path = $this->em->getRepository('WebCMS\Entity\Page')->getPath($new);
            $final = array();
            foreach ($path as $p) {
	            if ($p->getParent() != NULL) {
	                $final[] = $p->getSlug();
	            }
            }

            $new->setPath(implode('/', $final));
            $this->em->flush();

            $transformTable[$page->getId()] = $new;
        }

        foreach ($pages as $page) {
            $boxes = $this->em->getRepository('WebCMS\Entity\Box')->findBy(array(
            	'pageTo' => $page
            ));

            foreach ($boxes as $box) {
            	$this->createNewBox($box);
            	$this->em->persist($newBox);
            }
        }

        // clone all data
        foreach ($values as $key => $value) {
            if ($value) {
	            $module = $this->createObject(str_replace('_', '-', $key));
	            if ($module->isCloneable()) {
	                $module->cloneData($this->em, $languageFrom, $languageTo, $transformTable);
	            }
            }
        }

        $this->em->flush();

        $this->flashMessage('Cloning has been successfuly done.', 'success');
        if (!$this->isAjax()) {
            $this->forward('Languages:cloning');
        }
    }

    /**
     * 
     */
    private function removeData()
    {
    	$pages = $this->em->getRepository('WebCMS\Entity\Page')->findBy(array(
            'language' => $languageTo,
            'parent' => NULL
        ));

        foreach ($pages as $page) {
        	$this->em->remove($page);
        }
    }

    /**
     * 
     */
    private function createNewPage($languageTo, $page)
    {
    	$new = new \WebCMS\Entity\Page;
        $new->setLanguage($languageTo);
        $new->setTitle($page->getTitle());
        $new->setPresenter($page->getPresenter());
        $new->setPath('tmp');
        $new->setVisible($page->getVisible());
        $new->setDefault($page->getDefault());
        $new->setClass($page->getClass());
        $new->setModule($page->getModule());
        $new->setModuleName($page->getModuleName());

        return $new;
    }

    private function createNewBox($box)
    {
    	$newBox = new \WebCMS\Entity\Box();
        $newBox->setBox($box->getBox());
        $newBox->setFunction($box->getFunction());
        $newBox->setModuleName($box->getModuleName());
        $newBox->setPresenter($box->getPresenter());
        $newBox->setPageFrom($transformTable[$box->getPageFrom()->getId()]);
        $newBox->setPageTo($transformTable[$box->getPageTo()->getId()]);

        return $newBox;
    }
}