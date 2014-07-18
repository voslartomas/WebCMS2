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
}
