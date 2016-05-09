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
    protected function getHeaderFooter()
    {
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('AppBundle:Page');

        $header = $repo->findPageBySlug('header');

        $footer = $repo->findPageBySlug('footer');

        return array('header'=>$header, 'footer' => $footer);
    }

}