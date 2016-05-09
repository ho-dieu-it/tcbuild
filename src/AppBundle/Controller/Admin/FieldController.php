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
use AppBundle\Form\MenuType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use AppBundle\Entity\Menu;
use AppBundle\Entity\Page;
use AppBundle\Form\PageType;
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
 * @Route("/admin/field")
 * @Security("has_role('ROLE_ADMIN')")
 *
 * @author Ryan Weaver <weaverryan@gmail.com>
 * @author Javier Eguiluz <javier.eguiluz@gmail.com>
 */
class FieldController extends BaseController
{
    const TYPE = 1;// 1 : Field of company
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
     * @Route("/category", name="admin_field_category_index")
     * @Method("GET")
     */
    public function indexAction()
    {

        $em = $this->getDoctrine()->getManager();
        $menus = $em->getRepository('AppBundle:Menu')->findMenuByType($this::TYPE);

        return $this->render('admin/field/category/index.html.twig',
            array(
                'menus' => $menus,
                'user' => $this->getUser()
            ));
    }

    /**     
     * @Route("/{id}", name="admin_field_list")
     * @Method("GET")
     */
    public function fieldListAction(Menu $menu)
    {

        $em = $this->getDoctrine()->getManager();
        $pages = $em->getRepository('AppBundle:Page')->findPageByMenu($menu);

        return $this->render('admin/field/index.html.twig',
            array(
                'pages' => $pages,
                'menu' => $menu,
                'user' => $this->getUser()
            ));
    }

    /**
     * Creates a new Page entity.
     *
     * @Route("/{id}/new", name="admin_field_new")
     * @Method({"GET", "POST"})
     *
     * NOTE: the Method annotation is optional, but it's a recommended practice
     * to constraint the HTTP methods each controller responds to (by default
     * it responds to all methods).
     */
    public function newFieldAction(Menu $menu,Request $request)
    {
        $page = new Page();
        $page->setAuthorEmail($this->getUser()->getEmail());
        $form = $this->createForm(new PageType(), $page);

        $form->handleRequest($request);

        // the isSubmitted() method is completely optional because the other
        // isValid() method already checks whether the form is submitted.
        // However, we explicitly add it to improve code readability.
        // See http://symfony.com/doc/current/best_practices/forms.html#handling-form-submits
        if ($form->isSubmitted() && $form->isValid()) {
            //$page->setSlug($this->get('slugger')->slugify($page->getTitle()));

            $page->setMenu($menu);

            $folder = '/fields';


            // Create Image
            if(isset($page->file)) {
                $tmpImage = $page->file;
                $temp_path = __DIR__ .ConstantBundle::UPLOAD_DIR. $folder;
                $page->file->move($temp_path, $page->file->getClientOriginalName());
                $image = new Image();
                $image->setName($tmpImage->getClientOriginalName());
                $image->setType($tmpImage->guessClientExtension());
                $image->setSize($tmpImage->getClientsize());
                $image->setIsType(2); // 2 is page type
                $page->addImage($image);
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($page);
            if(isset($page->file)) {
                $em->persist($image);
            }
            $em->flush();

            return $this->redirectToRoute('admin_field_list', array('id' =>$menu->getId()));
        }

        return $this->render('admin/field/new.html.twig', array(
            'page' => $page,
            'menu_id' => $menu->getId(),
            'form' => $form->createView(),
            'user' => $this->getUser(),
        ));
    }

    /**
     * Finds and displays a Post entity.
     *
     * @Route("/{menu_id}/show/{id}", requirements={"id" = "\d+", "menu_id" = "\d+"}, name="admin_field_show")
     * @Method("GET")
     */
    public function showFieldAction($menu_id, Page $page)
    {
        // This security check can also be performed:
        //   1. Using an annotation: @Security("post.isAuthor(user)")
        //   2. Using a "voter" (see http://symfony.com/doc/current/cookbook/security/voters_data_permission.html)
//        if (null === $this->getUser() || !$page->isAuthor($this->getUser())) {
//            throw $this->createAccessDeniedException('Posts can only be shown to their authors.');
//        }

        $deleteForm = $this->createDeleteForm($menu_id, $page);

        return $this->render('admin/field/show.html.twig', array(
            'page'        => $page,
            'menu_id'        => $menu_id,
            'delete_form' => $deleteForm->createView(),
            'user' => $this->getUser()
        ));
    }

    /**
     * Displays a form to edit an existing Post entity.
     *
     * @Route("/{menu_id}/edit/{id}", requirements={"id" = "\d+"}, name="admin_field_edit")
     * @Method({"GET", "POST"})
     */
    public function editFieldAction($menu_id, Page $page, Request $request)
    {
//        if (null === $this->getUser() || !$page->isAuthor($this->getUser())) {
//            throw $this->createAccessDeniedException('Posts can only be edited by their authors.');
//        }
        $em = $this->getDoctrine()->getManager();
        $menu = $em->getRepository('AppBundle:Menu')->find($menu_id);

        $editForm = $this->createForm(new PageType(), $page);
        $deleteForm = $this->createFieldDeleteForm($menu_id, $page);

        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            //$page->setSlug($this->get('slugger')->slugify($page->getTitle()));
            if($menu->getSlug() == "tin-tuc"){
                $folder = '/news';
            }else if($menu->getSlug() == "nghanh-nghe"){
                $folder = '/services';
            }else{
                $folder = '/fields';
            }
            // Create Image
            if(isset($page->file)) {
                $tmpImage = $page->file;
                $temp_path = __DIR__ .ConstantBundle::UPLOAD_DIR. $folder;
                $page->file->move($temp_path, $page->file->getClientOriginalName());
                $image = new Image();
                $image->setName($tmpImage->getClientOriginalName());
                $image->setType($tmpImage->guessClientExtension());
                $image->setSize($tmpImage->getClientsize());
                $image->setIsType(2); // 2 is page type
                $page->addImage($image);
            }

            $em->persist($page);
            if(isset($page->file)) {
                $em->persist($image);
            }

            $em->flush();

            return $this->redirectToRoute('admin_field_edit',
                array(
                    'menu_id'=> $menu_id,
                    'id' => $page->getId(),
                    'user' => $this->getUser()
                ));
        }

        return $this->render('admin/field/edit.html.twig', array(
            'page'        => $page,
            'menu_id'        => $menu_id,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'user' => $this->getUser(),
        ));
    }

