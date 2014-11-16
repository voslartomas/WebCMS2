<?php

namespace AdminModule;

use Nette\Application\UI;
use Nette\Utils\Finder;

/**
 * Admin presenter.
 * @author Tomáš Voslař <tomas.voslar at webcook.cz>
 * @package WebCMS2
 */
class PagesPresenter extends \AdminModule\BasePresenter
{
    /* @var Page */

    private $page;

    private $repository;

    protected function beforeRender()
    {
        parent::beforeRender();
    }

    protected function startup()
    {
        parent::startup();

        $this->repository = $this->em->getRepository('WebCMS\Entity\Page');
    }

    protected function createComponentPageForm()
    {
        $repository = $this->em->getRepository('WebCMS\Entity\Page');
        $hierarchy = $repository->getTreeForSelect(array(
            array('by' => 'root', 'dir' => 'ASC'),
            array('by' => 'lft', 'dir' => 'ASC'),
            ), array(
            'language = '.$this->state->language->getId(),
        ));

        $hierarchy = array(0 => $this->translation['Pick parent']) + $hierarchy;

        // loads modules
        $modules = $this->em->getRepository('WebCMS\Entity\Module')->findAll();
        $modulesToSelect = array(NULL => 'No module');
        foreach ($modules as $module) {
            $objectModule = $this->createObject($module->getName());

            foreach ($objectModule->getPresenters() as $presenter) {
                if ($presenter['frontend']) {
                    $modulesToSelect[$module->getId().'-'.$presenter['name']] = $module->getName().' '.$presenter['name'];
                }
            }
        }

        $layouts = array();
        foreach (Finder::findFiles('@*.latte')->in(APP_DIR.'/templates') as $key => $file) {
            $layouts[str_replace(array('@', '.latte'), '', $file->getFileName())] = $file->getFileName();
        }

        $form = $this->createForm();
        $form->addText('title', 'Name')->setAttribute('class', 'form-control')->setRequired();
        $form->addText('redirect', 'Redirect')->setAttribute('class', 'form-control');
        $form->addText('class', 'Menu item class')->setAttribute('class', 'form-control');
        $form->addSelect('module', 'Module')->setTranslator(null)->setItems($modulesToSelect)->setAttribute('class', 'form-control')->setRequired();
        $form->addSelect('parent', 'Parent')->setTranslator(null)->setItems($hierarchy)->setAttribute('class', 'form-control');
        $form->addSelect('layout', 'Page layout')->setTranslator(null)->setItems($layouts)->setAttribute('class', 'form-control');
        $form->addCheckbox('default', 'Default');
        $form->addCheckbox('visible', 'Show');

        $form->addSubmit('save', 'Save')->setAttribute('class', 'btn btn-success');

        $form->onSuccess[] = callback($this, 'pageFormSubmitted');

        if ($this->page) {
            $form->setDefaults($this->page->toArray());
            if (is_object($this->page->getModule())) {
                $form['module']->setDefaultValue($this->page->getModule()->getId().'-'.$this->page->getPresenter());
            }
        }

        return $form;
    }

    public function pageFormSubmitted(UI\Form $form)
    {
        $values = $form->getValues();

        $tmpBoxes = array();
        if ($values->parent) {
            $parent = $this->em->find("WebCMS\Entity\Page", $values->parent);
            // copy boxes
            $tmpBoxes = $parent->getBoxes();
        } else {
            $parent = null;
        }

        $this->setPageValues($values, $parent);

        if (!$this->page->getId()) {
            $this->em->persist($this->page);

            $this->createBoxesFromParent($tmpBoxes);

            $this->em->flush();
            $this->copyPermissions();
        }

        // persist and generate path
        $this->em->flush();
        $this->generatePath();
        $this->em->flush();
        $this->generateSitemap();

        $this->flashMessage('Page has been added.', 'success');
        $this->forward('Pages:default');
    }

    /**
     *
     *
     * @param [type] $values [description]
     */
    private function setPageValues($values, $parent)
    {
        if ($values->module) {
            $parse = explode('-', $values->module);
            $module = $this->em->find("WebCMS\Entity\Module", $parse[0]);
            $presenter = $parse[1];
        } else {
            $module = null;
            $presenter = '';
        }

        $this->page->setTitle($values->title);

        if (!empty($values->redirect)) {
            $this->page->setRedirect($values->redirect);
        } else {
            $this->page->setRedirect(null);
        }

        if ($module) {
            $this->page->setModuleName($module->getName());
        } else {
            $this->page->setModuleName('');
        }

        $this->page->setVisible($values->visible);
        $this->page->setDefault($values->default);
        $this->page->setParent($parent);
        $this->page->setLanguage($this->state->language);
        $this->page->setModule($module);
        $this->page->setPresenter($presenter);
        $this->page->setPath('tmp value');
        $this->page->setClass($values->class);
        $this->page->setLayout($values->layout);
    }

    /**
     *
     *
     * @param  [type] $boxes [description]
     * @return [type] [description]
     */
    private function createBoxesFromParent($boxes)
    {
        foreach ($boxes as $box) {
            $tmp = new \WebCMS\Entity\Box();
            $tmp->setBox($box->getBox());
            $tmp->setFunction($box->getFunction());
            $tmp->setModuleName($box->getModuleName());
            $tmp->setPresenter($box->getPresenter());
            $tmp->setPageFrom($box->getPageFrom());
            $tmp->setPageTo($this->page);

            $this->em->persist($tmp);
        }
    }

