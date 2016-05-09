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
use AppBundle\Form\CategoryType;
use AppBundle\Form\ProductType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use AppBundle\Entity\Product;
use AppBundle\Entity\Category;
use AppBundle\Entity\Image;

/**
 * Controller used to manage blog contents in the backend.
 *
 * Please note that the application backend is developed manually for learning
 * purposes. However, in your real Symfony application you should use any of the
 * existing bundles that let you generate ready-to-use backends without effort.
 * See http://knpbundles.com/keyword/admin
 *
 * @Route("/admin/category")
 * @Security("has_role('ROLE_ADMIN')")
 *
 * @author Ryan Weaver <weaverryan@gmail.com>
 * @author Javier Eguiluz <javier.eguiluz@gmail.com>
 */
class CategoryController extends BaseController
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
     * @Route("/", name="admin_index")
     * @Route("/", name="admin_category_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('AppBundle:Category');
//        $query = $em
//            ->createQueryBuilder()
//            ->select('category')
//            ->from('AppBundle:Category', 'category')
//            ->orderBy('category.root, category.lft', 'ASC')
//            ->where('category.root = 1')
//            ->getQuery()
//        ;
//        $options = array('decorate' => true);
//        $categories = $repo->buildTree($query->ge//
        $categories = $repo->getNodesHierarchy();
        //var_dump($categories);exit;

        return $this->render('admin/category/index.html.twig',
            array(
                'categories' => $categories,
                'user' =>$this->getUser(),
            ));
    }

    /**
     * Creates a new Post entity.
     *
     * @Route("/new", name="admin_category_new")
     * @Method({"GET", "POST"})
     * NOTE: the Method annotation is optional, but it's a recommended practice
     * to constraint the HTTP methods each controller responds to (by default
     * it responds to all methods).
     */
    public function newAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $category = new Category();

        $form = $this->createForm(new CategoryType($em, $category), $category);

        $form->handleRequest($request);



        // the isSubmitted() method is completely optional because the other
        // isValid() method already checks whether the form is submitted.
        // However, we explicitly add it to improve code readability.
        // See http://symfony.com/doc/current/best_practices/forms.html#handling-form-submits
        if ($form->isSubmitted() && $form->isValid()) {
            //var_dump('newAction',$request->request->get('app_category')['parent'][0]);exit;
            $app_category = $request->request->get('app_category');
            if( $app_category['parent'] ){
                $parent_id = ($request->request->get('app_category'));
                $repo = $em->getRepository('AppBundle:Category');
                $parent = $repo->findOneById($parent_id['parent']);
                $category->setParent($parent);
                $em->persist($parent);
            }
            $category->setAuthorEmail($this->getUser()->getEmail());
            $em->persist($category);
            $em->flush();

            return $this->redirectToRoute('admin_category_index');
        }

        return $this->render('admin/category/new.html.twig', array(
            'category' => $category,
            'form' => $form->createView(),
            'user' => $this->getUser(),
        ));
    }

    /**
     * Finds and displays a Product entity.
     *
     * @Route("/{id}", requirements={"id" = "\d+"}, name="admin_category_show")
     * @Method("GET")
     */
    public function showAction(Category $category)
    {
        // This security check can also be performed:
        //   1. Using an annotation: @Security("post.isAuthor(user)")
        //   2. Using a "voter" (see http://symfony.com/doc/current/cookbook/security/voters_data_permission.html)
        if (null === $this->getUser() || !$category->isAuthor($this->getUser())) {
            throw $this->createAccessDeniedException('Category can only be shown to their authors.');
        }

        $deleteForm = $this->createDeleteForm($category);

        return $this->render('admin/category/show.html.twig', array(
            'category'        => $category,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing Post entity.
     *
     * @Route("/{id}/edit", requirements={"id" = "\d+"}, name="admin_category_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Category $category, Request $request)
    {
//        if (null === $this->getUser() || !$category->isAuthor($this->getUser())) {
//            throw $this->createAccessDeniedException('Category can only be edited by their authors.');
//        }

        $em = $this->getDoctrine()->getManager();

        $editForm = $this->createForm(new CategoryType($em, $category, $selected_id = 24), $category);
        $deleteForm = $this->createDeleteForm($category);

        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $category->setSlug($this->get('slugger')->slugify($category->getName()));
            $em->flush();

            return $this->redirectToRoute('admin_category_edit', array('id' => $category->getId()));
        }

        return $this->render('admin/category/edit.html.twig', array(
            'category'        => $category,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'user' => $this->getUser(),
        ));
    }

    /**
     * Deletes a Post entity.
     *
     * @Route("/{id}", name="admin_category_delete")
     * @Method("DELETE")
     * @Security("category.isAuthor(user)")
     *
     * The Security annotation value is an expression (if it evaluates to false,
     * the authorization mechanism will prevent the user accessing this resource).
     * The isAuthor() method is defined in the AppBundle\Entity\Post entity.
     */
    public function deleteAction(Request $request, Category $category)
    {
        $form = $this->createDeleteForm($category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $em->remove($category);
            $em->flush();
        }

        return $this->redirectToRoute('admin_category_index');
    }

    /**product
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
    private function createDeleteForm(Category $category)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('admin_category_delete', array('id' => $category->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
