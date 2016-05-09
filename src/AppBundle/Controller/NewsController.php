<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use AppBundle\Entity\Post;
use AppBundle\Entity\PostCategory;
use AppBundle\Entity\Image;
use AppBundle\Entity\Page;
use AppBundle\Entity\Menu;
use AppBundle\Utils\Paginator;

/**
 * Controller used to manage blog contents in the backend.
 *
 * Please note that the application backend is developed manually for learning
 * purposes. However, in your real Symfony application you should use any of the
 * existing bundles that let you generate ready-to-use backends without effort.
 * See http://knpbundles.com/keyword/admin
 *
 * @Route("/tin-tuc")
 * @author Ryan Weaver <weaverryan@gmail.com>
 * @author Javier Eguiluz <javier.eguiluz@gmail.com>
 */
class NewsController extends BaseController
{
    const TYPE = 3;


    public function getMenus()
    {

        $em = $this->getDoctrine()->getManager();

        $categories = $em->getRepository('AppBundle:Category')->getNodesHierarchy();

        $menus = array();

        foreach($categories as $category){
            if($category['lvl'] === 0){
                $menus[$category['id']] = $category;
            }else if($category['lvl'] === 1){
                $menus[$category['root']]['children'][] = $category;
            }
        }

        return $menus;

    }
    /**
     * Lists all Post entities.
     *
     * This controller responds to two different routes with the same URL:
     *   * 'admin_post_index' is the route with a name that follows the same
     *     structure as the rest of the controllers of this class.
     *   * 'admin_index' is a nice shortcut to the backend homepage. This allows
     *     to create simpler links in the templates. Moreover, in the future we
     *     could move this annotation to any other controller while maintaining
     *     the route name and therefore, without breaking any existing link.
     *
     *
     * @Route("/page.{pageNum}", name="news_index")
     *
     */

    public function indexAction($pageNum)
    {
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('AppBundle:Post');
        $repoCategory = $em->getRepository('AppBundle:PostCategory');

        $categories = $repoCategory->findByType(self::TYPE);
        $category = new PostCategory();
        $category->setName('Tất cả');

        $countProducts = $repo->getCountPostsByCateType(self::TYPE);

        $paginator = new Paginator($countProducts, $pageNum, $limit = 5);

        $pagenums = $paginator->getNumPages();

        $posts = $repo->findPostsWithPagingByAllCate(
            $paginator->getOffset(),
            $paginator->getLimit(),
            self::TYPE
        );

        $hot_posts = $repo->findHotPost(self::TYPE);

        return $this->render('news/list.html.twig', array(
            'posts' => $posts,
            'page' => $pageNum,
            'pagenums' => $pagenums,
            'hot_posts' => $hot_posts,
            'menus' => $this->getMenus(),
            'slug' => $category->getSlug(),
            'categories' => $categories,
            'category' => $category,
            'header_footer'=> $this->getHeaderFooter(),
        ));
    }
    /**
     * Finds and displays a Product entity.
     * @Route("/chi-tiet/{slug}", name="news_show")
     */
    public function showAction(Post $post)
    {
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('AppBundle:Post');
        $post = $repo->findPostById($post);
        $related_posts = $repo->findRelatedPost($post);
        $hot_posts = $repo->findHotPost(self::TYPE);

        $repoCategory = $em->getRepository('AppBundle:PostCategory');
        $categories = $repoCategory->findByType(self::TYPE);
        return $this->render('news/show.html.twig', array(
            'post'        => $post,
            'categories'  => $categories,
            'related_posts' => $related_posts,
            'hot_posts' => $hot_posts,
            'menus' => $this->getMenus(),
            'page' => 1,
            'header_footer'=> $this->getHeaderFooter(),
        ));
    }

    /**
     * @Route("/{slug}.{pageNum}", name="news_list")
     * @ParamConverter("post", class="AppBundle:PostCategory")
     * @Method("GET")
     */
    public function listAction(PostCategory $category, $pageNum = 1)
    {
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('AppBundle:Post');
        $repoCategory = $em->getRepository('AppBundle:PostCategory');

        $categories = $repoCategory->findByType(self::TYPE);
        
        $countProducts = $repo->getCountPostsByCate($category);

        $paginator = new Paginator($countProducts, $pageNum, $limit = 5);

        $pagenums = $paginator->getNumPages();

        $posts = $repo->findPostsWithPagingByCate(
            $category,
            $paginator->getOffset(),
            $paginator->getLimit()
        );

        $hot_posts = $repo->findHotPost(self::TYPE);

        return $this->render('news/list.html.twig', array(
            'posts' => $posts,
            'page' => $pageNum,
            'pagenums' => $pagenums,
            'hot_posts' => $hot_posts,
            'slug' => $category->getSlug(),
            'categories' => $categories,
            'category' => $category,
            'header_footer'=> $this->getHeaderFooter(),
        ));
    }
}
