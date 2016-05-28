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
use AppBundle\Entity\Category;
use AppBundle\Form\PageType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use AppBundle\Entity\Page;
use AppBundle\Entity\Menu;
use AppBundle\Entity\Image;
use AppBundle\Utils\ConstantBundle;


/**
 * Controller used to manage blog contents in the backend.
 *
 * Please note that the application backend is developed manually for learning
 * purposes. However, in your real Symfony application you should use any of the
 * existing bundles that let you generate ready-to-use backends without effort.
 * See http://knpbundles.com/keyword/admin
 *
 * @Route("/admin/page")
 * @Security("has_role('ROLE_ADMIN')")
 *
 * @author Ryan Weaver <weaverryan@gmail.com>
 * @author Javier Eguiluz <javier.eguiluz@gmail.com>
 */
class PageController extends BaseController
{
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
     * @Route("/{id}", name="admin_page_index")
     * @Method("GET")
     *
     * @param Menu $menu
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Menu $menu)
    {
        $root = array(
            'title' => 'title.home',
            'url' => $this->container->get('router')->generate('admin_menu_index')
        );
        $parent = array(
            'title' => 'title.menu.management',
            'url' => $this->container->get('router')->generate('admin_page_index', array('id' => $menu->getId()))
        );
        $children = array(
            'title' => 'title.menu.list',
            'url' => ''
        );

        $em = $this->getDoctrine()->getManager();
        $pages = $em->getRepository('AppBundle:Page')->findPageByMenu($menu);

        return $this->render('admin/page/index.html.twig',
            array(
                'pages' => $pages,
                'menu' => $menu,
                'breadCrumb' => $this->getBreadCrumb($root, $parent, $children),
                'user' => $this->getUser()
            ));
    }

    /**
     * Creates a new Page entity.
     *
     * @Route("/{id}/new", name="admin_page_new")
     * @Method({"GET", "POST"})
     *
     * NOTE: the Method annotation is optional, but it's a recommended practice
     * to constraint the HTTP methods each controller responds to (by default
     * it responds to all methods).
     *
     * @param Menu $menu
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function newAction(Menu $menu,Request $request)
    {
        $root = array(
            'title' => 'title.home',
            'url' => $this->container->get('router')->generate('admin_menu_index')
        );
        $parent = array(
            'title' => 'title.menu.management',
            'url' => $this->container->get('router')->generate('admin_page_index')
        );
        $children = array(
            'title' => 'title.menu.list',
            'url' => ''
        );

        $page = new Page();
        $page->setAuthorEmail($this->getUser()->getEmail());
            $form = $this->createForm(new PageType(), $page);

        $form->handleRequest($request);

        // the isSubmitted() method is completely optional because the other
        // isValid() method already checks whether the form is submitted.
        // However, we explicitly add it to improve code readability.
        // See http://symfony.com/doc/current/best_practices/forms.html#handling-form-submits
        if ($form->isSubmitted() && $form->isValid()) {
            $page->setSlug($this->get('slugger')->slugify($page->getTitle()));

            $page->setMenu($menu);

            if($menu->getSlug() === 'tin-tuc'){
                $folder = '/news';
            }else if($menu->getSlug() === 'nganh-nghe'){
                $folder = '/services';
            }else{
                $folder = '/pages';
            }

            if($page->getFiles()) {

                $temp_path = __DIR__ .ConstantBundle::UPLOAD_DIR. $folder;

                $isUploaded = $page->upload($temp_path);

                if($isUploaded) {
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($page);
                    $em->flush();
                }
            }



            return $this->redirectToRoute('admin_page_index', array('id' =>$menu->getId()));
        }

        return $this->render('admin/page/new.html.twig', array(
            'page' => $page,
            'menu_id' => $menu->getId(),
            'form' => $form->createView(),
            'breadCrumb' => $this->getBreadCrumb($root, $parent, $children),
            'user' => $this->getUser(),
        ));
    }

    /**
     * Finds and displays a Post entity.
     *
     * @Route("/show/{id}", requirements={"id" = "\d+"}, name="admin_page_show")
     * @Method("GET")
     */
    public function showAction( Page $page )
    {
        $root = array(
            'title' => 'title.home',
            'url' => $this->container->get('router')->generate('admin_menu_index')
        );
        $parent = array(
            'title' => 'title.menu.management',
            'url' => $this->container->get('router')
                ->generate('admin_page_index', array('id' => $page->getMenu()->getId()))
        );
        $children = array(
            'title' => 'title.menu.show',
            'url' => ''
        );
        // This security check can also be performed:
        //   1. Using an annotation: @Security("post.isAuthor(user)")
        //   2. Using a "voter" (see http://symfony.com/doc/current/cookbook/security/voters_data_permission.html)
//        if (null === $this->getUser() || !$page->isAuthor($this->getUser())) {
//            throw $this->createAccessDeniedException('Posts can only be shown to their authors.');
//        }
        $menu_id = $page->getMenu()->getId();
        $deleteForm = $this->createDeleteForm($menu_id, $page);

        return $this->render('admin/page/show.html.twig', array(
            'page'        => $page,
            'menu_id'        => $menu_id,
            'delete_form' => $deleteForm->createView(),
            'breadCrumb' => $this->getBreadCrumb($root, $parent, $children),
            'user' => $this->getUser()
        ));
    }

    /**
     * Displays a form to edit an existing Post entity.
     *
     * @Route("/edit/{id}", requirements={"id" = "\d+"}, name="admin_page_edit")
     * @Method({"GET", "POST"})
     *
     * @param Page $page
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editAction( Page $page, Request $request)
    {
        $root = array(
            'title' => 'title.home',
            'url' => $this->container->get('router')->generate('admin_menu_index')
        );
        $parent = array(
            'title' => 'title.menu.management',
            'url' => $this->container->get('router')
                ->generate('admin_page_index', array('id' => $page->getMenu()->getId()))
        );
        $children = array(
            'title' => 'title.menu.edit',
            'url' => ''
        );

//        if (null === $this->getUser() || !$page->isAuthor($this->getUser())) {
//            throw $this->createAccessDeniedException('Posts can only be edited by their authors.');
//        }
        $menuId = $page->getMenu()->getId();
        $em = $this->getDoctrine()->getManager();
        $menu = $em->getRepository('AppBundle:Menu')->find($page->getMenu()->getId());

        $editForm = $this->createForm(new PageType(), $page);
        $deleteForm = $this->createDeleteForm($menuId, $page);

        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            //$page->setSlug($this->get('slugger')->slugify($page->getTitle()));
            if($menu->getSlug() == "tin-tuc"){
                $folder = '/news';
            }else if($menu->getSlug() == "nghanh-nghe"){
                $folder = '/services';
            }else{
                $folder = '/pages';
            }
            // Create Image
            if(!$page->getFiles()) {

                $temp_path = __DIR__ .ConstantBundle::UPLOAD_DIR. $folder;

                $isUploaded = $page->upload($temp_path);

                if($isUploaded) {
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($page);
                    $em->flush();
                }
            }

            $em->flush();

            return $this->redirectToRoute('admin_page_edit',
                array(
                    'id' => $page->getId(),
                    'user' => $this->getUser()
                ));
        }

        return $this->render('admin/page/edit.html.twig', array(
            'page'        => $page,
            'menu_id'        => $menuId,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'breadCrumb' => $this->getBreadCrumb($root, $parent, $children),
            'user' => $this->getUser(),
        ));
    }

    /**
     * Deletes a Post entity.
     *
     * @Route("/{menu_id}/delete/{id}", name="admin_page_delete")
     * @Method("DELETE")
     * @Security("page.isAuthor(user)")
     *
     * The Security annotation value is an expression (if it evaluates to false,
     * the authorization mechanism will prevent the user accessing this resource).
     * The isAuthor() method is defined in the AppBundle\Entity\Post entity.
     */
    public function deleteAction(Request $request, $menu_id, Page $page)
    {
        $form = $this->createDeleteForm($menu_id, $page);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $em->remove($page);
            $em->flush();
        }

        return $this->redirectToRoute('admin_page_index', array('id' => $menu_id ));
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
     * @param Post $post The post object
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($menu_id, Page $page)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('admin_page_delete',
                array(
                    'menu_id' => $menu_id,
                    'id' => $page->getId()))
                )
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
