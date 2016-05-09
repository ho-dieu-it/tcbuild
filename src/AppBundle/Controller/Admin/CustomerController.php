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
use AppBundle\Form\CustomerType;
use AppBundle\Entity\Customer;
use AppBundle\Utils\ConstantBundle;


/**
 * Controller used to manage blog contents in the backend.
 *
 * Please note that the application backend is developed manually for learning
 * purposes. However, in your real Symfony application you should use any of the
 * existing bundles that let you generate ready-to-use backends without effort.
 * See http://knpbundles.com/keyword/admin
 *
 * @Route("/admin/customer")
 * @Security("has_role('ROLE_ADMIN')")
 *
 * @author Ryan Weaver <weaverryan@gmail.com>
 * @author Javier Eguiluz <javier.eguiluz@gmail.com>
 */
class CustomerController extends BaseController
{
    CONST UPLOAD_FOLDER = '/customers/';
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
     * @Route("/", name="admin_customer_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $customers = $em->getRepository('AppBundle:Customer')->findAll();

        return $this->render('admin/customer/index.html.twig',
            array(
                'customers' => $customers,
                'user' => $this->getUser()
            ));
    }

    /**
     * Creates a new Page entity.
     *
     * @Route("/new", name="admin_customer_new")
     * @Method({"GET", "POST"})
     * NOTE: the Method annotation is optional, but it's a recommended practice
     * to constraint the HTTP methods each controller responds to (by default
     * it responds to all methods).
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function newAction(Request $request)
    {
        $customer = new Customer();
        $authorEmail = $this->getUser()->getEmail();
        $customer->setAuthorEmail( $authorEmail );

        $form = $this->createForm(new CustomerType(), $customer);

        $form->handleRequest($request);

        // the isSubmitted() method is completely optional because the other
        // isValid() method already checks whether the form is submitted.
        // However, we explicitly add it to improve code readability.
        // See http://symfony.com/doc/current/best_practices/forms.html#handling-form-submits
        if ($form->isSubmitted() && $form->isValid()) 
        {
            $uploadedFile = $customer->getUploadedFile();
            if($uploadedFile) {

                $temp_path = __DIR__ .ConstantBundle::UPLOAD_DIR.self::UPLOAD_FOLDER;

                $isUploaded = $customer->upload($temp_path);

                if($isUploaded) {
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($customer);
                    $em->flush();
                }
            }

            return $this->redirectToRoute('admin_customer_index');
        }

        return $this->render('admin/customer/new.html.twig', array(
            'customer' => $customer,
            'form' => $form->createView(),
            'user' => $this->getUser(),
        ));
    }

    /**
     * Finds and displays a Post entity.
     *
     * @Route("/show/{id}", requirements={"id" = "\d+"}, name="admin_customer_show")
     * @Method("GET")
     *
     * @param Customer $customer
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showAction( Customer $customer )
    {
        // This security check can also be performed:
        //   1. Using an annotation: @Security("post.isAuthor(user)")
        //   2. Using a "voter" (see http://symfony.com/doc/current/cookbook/security/voters_data_permission.html)
//        if (null === $this->getUser() || !$customer->isAuthor($this->getUser())) {
//            throw $this->createAccessDeniedException('Posts can only be shown to their authors.');
//        }
        $deleteForm = $this->createDeleteForm( $customer);

        return $this->render('admin/customer/show.html.twig', array(
            'customer'        => $customer,
            'delete_form' => $deleteForm->createView(),
            'user' => $this->getUser()
        ));
    }

    /**
     * Displays a form to edit an existing Post entity.
     *
     * @Route("/edit/{id}", requirements={"id" = "\d+"}, name="admin_customer_edit")
     * @Method({"GET", "POST"})
     * @param Customer $customer
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editAction( Customer $customer, Request $request)
    {
//        if (null === $this->getUser() || !$customer->isAuthor($this->getUser())) {
//            throw $this->createAccessDeniedException('Posts can only be edited by their authors.');
//        }

        $em = $this->getDoctrine()->getManager();

        $editForm = $this->createForm(new CustomerType(), $customer);
        $deleteForm = $this->createDeleteForm( $customer );

        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            //$customer->setSlug($this->get('slugger')->slugify($customer->getTitle()));

            $uploadedFile = $customer->getUploadedFile();
            // Create Image1
            if($uploadedFile) {

                $temp_path = __DIR__ .ConstantBundle::UPLOAD_DIR.self::UPLOAD_FOLDER;

                $path = $temp_path.$customer->getLogo();

                $isUploaded = $customer->upload($temp_path);

                if($isUploaded && file_exists($path)) {
                    unlink($path);
                }
            }
            $em = $this->getDoctrine()->getManager();
            $em->persist($customer);
            $em->flush();

            return $this->redirectToRoute('admin_customer_index');
        }

        return $this->render('admin/customer/index.html.twig', array(
            'customer'        => $customer,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'user' => $this->getUser(),
        ));
    }

    /**
     * Deletes a Post entity.
     *
     * @Route("/delete/{id}", name="admin_customer_delete")
     * @Method("DELETE")
     * @Security("customer.isAuthor(user)")
     *
     * @param Request $request
     * @param Customer $customer
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * 
     * The Security annotation value is an expression (if it evaluates to false,
     * the authorization mechanism will prevent the user accessing this resource).
     * The isAuthor() method is defined in the AppBundle\Entity\Customer entity.
     */
    
    public function deleteAction(Request $request, Customer $customer)
    {
        $form = $this->createDeleteForm( $customer );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $uploadPath = __DIR__ .ConstantBundle::UPLOAD_DIR.self::UPLOAD_FOLDER;
            $uploadPath .= $customer->getLogo();
            if(file_exists( $uploadPath ))
            {
                unlink($uploadPath);
            }
            $em = $this->getDoctrine()->getManager();
            $em->remove( $customer );
            $em->flush();
        }

        return $this->redirectToRoute( 'admin_customer_index' );
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
     * @param Customer $customer The $customer object
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm( Customer $customer )
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('admin_customer_delete',
                array(
                    'id' => $customer->getId()))
                )
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
