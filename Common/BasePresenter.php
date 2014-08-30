<?php

namespace WebCMS2\Common;

abstract class BasePresenter extends \Nette\Application\UI\Presenter
{
	protected $em;
	
	protected abstract function getLanguageId();

	protected function startUp()
	{
		parent::startUp();
	}

	protected function getSettings()
    {
        $query = $this->em->createQuery('SELECT s FROM WebCMS\Entity\Setting s WHERE s.language >= ' . $this->getLanguageId() . ' OR s.language IS NULL');
        $tmp = $query->getResult();

        $settings = array();
        foreach ($tmp as $s) {
            $settings[$s->getSection()][$s->getKey()] = $s;
        }

        return $settings;
    }	

    /**
     * Injects entity manager.
     * 
     * @param  \Doctrine\ORM\EntityManager  $em
     * 
     * @return BasePresenter
     * @throws \Nette\InvalidStateException
     */
    public function injectEntityManager(\Doctrine\ORM\EntityManager $em)
    {
        if ($this->em) {
            throw new \Nette\InvalidStateException('Entity manager has been already set.');
        }

        $this->em = $em;

        return $this;
    }

    /**
     * Generate sitemap.xml file in www (public) directory.
     * 
     * @return XML sitemap
     */
    public function generateSitemap()
    {
        $sitemapXml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";

        $repository = $this->em->getRepository('WebCMS\Entity\Page');
        $pages = $repository->findAll();

        foreach ($pages as $page) {
            if ($page->getParent() !== null && $page->getVisible()) {
                $sitemapXml .= "<url>\n\t<loc>" . $this->getSitemapLink($page) . "</loc>\n</url>\n";
            }
        }

        $sitemapXml .= '</urlset>';

        file_put_contents('./sitemap.xml', $sitemapXml);
    }

    /**
     * Get single sitemap link url address.
     * 
     * @param  \WebCMS2\Entity\Page $page Page entity object.
     * @return string               Url address of the link.
     */
    private function getSitemapLink($page)
    {
        $url = $this->context->httpRequest->url->baseUrl;
        $url .= !$page->getLanguage()->getDefaultFrontend() ? $page->getLanguage()->getAbbr() . '/' : '';
        $url .= $page->getPath();

        return $url;
    }

    protected function getAllLanguages()
    {
        $languages = $this->em->getRepository('WebCMS\Entity\Language')->findAll();

        $langs = array('' => $this->translation['Pick a language']);
        foreach ($languages as $l) {
            $langs[$l->getId()] = $l->getName();
        }

        return $langs;
    }
}
