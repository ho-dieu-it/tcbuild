<?php
namespace AppBundle\Repository;

use Gedmo\Tree\Entity\Repository\NestedTreeRepository;

class CategoryRepository extends NestedTreeRepository
{
    public function findCategoryById(Product $product)
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

    
}

