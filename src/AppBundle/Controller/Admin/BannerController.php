<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AppBundle\Controller\Admin;

use AppBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use AppBundle\Form\BannerType;
use AppBundle\Entity\Banner;
use AppBundle\Utils\ConstantBundle;


/**
 * Controller used to manage blog contents in the backend.
 *
 * Please note that the application backend is developed manually for learning
 * purposes. However, in your real Symfony application you should use any of the
 * existing bundles that let you generate ready-to-use backends without effort.
 * See http://knpbundles.com/keyword/admin
 *
 * @Route("/admin")
 * @Security("has_role('ROLE_ADMIN')")
 *
 * @author Ryan Weaver <weaverryan@gmail.com>
 * @author Javier Eguiluz <javier.eguiluz@gmail.com>
 */
class BannerController extends BaseController
{
    CONST UPLOAD_FOLDER = '/banners/';
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
     * @Route("")
     * @Route("/banner", name="admin_banner_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $banners = $em->getRepository('AppBundle:Banner')->findAll();

        $url = $this->container->get('router')->generate('admin_banner_index');
        $root = array(
            'title' => 'title.home',
            'url' => $this->container->get('router')->generate('admin_banner_index')
        );
        $parent = array(
            'title' => 'title.banner.management',
            'url' => $url
        );
        $children = array(
            'title' => 'title.banner.list',
            'url' => $url
        );


        return $this->render('admin/banner/index.html.twig',
        array(
            'banners' => $banners,
            'breadCrumb' => $this->getBreadCrumb($root, $parent, $children),
            'user' => $this->getUser()
        ));
    }

