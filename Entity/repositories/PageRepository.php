<?php

namespace WebCMS\Entity;

use Gedmo\Tree\Entity\Repository\NestedTreeRepository;

class PageRepository extends NestedTreeRepository
{
    public function getTreeForSelect($order = null, $where = null)
    {
        $qb = $this->_em->createQueryBuilder();

        if ($order) {
            foreach ($order as $o) {
                $qb->addOrderBy('l.'.$o['by'], $o['dir']);
            }
        }

        if ($where) {
            foreach ($where as $w) {
                $qb->andWhere('l.'.$w);
            }
        }

        $tree = $qb->select('l')->from("WebCMS\Entity\Page", 'l')->getQuery()->getResult();

        $array = array();
        foreach ($tree as $node) {
            $array[$node->getId()] = str_repeat("-", $node->getLevel()).$node->getTitle();
        }

        return $array;
    }
}