    /**
     *
     *
     * @return [type] [description]
     */
    private function generatePath()
    {
        $path = $this->em->getRepository('WebCMS\Entity\Page')->getPath($this->page);
        $final = array();
        foreach ($path as $p) {
            if ($p->getParent() != NULL) {
                $final[] = $p->getSlug();
            }
        }

        $this->page->setPath(implode('/', $final));
    }

    /**
     *
     *
     * @return [type] [description]
     */
    private function copyPermissions()
    {
        $roles = $this->em->getRepository('WebCMS\Entity\Role')->findBy(array(
            'automaticEnable' => true,
        ));

        foreach ($roles as $r) {
            $module = $this->createObject($this->page->getModuleName());
            foreach ($module->getPresenters() as $presenter) {
                $permission = new \WebCMS\Entity\Permission();

                $resource = 'admin:'.$this->page->getModuleName().$presenter['name'].$this->page->getId();
                $permission->setResource($resource);
                $permission->setPage($this->page);
                $permission->setRead(true);

                $r->addPermission($permission);
            }
        }
    }

    protected function createComponentPagesGrid($name)
    {
        $parents = $this->em->getRepository('WebCMS\Entity\Page')->findBy(array(
            'parent' => NULL,
            'language' => $this->state->language->getId(),
        ));

        $prnts = array('' => $this->translation['Pick structure']);

        foreach ($parents as $p) {
            $prnts[$p->getId()] = $p->getTitle();
        }

        $grid = $this->createGrid($this, $name, 'Page', array(
            array('by' => 'root', 'dir' => 'ASC'),
            array('by' => 'lft', 'dir' => 'ASC'),
            ), array(
            'language = '.$this->state->language->getId(),
            )
        );

        $grid->addColumnText('title', 'Name')->setCustomRender(function ($item) {
            return str_repeat("-", $item->getLevel()).$item->getTitle();
        });

        $grid->addColumnText('root', 'Structure')->setCustomRender(function ($item) {
            return $item->getParent() ? $item->getParent() : '-';
        });

        $grid->addFilterSelect('root', 'Structure')->getControl()->setTranslator(null)->setItems($prnts);

        $grid->addColumnText('moduleName', 'Module');

        $grid->addColumnText('visible', 'Visible')->setReplacement(array(
            '1' => 'Yes',
            NULL => 'No',
        ));

        $grid->addColumnText('default', 'Default')->setReplacement(array(
            '1' => 'Yes',
            NULL => 'No',
        ));

        //$grid->addActionHref("moveUp", "Move up");
        //$grid->addActionHref("moveDown", "Move down");
        $grid->addActionHref("updatePage", 'Edit')->getElementPrototype()->addAttributes(array('class' => array('btn', 'btn-primary', 'ajax')));
        $grid->addActionHref("deletePage", 'Delete')->getElementPrototype()->addAttributes(array('class' => array('btn', 'btn-danger'), 'data-confirm' => 'Are you sure you want to delete this item?'));

        return $grid;
    }

    public function actionUpdatePage($id)
    {
        if ($id) {
            $this->page = $this->em->find("WebCMS\Entity\Page", $id);
        } else {
            $this->page = new \WebCMS\Entity\Page();
        }
    }

    public function actionDeletePage($id)
    {
        $this->page = $this->em->find("WebCMS\Entity\Page", $id);
        $this->em->remove($this->page);
        $this->em->flush();

        $this->flashMessage('Page has been removed.', 'success');

        $this->forward('Pages:default');
    }

    public function renderUpdatePage($id)
    {
        $this->reloadContent();

        $this->template->page = $this->page;
    }

    public function renderDefault()
    {
        $this->reloadContent();
    }

    // Sorting
    public function renderSorting($id)
    {
        $this->reloadContent();

        $roots = $this->repository->findBy(array(
            'parent' => NULL,
            'language' => $this->state->language->getId(),
        ));

        $tree = array();
        foreach ($roots as $r) {
            $tmp = array();
            $tmp['title'] = $r->getTitle();
            $tmp['__children'] = $this->repository->childrenHierarchy($r);

            $tree[] = $tmp;
        }

        $this->template->tree = $tree;
    }

    public function actionMove($id, $oldPosition, $newPosition)
    {
        $step = $newPosition - $oldPosition;

        if ($step > 0) {
            $this->actionMoveDown($id, $step);
        } else {
            $this->actionMoveUp($id, $step * -1);
        }

        $this->forward('Pages:sorting');
    }

    public function actionMoveUp($id, $step = 1)
    {
        $this->page = $this->em->find("WebCMS\Entity\Page", $id);

        if ($this->page->getParent()) {
            $repository = $this->em->getRepository('WebCMS\Entity\Page');
            $repository->moveUp($this->page, $step);

            $this->flashMessage('Page has been moved up.', 'success');
        } else {
            $this->flashMessage('Page has not been moved up, because it is root page.', 'warning');
        }

        if (!$this->isAjax()) {
            $this->forward('Pages:default');
        }
    }

    public function actionMoveDown($id, $step = 1)
    {
        $this->page = $this->em->find("WebCMS\Entity\Page", $id);

        if ($this->page->getParent()) {
            $repository = $this->em->getRepository('WebCMS\Entity\Page');
            $repository->moveDown($this->page, $step);

            $this->flashMessage('Page has been moved down.', 'success');
        } else {
            $this->flashMessage('Page has not been moved up, because it is root page.', 'warning');
        }

        if (!$this->isAjax()) {
            $this->forward('Pages:default');
        }
    }
}
