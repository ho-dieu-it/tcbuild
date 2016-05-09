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
use AppBundle\Entity\Category;
use AppBundle\Entity\Menu;
use AppBundle\Entity\Page;
use AppBundle\Utils\Paginator;


class ServiceController extends Controller
{
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

    public function indexAction($pageNum)
    {
        $em = $this->getDoctrine()->getManager();
        $menu = $em->getRepository('AppBundle:Menu')->find(1);
        $repo = $em->getRepository('AppBundle:Page');

        $countPages = $repo->getCountPagesByMenu($menu);

        $paginator = new Paginator($countPages, $pageNum, $limit = 10);

        $pagenums = $paginator->getNumPages();

        $services = $repo->findPagesWithPagingByMenu(
            $menu,
            $paginator->getOffset(),
            $paginator->getLimit()
        );

        $em = $this->getDoctrine()->getManager();
        $menu = $em->getRepository('AppBundle:Menu')->find(1);
        $services = $em->getRepository('AppBundle:Page')->findPageByMenu($menu);

        return $this->render('service/index.html.twig',
            array(
                'services' => $services,
                'menus' => $this->getMenus(),
                'page' => 1,
                'pageNum' => $pagenums,
                'header_footer'=> $this->getHeaderFooter(),
            ));
    }

    public function showAction(Page $page)
    {
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('AppBundle:Page');
        $service = $repo->findPageBySlug($page);
        $related_pages = $repo->findRelatedPages($page);
        //$hot_products = $repo->findHotProduct();

        return $this->render('service/show.html.twig', array(
            'service'        => $service,
            'related_pages' => $related_pages,
            //'hot_products' => $hot_products,
            'menus' => $this->getMenus(),
            'page' => 1,
            'header_footer'=> $this->getHeaderFooter(),
        ));
    }


}
