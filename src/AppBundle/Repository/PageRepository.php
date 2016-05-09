<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use AppBundle\Entity\Page;
use AppBundle\Entity\Menu;

/**
 * This custom Doctrine repository contains some methods which are useful when
 * querying for blog post information.
 * See http://symfony.com/doc/current/book/doctrine.html#custom-repository-classes
 *
 * @author Ryan Weaver <weaverryan@gmail.com>
 * @author Javier Eguiluz <javier.eguiluz@gmail.com>
 */
class PageRepository extends EntityRepository
{
    public function findLatest($limit = Page::NUM_ITEMS)
    {
        return $this
            ->createQueryBuilder('p')
            ->select('p')
            ->leftJoin('p.files', 'i')
            ->where('p.publishedAt <= :now')->setParameter('now', new \DateTime())
            ->orderBy('p.publishedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findPageByMenu(Menu $menu)
    {
        return $this
            ->createQueryBuilder('p')
            ->select('p')
            ->leftJoin('p.files', 'i')
            ->where('p.menu = :menu')->setParameter('menu',$menu)
            ->getQuery()
            ->getResult()
            ;
    }

    public function findPageByAllMenu()
    {
        return $this
            ->createQueryBuilder('p')
            ->select('p,m.title, m.slug, m.id,m.description')
            ->leftJoin('p.menu','m')
            //->leftJoin('p.files', 'i')
            ->getQuery()
            ->getResult()
            ;
    }
    
    public function findPageByMenuWithLimit(Menu $menu, $limit)
    {
        return $this
            ->createQueryBuilder('p')
            ->select('p')
            ->leftJoin('p.files', 'i')
            ->where('p.menu = :menu')->setParameter('menu',$menu)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
            ;
    }
    public function findPageBySlug($slug)
    {
        return $this
            ->createQueryBuilder('p')
            ->select('p')
            ->leftJoin('p.files', 'i')
            ->where('p.slug = :slug')->setParameter('slug', $slug)
            ->getQuery()
            ->getSingleResult()
            ;
    }
    
    
    public function findPageByMenuId($menu_id)
    {
        return $this
            ->createQueryBuilder('p')
            ->select('p')
            ->where('p.menu = :menu')->setParameter('menu',$menu_id)
            ->getQuery()
            ->getResult()
            ;
    }

    public function findRelatedPages(Page $page)
    {
        return $this
            ->createQueryBuilder('p')
            ->select('p')
            ->where('p.menu = :menu')
            ->setParameter('menu', $page->getMenu())
            ->andWhere('p.id <> :page_id')
            ->setParameter('page_id', $page->getId())
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findPagesWithPagingByMenu(Menu $menu,
                                                 $offset = 0, $limit =0 )
    {
        $query_builder  = $this->createQueryBuilder('p');
        if ((isset($offset)) && (isset($limit))) {
            if ($limit > 0) {
                $query_builder->setFirstResult($offset);
                $query_builder->setMaxResults($limit);
            }
        }
        return $query_builder
            ->select('p,i')
            ->leftJoin('p.files', 'i')
            ->where('p.menu = :menu')
            ->setParameter('menu', $menu)
//            ->andWhere('i.isType = :isType')
//            ->setParameter('isType', 2)
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function getCountPagesByMenu(Menu $menu)
    {
        $query_builder  = $this->createQueryBuilder('p');

        return $query_builder
            ->add('select', $query_builder->expr()->count('p'))
            ->where('p.menu = :menu')
            ->setParameter('menu', $menu)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
