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
use AppBundle\Entity\Menu;

/**
 * This custom Doctrine repository contains some methods which are useful when
 * querying for blog post information.
 * See http://symfony.com/doc/current/book/doctrine.html#custom-repository-classes
 *
 * @author Ryan Weaver <weaverryan@gmail.com>
 * @author Javier Eguiluz <javier.eguiluz@gmail.com>
 */
class MenuRepository extends EntityRepository
{
    public function findLatest($limit = Menu::NUM_ITEMS)
    {
        return $this
            ->createQueryBuilder('p')
            ->select('p')
            ->where('p.publishedAt <= :now')->setParameter('now', new \DateTime())
            ->orderBy('p.publishedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findMenuByCate(Menu $menu)
    {
        return $this
            ->createQueryBuilder('p')
            ->select('p')
            ->where('p.slug = :slug')->setParameter('slug',$menu->getSlug())
            ->getQuery()
            ->getSingleResult()
            ;
    }

    public function findMenuById($id)
    {
        return $this
            ->createQueryBuilder('p')
            ->select('p')
            ->where('p.id = :id')->setParameter('id',$id)
            ->getQuery()
            ->getSingleResult()
            ;
    }

    public function findMenuByType($type)
    {
        return $this
            ->createQueryBuilder('p')
            ->select('p')
            ->where('p.type = :type')->setParameter('type',$type)
            ->getQuery()
            ->getResult()
            ;
    }

    public function findPageByAllMenu()
    {
        return $this
            ->createQueryBuilder('p')
            ->select('p,m.title, m.slug, m.id')->distinct(true)
            ->leftJoin('p.menu','m')
            //->leftJoin('p.files', 'i')
            ->getQuery()
            ->getResult()
            ;
    }


}
