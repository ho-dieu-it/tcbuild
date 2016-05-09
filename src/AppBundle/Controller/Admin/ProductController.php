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

use AppBundle\Form\CategoryType;
use AppBundle\Form\ProductType;
use AppBundle\Utils\Constant;
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
use AppBundle\Utils\ConstantBundle;

/**
 * Controller used to manage blog contents in the backend.
 *
 * Please note that the application backend is developed manually for learning
 * purposes. However, in your real Symfony application you should use any of the
 * existing bundles that let you generate ready-to-use backends without effort.
 * See http://knpbundles.com/keyword/admin
 *
 * @Security("has_role('ROLE_ADMIN')")
 *
 */
class ProductController extends Controller
{
    /**
     * @Route("/admin", name="admin_index")
     * @Route("/admin/product", name="admin_product_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $products = $em->getRepository('AppBundle:Product')->findAll();

        return $this->render('admin/product/index.html.twig',
            array(
                'products' => $products,
                'user' => $this->getUser()
            ));
    }

    /**
     * @Route("/admin/product/new/", name="admin_product_new")
     * @Method("GET")
     */
    public function newAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $categories = $em->getRepository('AppBundle:Category')->findAll();
        $product = new Product();
        $form = $this->createForm(new ProductType(), $product,array('categories'=>$categories));

        //$form->add('name');
        //$form->add('image');
        $form->handleRequest($request);
        // the isSubmitted() method is completely optional because the other
        // isValid() method already checks whether the form is submitted.
        // However, we explicitly add it to improve code readability.
        // See http://symfony.com/doc/current/best_practices/forms.html#handling-form-submits
        if ($form->isSubmitted() && $form->isValid()) {

            $product->setSlug($this->get('slugger')->slugify($product->getName()));
            $product->setAuthorEmail($this->getUser()->getEmail());

            if($product->file) {
                // Create Image
                $tmpImage = $product->file;
                $temp_path = __DIR__.ConstantBundle::UPLOAD_DIR.'/products';
                $product->file->move($temp_path, $product->file->getClientOriginalName());
                $image = new Image();
                $image->setName($tmpImage->getClientOriginalName());
                $image->setType($tmpImage->guessClientExtension());
                $image->setSize($tmpImage->getClientsize());
                $image->setIsType(1); // 1 is product type
                $product->addImage($image);
            }
            $em = $this->getDoctrine()->getManager();
            $em->persist($product);
            if($product->file) {
                $em->persist($image);
            }
            $em->flush();

            return $this->redirectToRoute('admin_product_index');
        }

        return $this->render('admin/product/new.html.twig', array(
            'product' => $product,
            'form' => $form->createView(),
            'user' => $this->getUser()
        ));
    }

    /**
     * Finds and displays a Product entity.
     *
     * @Route("/product/detail/{id}", requirements={"id" = "\d+"}, name="admin_product_show")
     * @Method("GET")
     */
    public function showAction(Product $product)
    {
        // This security check can also be performed:
        //   1. Using an annotation: @Security("post.isAuthor(user)")
        //   2. Using a "voter" (see http://symfony.com/doc/current/cookbook/security/voters_data_permission.html)
        if (null === $this->getUser() || !$product->isAuthor($this->getUser())) {
            throw $this->createAccessDeniedException('Product can only be shown to their authors.');
        }

        $deleteForm = $this->createDeleteForm($product);

        return $this->render('admin/product/show.html.twig', array(
            'product'        => $product,
            'delete_form' => $deleteForm->createView(),
            'user' => $this->getUser()
        ));
    }

    /**
     * Displays a form to edit an existing Post entity.
     *
     * @Route("/product/edit/{id}/", requirements={"id" = "\d+"}, name="admin_product_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Product $product, Request $request)
    {
        if (null === $this->getUser() || !$product->isAuthor($this->getUser())) {
            throw $this->createAccessDeniedException('Product can only be edited by their authors.');
        }

        $em = $this->getDoctrine()->getManager();

        $editForm = $this->createForm(new ProductType(), $product);
        $deleteForm = $this->createDeleteForm($product);

        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $product->setSlug($this->get('slugger')->slugify($product->getName()));
            if($product->file) {
                // Create Image
                $tmpImage = $product->file;
                $temp_path = __DIR__.ConstantBundle::UPLOAD_DIR.'/products';
                $product->file->move($temp_path, $product->file->getClientOriginalName());
                $image = new Image();
                $image->setName($tmpImage->getClientOriginalName());
                $image->setType($tmpImage->guessClientExtension());
                $image->setSize($tmpImage->getClientsize());
                $image->setIsType(1); // 1 is product type
                $product->addImage($image);
            }
            $em = $this->getDoctrine()->getManager();
            $em->persist($product);
            if($product->file) {
                $em->persist($image);
            }
            $em->flush();

            return $this->redirectToRoute('admin_product_edit', array('id' => $product->getId()));
        }

        return $this->render('admin/product/edit.html.twig', array(
            'product'        => $product,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'user' => $this->getUser()
        ));
    }

    /**
     * Deletes a Post entity.
     *
     * @Route("/{id}", name="admin_product_delete")
     * @Method("DELETE")
     * @Security("post.isAuthor(user)")
     *
     * The Security annotation value is an expression (if it evaluates to false,
     * the authorization mechanism will prevent the user accessing this resource).
     * The isAuthor() method is defined in the AppBundle\Entity\Post entity.
     */
    public function deleteAction(Request $request, Product $product)
    {
        $form = $this->createDeleteForm($product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $em->remove($product);
            $em->flush();
        }

        return $this->redirectToRoute('admin_post_index');
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
    private function createDeleteForm(Product $product)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('admin_product_delete', array('id' => $product->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
