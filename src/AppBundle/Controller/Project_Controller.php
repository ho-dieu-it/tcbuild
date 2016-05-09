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

use AppBundle\Entity\Category;
use AppBundle\Entity\Post;
use AppBundle\Entity\Menu;
use AppBundle\Entity\Page;
use AppBundle\Utils\Paginator;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;


/**
 * Controller used to manage blog contents in the public part of the site.
 *
 * @Route("/project")
 *
 * @author Ryan Weaver <weaverryan@gmail.com>
 * @author Javier Eguiluz <javier.eguiluz@gmail.com>
 */
class ProjectController extends Controller
{
    const FIELD = 7; // is field
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
     *
     * @Route("/", name="project_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $slug ='';
        $em = $this->getDoctrine()->getManager();
        //$menu = $em->getRepository('AppBundle:Menu')->findMenuById(self::FIELD);
        $repo = $em->getRepository('AppBundle:Product');
        
        $pages = $repo->findPageByMenuId(self::FIELD);
//        $slug = $category->getSlug();

        if( $slug !== '' )
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
//                'header_footer'=> $this->getHeaderFooter()
            ));
    }
    /**
     * @Route("/chi-tiet/{slug}", name="project_post")

     */
    public function postShowAction(Page $page)
    {
        $em = $this->getDoctrine()->getManager();
        //$menu = $em->getRepository('AppBundle:Menu')->findMenuById(self::FIELD);
        $repo = $em->getRepository('AppBundle:Page');

        $pages = $repo->findPageByMenuId(self::FIELD);
        $slug = $page->getSlug();

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
//                'header_footer'=> $this->getHeaderFooter()
            ));
    }


}
