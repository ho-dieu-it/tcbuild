<?php
namespace AppBundle\Repository;

use Gedmo\Tree\Entity\Repository\NestedTreeRepository;

class PostCategoryRepository extends NestedTreeRepository
{
    public function findByType($type)
    {
        return $this
            ->createQueryBuilder('p')
            ->select('p')
            ->where('p.type = :type')
            ->setParameter('type', $type)
            ->getQuery()
            ->getResult();
    }
    public function findBySlug($slug)
    {
        return $this
            ->createQueryBuilder('p')
            ->select('p')
            ->where('p.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getSingleResult();
    }

    
}

