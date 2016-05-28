<?php
/**
 * Created by PhpStorm.
 * User: Jon
 * Date: 5/8/2016
 * Time: 4:30 PM
 */

namespace AppBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class BaseController extends Controller
{
    /**
     * @return array
     */
    protected function getHeaderFooter()
    {
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('AppBundle:Page');

        $header = $repo->findPageBySlug('header');

        $footer = $repo->findPageBySlug('footer');

        return array('header'=>$header, 'footer' => $footer);
    }

    /**
     * @param $root
     * @param $parent
     * @param $children
     * @return array
     */
    protected function getBreadCrumb( $root, $parent, $children )
    {        

        $root = array(
            'name' => $this->get('translator')->trans($root['title']),
            'url'  => $root['url']
        );
        $parent = array(
            'name' => $this->get('translator')->trans($parent['title']),
            'url'  => $parent['url'],
        );
        $children = array(
            'name' => $this->get('translator')->trans($children['title']),
            'url'  => $children['url'],
        );
        
        $breadCrumb = array(
        'root'  => $root,
        'parent' => $parent,
        'children' => $children,
        );
        
        return $breadCrumb;
    }
    

}