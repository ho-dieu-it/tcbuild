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
use AppBundle\Entity\Page;

/**
 * Class IntroController
 * @package AppBundle\Controller
 * @Route("/gioi-thieu", name="intro")
 */
class IntroController extends BaseController
{
    /**
     * @Route("/{slug}", name="intro_index")
     * @Method("GET")
     */
    public function indexAction($slug)
    {
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('AppBundle:Page');

        $default_intro = $repo->findPageBySlug($slug);

        return $this->render('intro/index.html.twig',
            array(
                'intros' => null,
                //'menus' => $menus,
                'default_intro' => $default_intro,
                'page' => 1,
                'header_footer'=> $this->getHeaderFooter(),
            ));
    }
}