    /**
     * Creates a new Page entity.
     *
     * @Route("/new", name="admin_banner_new")
     * @Method({"GET", "POST"})
     * NOTE: the Method annotation is optional, but it's a recommended practice
     * to constraint the HTTP methods each controller responds to (by default
     * it responds to all methods).
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function newAction(Request $request)
    {
        $root = array(
            'title' => 'title.home',
            'url' => $this->container->get('router')->generate('admin_banner_index')
        );
        $parent = array(
            'title' => 'title.banner.management',
            'url' => $this->container->get('router')->generate('admin_banner_index')
        );
        $children = array(
            'title' => 'title.banner.new',
            'url' => ''
        );

        $banner = new Banner();
        $authorEmail = $this->getUser()->getEmail();
        $banner->setAuthorEmail( $authorEmail );

        $form = $this->createForm(new BannerType(), $banner);

        $form->handleRequest($request);

        // the isSubmitted() method is completely optional because the other
        // isValid() method already checks whether the form is submitted.
        // However, we explicitly add it to improve code readability.
        // See http://symfony.com/doc/current/best_practices/forms.html#handling-form-submits
        if ($form->isSubmitted() )
        {
            $uploadedFile = $banner->getUploadedFile();
            if($uploadedFile) {

                $temp_path = __DIR__ .ConstantBundle::UPLOAD_DIR.self::UPLOAD_FOLDER;

                $isUploaded = $banner->upload($temp_path);

                if($isUploaded) {
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($banner);
                    $em->flush();
                }
            }

            return $this->redirectToRoute('admin_banner_index');
        }
        return $this->render('admin/banner/new.html.twig', array(
            'banner' => $banner,
            'form' => $form->createView(),
            'breadCrumb' => $this->getBreadCrumb($root, $parent, $children),
            'user' => $this->getUser(),
        ));
    }

    /**
     * Finds and displays a Post entity.
     *
     * @Route("/show/{id}", requirements={"id" = "\d+"}, name="admin_banner_show")
     * @Method("GET")
     *
     * @param Banner $banner
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showAction( Banner $banner )
    {
        $root = array(
            'title' => 'title.home',
            'url' => $this->container->get('router')->generate('admin_banner_index')
        );
        $parent = array(
            'title' => 'title.banner.management',
            'url' => $this->container->get('router')->generate('admin_banner_index')
        );
        $children = array(
            'title' => 'title.banner.show',
            'url' => ''
        );

        // This security check can also be performed:
        //   1. Using an annotation: @Security("post.isAuthor(user)")
        //   2. Using a "voter" (see http://symfony.com/doc/current/cookbook/security/voters_data_permission.html)
//        if (null === $this->getUser() || !$banner->isAuthor($this->getUser())) {
//            throw $this->createAccessDeniedException('Posts can only be shown to their authors.');
//        }
        $deleteForm = $this->createDeleteForm( $banner);

        return $this->render('admin/banner/show.html.twig', array(
            'banner'        => $banner,
            'delete_form' => $deleteForm->createView(),
            'breadCrumb' => $this->getBreadCrumb($root, $parent, $children),
            'user' => $this->getUser()
        ));
    }

    /**
     * Displays a form to edit an existing Post entity.
     *
     * @Route("/edit/{id}", requirements={"id" = "\d+"}, name="admin_banner_edit")
     * @Method({"GET", "POST"})
     * @param Banner $banner
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editAction( Banner $banner, Request $request)
    {
        $root = array(
            'title' => 'title.home',
            'url' => $this->container->get('router')->generate('admin_banner_index')
        );
        $parent = array(
            'title' => 'title.banner.management',
            'url' => $this->container->get('router')->generate('admin_banner_index')
        );
        $children = array(
            'title' => 'title.banner.edit',
            'url' => ''
        );

//        if (null === $this->getUser() || !$banner->isAuthor($this->getUser())) {
//            throw $this->createAccessDeniedException('Posts can only be edited by their authors.');
//        }     

        $editForm = $this->createForm(new BannerType(), $banner);
        $deleteForm = $this->createDeleteForm( $banner );

        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            //$banner->setSlug($this->get('slugger')->slugify($banner->getTitle()));

            $uploadedFile = $banner->getUploadedFile();
            // Create Image1
            if($uploadedFile) {

                $temp_path = __DIR__ .ConstantBundle::UPLOAD_DIR.self::UPLOAD_FOLDER;

                $path = $temp_path.$banner->getImage();

                $isUploaded = $banner->upload($temp_path);

                if($isUploaded && file_exists($path)) {
                    unlink($path);
                }
            }
            $em = $this->getDoctrine()->getManager();
            $em->persist($banner);
            $em->flush();

            return $this->redirectToRoute('admin_banner_index');
        }

        return $this->render('admin/banner/edit.html.twig', array(
            'banner'        => $banner,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'breadCrumb' => $this->getBreadCrumb($root, $parent, $children),
            'user' => $this->getUser(),
        ));
    }

    /**
     * Deletes a Post entity.
     *
     * @Route("/delete/{id}", name="admin_banner_delete")
     * @Method("DELETE")
     * @@Security("banner.isAuthor(user)")
     *
     * @param Request $request
     * @param Banner $banner
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * 
     * The Security annotation value is an expression (if it evaluates to false,
     * the authorization mechanism will prevent the user accessing this resource).
     * The isAuthor() method is defined in the AppBundle\Entity\Banner entity.
     */
    
    public function deleteAction(Request $request, Banner $banner)
    {
        $form = $this->createDeleteForm( $banner );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $uploadPath = __DIR__ .ConstantBundle::UPLOAD_DIR.self::UPLOAD_FOLDER;
            $uploadPath .= $banner->getImage();
            if(file_exists( $uploadPath ))
            {
                unlink($uploadPath);
            }
            $em = $this->getDoctrine()->getManager();
            $em->remove( $banner );
            $em->flush();
        }

        return $this->redirectToRoute( 'admin_banner_index' );
    }

    /**
     * Creates a form to delete a Post entity by id.
     *
     * This is necessary because browsers don't support HTTP methods different
     * from GET and POST. Since the controller that removes the blog posts expects
     * a DELETE method, the trick is to create a simple form that *fakes* the
     * HTTP DELETE method.
     * See http://symfony.com/doc/current/cookbook/routing/method_parameters.html.
     *
     * @param Banner $banner The $banner object
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm( Banner $banner )
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('admin_banner_delete',
                array(
                    'id' => $banner->getId()))
                )
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
