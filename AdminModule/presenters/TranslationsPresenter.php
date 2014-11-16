<?php

/**
 * Webcms2 admin module package.
 */

namespace AdminModule;

/**
 * @author Tomáš Voslař <tomas.voslar at webcook.cz>
 * @package WebCMS2
 */
class TranslationsPresenter extends BasePresenter
{
    public function renderDefault()
    {
        $this->reloadContent();
    }

    protected function createComponentTranslationGrid($name)
    {
        $grid = $this->createGrid($this, $name, "Translation");

        $langs = $this->getAllLanguages();

        $backend = array(
            '' => $this->translation['Pick filter'],
            0 => $this->translation['No'],
            1 => $this->translation['Yes'],
        );

        $grid->addColumnText('id', 'ID')->setSortable()->setFilterNumber();
        $grid->addColumnText('key', 'Key')->setSortable()->setFilterText();
        $grid->addColumnText('translation', 'Value')->setSortable()->setCustomRender(function ($item) {
            return '<div class="translation" contentEditable>'.$item->getTranslation().'</div>';
        });
        $grid->addColumnText('backend', 'Backend')->setReplacement(array(
            '1' => 'Yes',
            NULL => 'No',
        ))->setFilterSelect($backend);

        $grid->addColumnText('translated', 'Translated')->setReplacement(array(
            '1' => 'Yes',
            NULL => 'No',
        ))->setFilterSelect($backend);

        $grid->addColumnText('language', 'Language')->setCustomRender(function ($item) {
            return $item->getLanguage()->getName();
        })->setSortable();

        $grid->addFilterSelect('language', 'Language')->getControl()->setTranslator(null)->setItems($langs);

        $grid->addActionHref("deleteTranslation", 'Delete')->getElementPrototype()->addAttributes(array('class' => array('btn', 'btn-danger'), 'data-confirm' => 'Are you sure you want to delete the item?'));

        $grid->setFilterRenderType(\Grido\Components\Filters\Filter::RENDER_INNER);

        return $grid;
    }

    public function actionDeleteTranslation($id)
    {
        $translation = $this->em->find("WebCMS\Entity\Translation", $id);
        $this->em->remove($translation);
        $this->em->flush();

        $this->flashMessage('Translation has been removed.', 'success');

        $this->cleanCache();

        $this->forward('Languages:Translates');
    }

    public function handleUpdateTranslation($idTranslation, $value)
    {
        $value = strip_tags($value, '<strong><b><i><u>');

        $translation = $this->em->find('WebCMS\Entity\Translation', trim($idTranslation));
        $translation->setTranslation(trim($value));

        $this->em->persist($translation);
        $this->em->flush();

        $this->flashMessage('Translation has been added.', 'success');

        $this->invalidateControl('flashMessages');

        $this->forward('Languages:Translates');
    }

    public function handleRegenerateTranslations()
    {
        $translations = $this->em->getRepository('WebCMS\Entity\Translation')->findAll();

        foreach ($translations as $t) {
            $t->setTranslation($t->getTranslation());
        }

        $this->em->flush();
    }
}
