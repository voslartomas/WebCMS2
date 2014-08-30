<?php

namespace AdminModule;

use Nette\Application\UI;

/**
 * Languages and translations presenter.
 * @author Tomáš Voslař <tomas.voslar at webcook.cz>
 * @package WebCMS2
 */
class LanguagesPresenter extends BasePresenter
{
    /* @var Language */
    private $lang;

    /* @var \Webcook\Translator\ITranslator */
    private $translatorService;

    protected function beforeRender()
    {
        parent::beforeRender();
    }

    protected function startup()
    {
        parent::startup();
    }

    public function renderDefault()
    {
        $this->reloadContent();
    }

    protected function createComponentLanguageForm()
    {
        $locales = \WebCMS\Locales::getSystemLocales();
        $translationFiles = \WebCMS\Helpers\SystemHelper::getTranslationFiles();
        $files = array('Pick a file');

        foreach ($translationFiles as $f) {
            $files[$f] = $f;
        }

        $form = $this->createForm();
        $form->addText('name', 'Name')->setAttribute('class', 'form-control');
        $form->addText('abbr', 'Abbreviation')->setAttribute('class', 'form-control');
        $form->addSelect('locale', 'Locale')->setItems($locales)->setAttribute('class', 'form-control');
        $form->addCheckbox('defaultFrontend', 'Default fe');
        $form->addCheckbox('defaultBackend', 'Default be');
        $form->addSelect('import', 'Import translation', $files)->setAttribute('class', 'form-control');
        $form->addSubmit('save', 'Save')->setAttribute('class', 'btn btn-success');

        $form->onSuccess[] = callback($this, 'languageFormSubmitted');

        if ($this->lang) {
            $form->setDefaults($this->lang->toArray());
        }

        return $form;
    }

    public function languageFormSubmitted(UI\Form $form)
    {
        $values = $form->getValues();

        $this->lang->setName($values->name);
        $this->lang->setAbbr($values->abbr);
        $this->lang->setLocale($values->locale);
        $this->lang->setDefaultFrontend($values->defaultFrontend);
        $this->lang->setDefaultBackend($values->defaultBackend);

        $this->em->persist($this->lang);
        $this->em->flush();

        if ($values->import) {
            $file = \WebCMS\Helpers\SystemHelper::WEBCMS_PATH . 'AdminModule/static/translations/' . $values->import;

            $content = file_get_contents($file);
            $this->importLanguage($content, $this->lang);
        }

        // only one item can be default
        if ($values->defaultFrontend) {
            $qb = $this->em->createQueryBuilder();
            $qb->update('WebCMS\Entity\Language', 'l')
            ->set('l.defaultFrontend', 0)
            ->where('l.id <> ?1')
            ->setParameter(1, $this->lang->getId())
            ->getQuery()
            ->execute();
            $this->em->flush();
        }

        if ($values->defaultBackend) {
            $qb = $this->em->createQueryBuilder();
            $qb->update('WebCMS\Entity\Language', 'l')
            ->set('l.defaultBackend', 0)
            ->where('l.id <> ?1')
            ->setParameter(1, $this->lang->getId())
            ->getQuery()
            ->execute();
            $this->em->flush();
        }

        $this->flashMessage('Language has been added.', 'success');

        if (!$this->isAjax())
            $this->redirect('Languages:default');
        else {
            $this->invalidateControl();
            $this->forward('Languages:default');
        }
    }

    protected function createComponentGrid($name)
    {
        $grid = $this->createGrid($this, $name, "Language");

        $grid->addColumnText('name', 'Name')->setSortable();
        $grid->addColumnText('abbr', 'Abbreviation')->setSortable();
        $grid->addColumnText('defaultFrontend', 'Default fe')->setReplacement(array(
            '1' => 'Yes',
            NULL => 'No'
        ));
        $grid->addColumnText('defaultBackend', 'Default be')->setReplacement(array(
            '1' => 'Yes',
            NULL => 'No'
        ));

        $grid->addActionHref("exportLanguage", 'Export')->getElementPrototype()->addAttributes(array('class' => array('btn', 'btn-primary')));
        $grid->addActionHref("updateLanguage", 'Edit')->getElementPrototype()->addAttributes(array('class' => array('btn', 'btn-primary', 'ajax'), 'data-toggle' => 'modal', 'data-target' => '#myModal', 'data-remote' => 'false'));
        $grid->addActionHref("deleteLanguage", 'Delete')->getElementPrototype()->addAttributes(array('class' => array('btn', 'btn-danger'), 'data-confirm' => 'Are you sure you want to delete the item?'));

        return $grid;
    }

    /**
     * Export language into JSON file and terminate response for download it.
     * @param Int $id
     */
    public function actionExportLanguage($id)
    {
        $language = $this->em->find("WebCMS\Entity\Language", $id);

        $export = array(
            'name' => $language->getName(),
            'abbr' => $language->getAbbr(),
            'translations' => array()
        );

        foreach ($language->getTranslations() as $translation) {
            if ($translation->getBackend()) {
            $export['translations'][] = array(
                'key' => $translation->getKey(),
                'translation' => $translation->getTranslation(),
                'backend' => $translation->getBackend()
            );
            }
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

    /**
     * @param string $fileData
     */
    public function importLanguage($fileData, $language)
    {
        $data = json_decode($fileData, TRUE);

        $name = $language->getName();
        if (empty($name))
            $language->setName($data['name']);

        $translations = array();
        foreach ($data['translations'] as $translation) {
            $t = new \WebCMS\Entity\Translation();
            $t->setLanguage($language);
            $t->setKey($translation['key']);
            $t->setTranslation($translation['translation']);
            $t->setBackend($translation['backend']);
            $t->setHash();

            $exists = $this->translationExists($t);
            if (!$exists) {
                if (!array_key_exists($t->getHash(), $translations)) {
                $this->em->persist($t);
                $translations[$t->getHash()] = $t;
                }
            } else {
            $exists->setHash();
            $exists->setTranslation($translation['translation']);
            }
        }

        $this->em->persist($language);
        $this->em->flush();

        // reload actual translations
        $default = $this->em->getRepository('WebCMS\Entity\Language')->findOneBy(array(
            'defaultBackend' => 1
        ));

        $translation = new \WebCMS\Translation\Translation($this->em, $default, 1);
        $this->translation = $translation->getTranslations();

        $this->translator = new \WebCMS\Translation\Translator($this->translation);
    }

    /**
     * @param \WebCMS\Entity\Translation $translation
     */
    private function translationExists($translation)
    {
        $exists = $this->em->getRepository('WebCMS\Entity\Translation')->findOneBy(array(
            'hash' => $translation->getHash()
        ));

        if (is_object($exists)) {
            return $exists;
        } else {
            return FALSE;
        }
    }

    public function actionUpdateLanguage($id)
    {
        if ($id) {
            $this->lang = $this->em->find("WebCMS\Entity\Language", $id);
        } else {
            $this->lang = new \WebCMS\Entity\Language();
        }
    }

    public function actionDeleteLanguage($id)
    {
        $this->lang = $this->em->find("WebCMS\Entity\Language", $id);
        $this->em->remove($this->lang);
        $this->em->flush();

        $this->flashMessage('Language has been removed.', 'success');
        $this->forward('Languages:default');
    }

    public function renderUpdateLanguage($id)
    {
        $this->reloadModalContent();
        $this->template->language = $this->lang;
    }
}