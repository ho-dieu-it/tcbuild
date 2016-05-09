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
use AppBundle\Entity\Page;
use AppBundle\Entity\Enquiry;
use AppBundle\Form\EnquiryType;

/**
 * Class ContactController
 * @package AppBundle\Controller
 * @Route("/lien-he")
 */
class ContactController extends BaseController
{
    /**
     * @Route("/", name="contact_index")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('AppBundle:Page');

        $contact = $repo->findPageBySlug('lien-he');

        $enquiry = new Enquiry();
        $form = $this->createForm(new EnquiryType(), $enquiry);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $emailTo = $enquiry->getEmail();
            $content = 'Chúng tôi đã nhận được yêu cầu từ bạn !';

            $message = \Swift_Message::newInstance()
                ->setSubject('Hello Email')
                ->setFrom('hodieu.it@gmail.com')
                ->setTo($emailTo)
                ->setBody(
                    $content
                )
            ;
            $this->get('mailer')->send($message);

            return $this->render('contact/index.html.twig',
                array(
                    'contact' => $contact,
                    'page' => 1,
                    'form' => $form->createView(),
                    'header_footer'=> $this->getHeaderFooter()
                ));
        }

        return $this->render('contact/index.html.twig',
            array(
                'contact' => $contact,
                'page' => 1,
                'form' => $form->createView(),
                'header_footer'=> $this->getHeaderFooter()
            ));
    }

    /**
     * @Route("/contact", name="contact_action")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
//    public function contactAction(Request $request)
//    {
//        $enquiry = new Enquiry();
//        $form = $this->createForm(new EnquiryType(), $enquiry);
//
//        $form->handleRequest($request);
//
//        if ($form->isSubmitted() && $form->isValid()) {
//
//        }
//
//        return $this->redirectToRoute('admin_post_list', array('id' =>$category->getId()));
//
//        return $this->render('BloggerBlogBundle:Page:contact.html.twig', array(
//            'form' => $form->createView()
//        ));
//    }


}
