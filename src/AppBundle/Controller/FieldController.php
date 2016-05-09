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

use AppBundle\Entity\PostCategory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use AppBundle\Entity\Category;
use AppBundle\Entity\Menu;
use AppBundle\Entity\Page;
use AppBundle\Utils\Paginator;

/**
 * @Route("/linh-vuc-hoat-dong")
 */
class FieldController extends BaseController
{
    const FIELD = 1; // is field
    public function getMenus()
    {

        $em = $this->getDoctrine()->getManager();

        $categories = $em->getRepository('AppBundle:PostCategory')->getNodesHierarchy();

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
     * @Route("/", name="field_index")
     * @Route("/{slug}", name="field_index")
     * @Method("GET")
     */
    public function indexAction($slug = '')
    {
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('AppBundle:Post');
        $repoCate = $em->getRepository('AppBundle:PostCategory');

        $categories = $repoCate->findByType(self::FIELD);
        if( $slug !== '' )
        {
            $category = $repoCate->findBySlug($slug);
            $posts = $repo->findByCate($category[0]);
        }
        else 
        {
            $category = reset($categories);
            $posts = $repo->findByCate($category);
        }


        $post = reset( $posts );

        return $this->render('field/index.html.twig',
            array(
                'posts' => $posts,
                'categories' => $categories,
                'post' => $post,
                'header_footer'=> $this->getHeaderFooter()
            ));
    }
    /**
     * @Route("/a/{slug}", name="field_show")
     * @Route("/a", name="field_show")
     *
     * @Method("GET")
     */
    public function showAction($slug)
    {
        $em = $this->getDoctrine()->getManager();
        //$menu = $em->getRepository('AppBundle:Menu')->findMenuById(self::FIELD);
        $repo = $em->getRepository('AppBundle:Page');

        $pages = $repo->findPageByMenuId(self::FIELD);

        if( $slug !== null )
        {
            $page = $repo->findPageBySlug($slug);
        }
        else
        {
            $page = reset( $pages );
        }

        return $this->render('field/index.html.twig',
            array(
                'pages' => $pages,
                'menus' => $this->getMenus(),
                'page' => $page,
                'header_footer'=> $this->getHeaderFooter(),
            ));
    }


}
