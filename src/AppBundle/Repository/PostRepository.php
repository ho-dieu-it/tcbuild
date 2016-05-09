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

use AppBundle\Entity\PostCategory;
use Doctrine\ORM\EntityRepository;
use AppBundle\Entity\Post;

/**
 * This custom Doctrine repository contains some methods which are useful when
 * querying for blog post information.
 * See http://symfony.com/doc/current/book/doctrine.html#custom-repository-classes
 *
 * @author Ryan Weaver <weaverryan@gmail.com>
 * @author Javier Eguiluz <javier.eguiluz@gmail.com>
 */
class PostRepository extends EntityRepository
{
    public function findLatest($limit = Post::NUM_ITEMS)
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
    
    public function findHotPost($type)
    {
        return $this
            ->createQueryBuilder('p')
            ->select('p')
            ->leftJoin('p.files', 'i')
            ->leftJoin('p.post_category','c')
            ->where('c.type = :type')
            ->setParameter('type', $type)
            ->orderBy('p.id', 'DESC')
            ->addOrderBy('i.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findHotPostHome($type)
    {
        return $this
            ->createQueryBuilder('p')
            ->select('p,c.slug,c.id as category_id')
            ->leftJoin('p.files', 'i')
            ->leftJoin('p.post_category','c')
            ->where('c.type = :type')
            ->setParameter('type', $type)
            ->orderBy('p.id', 'DESC')
            ->addOrderBy('i.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
    
    public function findPostById(Post $post)
    {
        return $this
            ->createQueryBuilder('p')
            ->select('p')
            ->leftJoin('p.files', 'i')
            ->where('p.id = :post_id')
            ->setParameter('post_id', $post->getId())
            ->getQuery()
            ->getSingleResult();
    }

    public function findPostBySlug($slug)
    {
        return $this
            ->createQueryBuilder('p')
            ->select('p')
            ->leftJoin('p.files', 'i')
            ->where('p.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getSingleResult();
    }
    
    public function findPostByPost(Post $post)
    {
        return $this
            ->createQueryBuilder('p')
            ->select('p')
            ->where('p.slug = :slug')->setParameter('slug',$post->getSlug())
            ->getQuery()
            ->getSingleResult()
            ;
    }
    public function findByCate(PostCategory $category)
    {
        return $this
            ->createQueryBuilder('p')
            ->select('p,i')
            ->leftJoin('p.files', 'i')
            ->where('p.post_category = :category')
            ->setParameter('category', $category)
//            ->andWhere('i.isType = :isType')
//            ->setParameter('isType', 1)
//            ->orderBy('p.publishedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findPostsByCateId($id)
    {
        return $this
            ->createQueryBuilder('p')
            ->select('p,i')
            ->leftJoin('p.files', 'i')
            ->where('p.post_category = :cateId')
            ->setParameter('cateId', $id)
//            ->orderBy('p.publishedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findRelatedPost(Post $post)
    {
        return $this
            ->createQueryBuilder('p')
            ->select('p')
            ->leftJoin('p.files', 'i')
            ->where('p.post_category = :category')
            ->setParameter('category', $post->getPostCategory())
            ->andWhere('p.id <> :post_id')
            ->setParameter('post_id', $post->getId())
            ->orderBy('p.publishedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function getCountPostsByCate(PostCategory $category)
    {
        $query_builder  = $this->createQueryBuilder('p');

        return $query_builder
            ->add('select', $query_builder->expr()->count('p'))
            ->where('p.post_category = :category')
            ->setParameter('category', $category)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getCountPostsByCateType($type)
    {
        $query_builder  = $this->createQueryBuilder('p');

        return $query_builder
            ->add('select', $query_builder->expr()->count('p'))
            ->leftJoin('p.post_category','c')
            ->where('c.type = :type')
            ->setParameter('type', $type)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findPostsWithPagingByAllCate($offset = 0, $limit =0, $type )
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
            ->leftJoin('p.files','i')
            ->leftJoin('p.post_category','c')
//            ->orderBy('p.createdAt', 'DESC')
            ->where('c.type = :type')
            ->setParameter('type', $type)
            ->getQuery()
            ->getResult();
    }

    public function findPostsWithPagingByCate(PostCategory $category,
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
            ->leftJoin('p.files','i')
            ->where('p.post_category = :category')
            ->setParameter('category', $category)
            ->orderBy('p.publishedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