    /**
     * Deletes a Post entity.
     *
     * @Route("/{menu_id}/delete/{id}", name="admin_field_delete")
     * @Method("DELETE")
     * @Security("page.isAuthor(user)")
     *
     * The Security annotation value is an expression (if it evaluates to false,
     * the authorization mechanism will prevent the user accessing this resource).
     * The isAuthor() method is defined in the AppBundle\Entity\Post entity.
     */
    public function deleteFieldAction(Request $request, $menu_id, Page $page)
    {
        $form = $this->createFieldDeleteForm($menu_id, $page);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $em->remove($page);
            $em->flush();
        }

        return $this->redirectToRoute('admin_field_list', array('id' => $menu_id ));
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
    private function createFieldDeleteForm($menu_id, Page $page)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('admin_field_delete',
                array(
                    'menu_id' => $menu_id,
                    'id' => $page->getId()))
            )
            ->setMethod('DELETE')
            ->getForm()
            ;
    }

    /**
     * Creates a new Page entity.
     *
     * @Route("/category/new", name="admin_field_category_new")
     * @Method({"GET", "POST"})
     *
     * NOTE: the Method annotation is optional, but it's a recommended practice
     * to constraint the HTTP methods each controller responds to (by default
     * it responds to all methods).
     */
    public function newAction(Request $request)
    {
        $menu = new Menu();
        $menu->setAuthorEmail($this->getUser()->getEmail());
            $form = $this->createForm(new MenuType(), $menu);

        $form->handleRequest($request);

        // the isSubmitted() method is completely optional because the other
        // isValid() method already checks whether the form is submitted.
        // However, we explicitly add it to improve code readability.
        // See http://symfony.com/doc/current/best_practices/forms.html#handling-form-submits
        if ($form->isSubmitted() && $form->isValid()) {
            //$menu->setSlug($this->get('slugger')->slugify($menu->getTitle()));
            $menu->setType($this::TYPE);
            $em = $this->getDoctrine()->getManager();
            $em->persist($menu);
            $em->flush();

            return $this->redirectToRoute('admin_field_category_index');
        }

        return $this->render('admin/field/category/new.html.twig', array(
            'menu' => $menu,
            'form' => $form->createView(),
            'user' => $this->getUser(),
        ));
    }

    /**
     * Finds and displays a Post entity.
     *
     * @Route("/category/{id}", requirements={"id" = "\d+"}, name="admin_field_category_show")
     * @Method("GET")
     */
    public function showAction(Menu $menu)
    {
        // This security check can also be performed:
        //   1. Using an annotation: @Security("post.isAuthor(user)")
        //   2. Using a "voter" (see http://symfony.com/doc/current/cookbook/security/voters_data_permission.html)
        if (null === $this->getUser() || !$menu->isAuthor($this->getUser())) {
            throw $this->createAccessDeniedException('Posts can only be shown to their authors.');
        }

        $deleteForm = $this->createDeleteForm($menu);

        return $this->render('admin/field/category/show.html.twig', array(
            'data'        => $menu,
            'user' => $this->getUser(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing Post entity.
     *
     * @Route("/category/{id}/edit", requirements={"id" = "\d+"}, name="admin_field_category_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(FieldCategory $field_cate, Request $request)
    {
//        if (null === $this->getUser() || !$menu->isAuthor($this->getUser())) {
//            throw $this->createAccessDeniedException('Posts can only be edited by their authors.');
//        }

        $em = $this->getDoctrine()->getManager();

        $editForm = $this->createForm(new FieldCategoryType(), $field_cate);
        $deleteForm = $this->createDeleteForm($field_cate);

        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            //$menu->setSlug($this->get('slugger')->slugify($menu->getTitle()));
            $em->flush();

            return $this->redirectToRoute('admin_field_edit', array('id' => $field_cate->getId()));
        }

        return $this->render('admin/menu/edit.html.twig', array(
            'menu'        => $field_cate,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'user' => $this->getUser(),
        ));
    }

    /**
     * Deletes a Post entity.
     *
     * @Route("/{id}", name="admin_category_field_delete")
     * @Method("DELETE")
     * @Security("post.isAuthor(user)")
     *
     * The Security annotation value is an expression (if it evaluates to false,
     * the authorization mechanism will prevent the user accessing this resource).
     * The isAuthor() method is defined in the AppBundle\Entity\Post entity.
     */
    public function deleteAction(Request $request, FieldCategory $field_cate)
    {
        $form = $this->createDeleteForm($field_cate);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $em->remove($field_cate);
            $em->flush();
        }

        return $this->redirectToRoute('admin_category_field_index');
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
    private function createDeleteForm(Menu $menu)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('admin_post_delete', array('id' => $menu->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
