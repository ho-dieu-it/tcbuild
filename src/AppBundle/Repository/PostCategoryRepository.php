<?php
namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

class PostCategoryRepository extends EntityRepository
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

