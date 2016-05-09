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
use AppBundle\Entity\FieldCategory;

/**
 * This custom Doctrine repository contains some methods which are useful when
 * querying for blog post information.
 * See http://symfony.com/doc/current/book/doctrine.html#custom-repository-classes
 *
 * @author Ryan Weaver <weaverryan@gmail.com>
 * @author Javier Eguiluz <javier.eguiluz@gmail.com>
 */
class FieldCategoryRepository extends EntityRepository
{
    public function findLatest($limit = FieldCategory::NUM_ITEMS)
    {
        return $this
            ->createQueryBuilder('p')
            ->select('p')
            ->where('p.created_at <= :now')->setParameter('now', new \DateTime())
            ->orderBy('p.created_at', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findFieldCategoryById($slug)
    {
        return $this
            ->createQueryBuilder('p')
            ->select('p')
            ->where('p.slug = :slug')->setParameter('slug',$slug)
            ->getQuery()
            ->getSingleResult()
            ;
    }

}
