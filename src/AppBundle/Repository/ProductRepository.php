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
use AppBundle\Entity\Product;
use AppBundle\Entity\Category;
use AppBundle\Entity\Image;

/**
 * This custom Doctrine repository contains some methods which are useful when
 * querying for blog post information.
 * See http://symfony.com/doc/current/book/doctrine.html#custom-repository-classes
 *
 * @author Ryan Weaver <weaverryan@gmail.com>
 * @author Javier Eguiluz <javier.eguiluz@gmail.com>
 */
class ProductRepository extends EntityRepository
{
    public function findLatest($limit = Product::NUM_ITEMS)
    {
        return $this
            ->createQueryBuilder('p')
            ->select('p')
            ->where('p.createdAt <= :now')->setParameter('now', new \DateTime())
            ->andWhere('i.isType = :isType')
            ->setParameter('isType', 1)
            ->orderBy('p.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }
    public function findProductsByCate(Category $category)
    {
        return $this
            ->createQueryBuilder('p')
            ->select('p,i')
            ->leftJoin('p.images', 'i')
            ->where('p.category_id = :category')
            ->setParameter('category', $category)
            ->andWhere('i.isType = :isType')
            ->setParameter('isType', 1)
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function getCountProductsByCate(Category $category)
    {
        $query_builder  = $this->createQueryBuilder('p');

        return $query_builder
            ->add('select', $query_builder->expr()->count('p'))
            ->where('p.category = :category')
            ->setParameter('category', $category)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getCountProductsByAllCate()
    {
        $query_builder  = $this->createQueryBuilder('p');

        return $query_builder
            ->add('select', $query_builder->expr()->count('p'))
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findProductsWithPagingByAllCate($offset = 0, $limit =0 )
    {
        $query_builder  = $this->createQueryBuilder('p');
        if ((isset($offset)) && (isset($limit))) {
            if ($limit > 0) {
                $query_builder->setFirstResult($offset);
                $query_builder->setMaxResults($limit);
            }
        }

        return $query_builder
            ->select('p')->distinct()
            ->leftJoin('p.images','i')
            ->leftJoin('p.category','c')
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findProductsWithPagingByCate(Category $category,
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
            ->select('p')->distinct()
            ->leftJoin('p.images','i')
            ->where('p.category = :category')
            ->setParameter('category', $category)
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findProductById(Product $product)
    {
        return $this
            ->createQueryBuilder('p')
            ->select('p')
            ->leftJoin('p.images', 'i')
            ->where('p.id = :product_id')
            ->setParameter('product_id', $product->getId())
            ->getQuery()
            ->getSingleResult();
    }

    public function findRelatedProduct(Product $product)
    {
        return $this
            ->createQueryBuilder('p')
            ->select('p')
            ->leftJoin('p.images', 'i')
            ->where('p.category = :category')
            ->setParameter('category', $product->getCategory())
            ->andWhere('p.id <> :product_id')
            ->setParameter('product_id', $product->getId())
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findHotProduct()
    {
        return $this
            ->createQueryBuilder('p')
            ->select('p')
            ->leftJoin('p.images', 'i')
            ->orderBy('p.createdAt', 'DESC')
            ->setMaxResults(3)
            ->getQuery()
            ->getResult();
    }
    public function findSlideProducts()
    {
        return $this
            ->createQueryBuilder('p')
            ->select('p')
            ->leftJoin('p.images', 'i')
            ->orderBy('p.createdAt', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();
    }
    public function findPresentProducts()
    {
        return $this
            ->createQueryBuilder('p')
            ->select('p')
            ->leftJoin('p.images', 'i')
            ->orderBy('p.createdAt', 'DESC')
            ->setMaxResults(12)
            ->getQuery()
            ->getResult();
    }


}
