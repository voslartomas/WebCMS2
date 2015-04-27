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
            'language' => $this->language,
        ));

        if (is_object($page)) {
            $root = $this->settings->get('Root domain', \WebCMS\Settings::SECTION_BASIC);
            $abbr = $page->getLanguage()->getDefaultFrontend() ? '' : $page->getLanguage()->getAbbr().'/';
            $params = array('id' => $page->getId(), 'path' => $page->getPath(), 'abbr' => $abbr);

            // Hotfix UTM params and gclid for homepage redirect
            // TODO - refactor / move into appropriate place
            if ($this->getParam('utm_source')) {

                $utm = array(
                    'utm_source' => $this->getParam('utm_source'),
                    'utm_medium' => $this->getParam('utm_medium'),
                    'utm_term' => $this->getParam('utm_term'),
                    'utm_content' => $this->getParam('utm_content'),
                    'utm_campaign' => $this->getParam('utm_campaign')
                );

                $params = array_merge($params, array_filter($utm));
            }

            if ($this->getParam('gclid')) {
                $params = array_merge($params, array('gclid' => $this->getParam('gclid')));
            }

            if ($root->getValue()) {
                $this->forward(':Frontend:'.$page->getModuleName().':'.$page->getPresenter().':default', $params);
            } else {
                $this->redirect(':Frontend:'.$page->getModuleName().':'.$page->getPresenter().':default', $params);
            }
        }

        $this->forward(':Admin:Login:');
    }
}
