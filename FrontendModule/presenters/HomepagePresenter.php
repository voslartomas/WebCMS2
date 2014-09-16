<?php

namespace FrontendModule;

/**
 * Admin presenter.
 * @author Tomáš Voslař <tomas.voslar at webcook.cz>
 * @package WebCMS2
 */
class HomepagePresenter extends \FrontendModule\BasePresenter
{
    protected function beforeRender()
    {
        parent::beforeRender();
    }

    protected function startup()
    {
        parent::startup();

        $page = $this->em->getRepository('WebCMS\Entity\Page')->findOneBy(array(
            'default' => 1,
            'language' => $this->language
        ));

        if (is_object($page)) {

            $root = $this->settings->get('Root domain', \WebCMS\Settings::SECTION_BASIC);
            $abbr = $page->getLanguage()->getDefaultFrontend() ? '' : $page->getLanguage()->getAbbr() . '/';

            if ($root->getValue()) {
                $this->forward(':Frontend:' . $page->getModuleName() . ':' . $page->getPresenter() . ':default', array('id' => $page->getId(), 'path' => $page->getPath(), 'abbr' => $abbr));
            } else {
                $this->redirect(':Frontend:' . $page->getModuleName() . ':' . $page->getPresenter() . ':default', array('id' => $page->getId(), 'path' => $page->getPath(), 'abbr' => $abbr));
            }
        }

        $this->forward(':Admin:Login:');
    }
}
