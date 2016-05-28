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
use AppBundle\Form\UserType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use AppBundle\Entity\Image;
use AppBundle\Entity\User;

/**
 * Controller used to manage blog contents in the backend.
 *
 * Please note that the application backend is developed manually for learning
 * purposes. However, in your real Symfony application you should use any of the
 * existing bundles that let you generate ready-to-use backends without effort.
 * See http://knpbundles.com/keyword/admin
 *
 * @Route("/admin/user")
 *
 * @author Ryan Weaver <weaverryan@gmail.com>
 * @author Javier Eguiluz <javier.eguiluz@gmail.com>
 */
class UserController extends BaseController
{
    public function loginAction()
    {
        $helper = $this->get('security.authentication_utils');

        return $this->render('security/login.html.twig', array(
            // last username entered by the user (if any)
            'last_username' => $helper->getLastUsername(),
            // last authentication error (if any)
            'error' => $helper->getLastAuthenticationError(),
        ));
    }
    /**
     * This is the route the login form submits to.
     *
     * But, this will never be executed. Symfony will intercept this first
     * and handle the login automatically. See form_login in app/config/security.yml
     *
     * @Route("/login_check", name="admin_user_login_check")
     */
    public function loginCheckAction()
    {
        throw new \Exception('This should never be reached!');
    }
    /**
     * Lists all Post entities.
     * @Route("/", name="admin_user_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $root = array(
            'title' => 'title.home',
            'url' => $this->container->get('router')->generate('admin_menu_index')
        );
        $parent = array(
            'title' => 'title.user.management',
            'url' => $this->container->get('router')
                ->generate('admin_user_index')
        );
        $children = array(
            'title' => 'title.user.list',
            'url' => ''
        );

        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('AppBundle:User');
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
        $users = $repo->findAll();
        //var_dump($categories);exit;

        return $this->render('admin/user/index.html.twig',
            array(
                'users' => $users,
                'breadCrumb' => $this->getBreadCrumb($root, $parent, $children),
                'user' => $this->getUser(),
            ));
    }

    /**
     * Creates a new Post entity.
     *
     * @Route("/admin/user/new", name="admin_user_new")
     * @Method({"GET", "POST"})
     * NOTE: the Method annotation is optional, but it's a recommended practice
     * to constraint the HTTP methods each controller responds to (by default
     * it responds to all methods).
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function newAction(Request $request)
    {
        $root = array(
            'title' => 'title.home',
            'url' => $this->container->get('router')->generate('admin_menu_index')
        );
        $parent = array(
            'title' => 'title.user.management',
            'url' => $this->container->get('router')
                ->generate('admin_user_index')
        );
        $children = array(
            'title' => 'title.user.new',
            'url' => ''
        );
        // 1) build the form
        $user = new User();
        $form = $this->createForm(new UserType(), $user);

        // 2) handle the submit (will only happen on POST)
        $form->handleRequest($request);
        if ($form->isValid() && $form->isSubmitted()) {
            // 3) Encode the password (you could also do this via Doctrine listener)
            $password = $this->get('security.password_encoder')
                ->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($password);

            // 4) save the User!
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            // ... do any other work - like send them an email, etc
            // maybe set a "flash" success message for the user

            return $this->redirectToRoute('admin_user_index');
        }

        return $this->render(
            'admin/user/new.html.twig',
            array(
                'form' => $form->createView(),
                'breadCrumb' => $this->getBreadCrumb($root, $parent, $children),
                'user'        => $this->getUser(),
            )
        );
    }

    /**
     * Finds and displays a Product entity.
     *
     * @Route("/detail/{id}", requirements={"id" = "\d+"}, name="admin_user_show")
     * @Method("GET")
     *
     * @param User $user
     * @return Response
     */
    public function showAction(User $user)
    {
        // This security check can also be performed:
        //   1. Using an annotation: @Security("post.isAuthor(user)")
        //   2. Using a "voter" (see http://symfony.com/doc/current/cookbook/security/voters_data_permission.html)
//        if (null === $this->getUser() || !$user->isAuthor($this->getUser())) {
//            throw $this->createAccessDeniedException('Category can only be shown to their authors.');
//        }

        $deleteForm = $this->createDeleteForm($user);

        return $this->render('admin/user/show.html.twig', array(
            'data'        => $user,
            'user'        => $this->getUser(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing Post entity.
     *
     * @Route("/{id}/edit", requirements={"id" = "\d+"}, name="admin_user_edit")
     * @Method({"GET", "POST"})
     *
     * @param User $user
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function editAction(User $user, Request $request)
    {
//        if (null === $this->getUser() || !$user->isAuthor($this->getUser())) {
//            throw $this->createAccessDeniedException('User can only be edited by their authors.');
//        }

        $em = $this->getDoctrine()->getManager();

        $editForm = $this->createForm(new UserType($em, $user, $selected_id = 24), $user);
        $deleteForm = $this->createDeleteForm($user);

        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
//            $user->setSlug($this->get('slugger')->slugify($user->getName()));
            $password = $this->get('security.password_encoder')
                ->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($password);

            $em->flush();

            return $this->redirectToRoute('admin_user_edit', array('id' => $user->getId()));
        }

        return $this->render('admin/user/edit.html.twig', array(
            'user_item'  => $user,
            'user'        => $this->getUser(),
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a User entity.
     *
     * @Route("/delete/{id}", name="admin_user_delete")
     * @Method("DELETE")
     *
     * The Security annotation value is an expression (if it evaluates to false,
     * the authorization mechanism will prevent the user accessing this resource).
     * The isAuthor() method is defined in the AppBundle\Entity\Post entity.
     *
     * @param Request $request
     * @param User $user
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction(Request $request, User $user)
    {
        $form = $this->createDeleteForm($user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($user);
            $em->flush();
        }

        return $this->redirectToRoute('admin_user_index');
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
     * @param User $user
     * @return \Symfony\Component\Form\Form
     */
    private function createDeleteForm(User $user)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('admin_user_delete', array('id' => $user->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
