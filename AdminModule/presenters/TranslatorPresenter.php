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
class TranslatorPresenter extends BasePresenter
{
    /* @var \Webcook\Translator\ServiceFactory */
    private $serviceFactory;

    /* @var \Webcook\Translator\ITranslator */
    private $translatorService;

    public function renderDefault()
    {
        $this->reloadContent();
    }

    public function actionTranslator()
    {
        $this->serviceFactory = new \Webcook\Translator\ServiceFactory();

        try {
            $this->translatorService = $this->getTranslateService();
        } catch (Exception $exc) {
            $this->flashMessage($exc->getMessage(), 'danger');
        }

        if (!$this->translatorService instanceof \Webcook\Translator\ITranslator) {
            $this->flashMessage('You must fill in API key.', 'danger');
        }
    }

    private function getTranslateService()
    {
        $serviceId = $this->settings->get('Translate service', \WebCMS\Settings::SECTION_BASIC, 'select')->getValue();

        $this->serviceFactory->setSettings(array(
            \Webcook\Translator\ServiceFactory::YANDEX => array(
            'key' => $this->settings->get('Yandex API key', \WebCMS\Settings::SECTION_BASIC)->getValue(),
            ),
            \Webcook\Translator\ServiceFactory::GOOGLE => array(
            'key' => $this->settings->get('Google API key', \WebCMS\Settings::SECTION_BASIC)->getValue(),
            ),
            \Webcook\Translator\ServiceFactory::BING => array(
            'clientId' => $this->settings->get('Bing client id', \WebCMS\Settings::SECTION_BASIC)->getValue(),
            'clientSecret' => $this->settings->get('Bing client secret', \WebCMS\Settings::SECTION_BASIC)->getValue(),
            ),
        ));

        return $this->serviceFactory->build($serviceId);
    }

    private function getLanguages()
    {
        $serviceId = $this->settings->get('Translate service', \WebCMS\Settings::SECTION_BASIC, 'select')->getValue();

        $cache = new \Nette\Caching\Cache($this->getContext()->getService('cacheStorage'), 'htmlFront');

        if (!$languages = $cache->load('tl'.$serviceId)) {
            $languages = $this->translatorService->getLanguages();

            $cache->save('tl'.$serviceId, $languages);
        }

        return $languages;
    }

    public function createComponentTranslatorForm()
    {
        $form = $this->createForm();

        $packages = \WebCMS\Helpers\SystemHelper::getPackages();

        if ($this->translatorService instanceof \Webcook\Translator\ITranslator) {
            $langs = $this->getLanguages();
            $langst = array();
            foreach ($langs as $yl) {
                $langst[$yl->getAbbreviation()] = $yl->getName();
            }

            $form->addGroup('System');
            $form->addSelect('systemLanguage', 'System language', $this->getAllLanguages())->setAttribute('class', 'form-control');

            $form->addGroup('Service');
            $form->addSelect('languageFrom', 'From', $langst)->setAttribute('class', 'form-control');
            $form->addSelect('languageTo', 'To', $langst)->setAttribute('class', 'form-control');

            $form->addGroup('Settings');

            foreach ($packages as $key => $package) {
                if ($package['vendor'] === 'webcms2' && $package['package'] !== 'webcms2') {
                    $object = $this->createObject($package['package']);

                    if ($object->isTranslatable()) {
                        $form->addCheckbox(str_replace('-', '_', $package['package']), $package['package']);
                    } else {
                        $form->addCheckbox(str_replace('-', '_', $package['package']), $package['package'].' not translatable.')->setDisabled(true);
                    }
                }
            }

            $form->addSubmit('translate', 'Translate');
        }

        $form->onSuccess[] = callback($this, 'translatorFormSubmitted');

        return $form;
    }

    public function translatorFormSubmitted(UI\Form $form)
    {
        $values = $form->getValues();
        $from = $values->languageFrom;
        $to = $values->languageTo;
        $language = $this->em->getRepository('WebCMS\Entity\Language')->find($values->systemLanguage);

        // clear values
        unset($values->languageFrom);
        unset($values->languageTo);
        unset($values->systemLanguage);

        $pages = $this->em->getRepository('WebCMS\Entity\Page')->findBy(array(
            'language' => $language,
            ), array('lft' => 'asc'));

        foreach ($pages as $page) {
            $t = $this->translatorService->translate($page->getTitle(), $from, $to);
            $page->setTitle($t->getTranslation());
            $page->setSlug(\Nette\Utils\Strings::webalize($page->getTitle()));

            $this->em->flush();

            $path = $this->em->getRepository('WebCMS\Entity\Page')->getPath($page);
            $final = array();
            foreach ($path as $p) {
                if ($p->getParent() != NULL) {
                    $final[] = $p->getSlug();
                }
            }

            $page->setPath(implode('/', $final));
        }

        // translate all data
        foreach ($values as $key => $value) {
            if ($value) {
                $module = $this->createObject(str_replace('_', '-', $key));
                if ($module->isTranslatable()) {
                    $module->translateData($this->em, $language, $from, $to, $this->translatorService);
                }
            }
        }

        // translate all static texts
        $translations = $this->em->getRepository('WebCMS\Entity\Translation')->findBy(array(
            'language' => $language,
        ));

        foreach ($translations as $translation) {
            $t = $this->translatorService->translate($translation->getTranslation(), $from, $to);
            $translation->setTranslation($t->getTranslation());
            $translation->setHash();
        }

        $this->em->flush();

        $this->flashMessage('Translation of language finished.', 'success');
        if (!$this->isAjax()) {
            $this->forward('Languages:translator');
        }
    }
}
