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
use AppBundle\Entity\PostCategory;
use AppBundle\Entity\Post;
use AppBundle\Form\PostType;
use AppBundle\Entity\Image;
use AppBundle\Utils\ConstantBundle;
use AppBundle\Utils\Paginator;
use AppBundle\Form\PostCategoryType;
use AppBundle\Utils\CF;
use AppBundle\Utils\Slugger;

/**
 * Controller used to manage blog contents in the backend.
 *
 * Please note that the application backend is developed manually for learning
 * purposes. However, in your real Symfony application you should use any of the
 * existing bundles that let you generate ready-to-use backends without effort.
 * See http://knpbundles.com/keyword/admin
 *
 * @Route("/admin/post")
 * @Security("has_role('ROLE_ADMIN')")
 *
 * @author Ryan Weaver <weaverryan@gmail.com>
 * @author Javier Eguiluz <javier.eguiluz@gmail.com>
 */
class PostController extends BaseController
{
    const TYPE = 1;// 1 : news
    CONST UPLOAD_FOLDER = '/posts/';
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
     * @Route("/{type}/category", name="admin_post_category_index")
     * @Method("GET")
     *
     * @param $type
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction($type)
    {
        $root = array(
            'title' => 'title.home',
            'url' => $this->container->get('router')->generate('admin_menu_index')
        );
        $typeObj = CF::getType($type);
        $parent = array(
            'title' => $typeObj['name'].'.management',
            'url' => $this->container->get('router')
                ->generate('admin_post_index', array('type'=>$type))
        );
        $children = array(
            'title' => $typeObj['name'].'.new',
            'url' => ''
        );

        $em = $this->getDoctrine()->getManager();
        $categories = $em->getRepository('AppBundle:PostCategory')->findByType($type);

        return $this->render('admin/post/category/index.html.twig',
            array(
                'categories' => $categories,
                'user' => $this->getUser(),
                'breadCrumb' => $this->getBreadCrumb($root, $parent, $children),
                'type' => CF::getType($type)
            ));
    }

    /**     
     * @Route("/{id}/list", name="admin_post_list")
     * @Method("GET")
     *
     * @param PostCategory $category
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function postListAction(PostCategory $category)
    {
        $type = 1;
        $root = array(
            'title' => 'title.home',
            'url' => $this->container->get('router')->generate('admin_menu_index')
        );
        $parent = array(
            'title' => 'title.category.management',
            'url' => $this->container->get('router')->generate('admin_post_category_index', array('type'=>$type))
        );
        $children = array(
            'title' => 'title.category.list',
            'url' => ''
        );

        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('AppBundle:Post');
        $repoCategory = $em->getRepository('AppBundle:PostCategory');

        $countProducts = $repo->getCountPostsByCateType($category->getType());

        $paginator = new Paginator($countProducts, $pageNum = 1 , $limit = 5);

        $pageNum = $paginator->getNumPages();

        $posts = $repo->findPostsWithPagingByCate(
            $category,
            $paginator->getOffset(),
            $paginator->getLimit()
        );
        return $this->render('admin/post/index.html.twig',
            array(
                'pageNum' => $pageNum,
                'posts' => $posts,
                'category' => $category,
                'type' => CF::getType($category->getType()),
                'breadCrumb' => $this->getBreadCrumb($root, $parent, $children),
                'user' => $this->getUser()
            ));
    }

    /**
     * Creates a new Page entity.
     *
     * @Route("/{type}/category/new", name="admin_post_category_new")
     * @Method({"GET", "POST"})
     *
     * NOTE: the Method annotation is optional, but it's a recommended practice
     * to constraint the HTTP methods each controller responds to (by default
     * it responds to all methods).
     *
     * @param Request $request
     * @param $type
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function newPostCategoryAction(Request $request, $type)
    {
        $root = array(
            'title' => 'title.home',
            'url' => $this->container->get('router')->generate('admin_menu_index')
        );
        $type = CF::getType($type);
        $parent = array(
            'title' => $type['name'].'.management',
            'url' => $this->container->get('router')
                ->generate('admin_post_index', array('type'=>$type))
        );
        $children = array(
            'title' => $type['name'].'.new',
            'url' => ''
        );

        $em = $this->getDoctrine()->getManager();

        $category = new PostCategory();

        $form = $this->createForm(new PostCategoryType($em, $category), $category);

        $form->handleRequest($request);

        // the isSubmitted() method is completely optional because the other
        // isValid() method already checks whether the form is submitted.
        // However, we explicitly add it to improve code readability.
        // See http://symfony.com/doc/current/best_practices/forms.html#handling-form-submits
        if ($form->isSubmitted() && $form->isValid()) {

            $category->setAuthorEmail($this->getUser()->getEmail());
            $category->setType($type['code']);
            $category->setSlug(Slugger::slugify($category->getName()));
            $em->persist($category);
            $em->flush();

            return $this->redirectToRoute('admin_post_category_index', array('type'=> $category->getType()));
        }

        return $this->render('admin/post/category/new.html.twig', array(
            'category' => $category,
            'form' => $form->createView(),
            'type' => $type,
            'breadCrumb' => $this->getBreadCrumb($root, $parent, $children),
            'user' => $this->getUser(),
        ));
    }


    /**
     * Creates a new Page entity.
     *
     * @Route("/{id}/new", name="admin_post_new")
     * @Method({"GET", "POST"})
     *
     * NOTE: the Method annotation is optional, but it's a recommended practice
     * to constraint the HTTP methods each controller responds to (by default
     * it responds to all methods).
     *
     * @param PostCategory $category
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function newPostAction(PostCategory $category,Request $request)
    {
        $root = array(
            'title' => 'title.home',
            'url' => $this->container->get('router')->generate('admin_menu_index')
        );
        $parent = array(
            'title' => 'title.category.management',
            'url' => $this->container->get('router')
                ->generate('admin_post_index', array('type'=>$category->getType()))
        );
        $children = array(
            'title' => 'title.category.list',
            'url' => ''
        );

        $post = new Post();
        $post->setAuthorEmail($this->getUser()->getEmail());
        $form = $this->createForm(new PostType(), $post);

        $form->handleRequest($request);

        // the isSubmitted() method is completely optional because the other
        // isValid() method already checks whether the form is submitted.
        // However, we explicitly add it to improve code readability.
        // See http://symfony.com/doc/current/best_practices/forms.html#handling-form-submits
        if ($form->isSubmitted() && $form->isValid()) {
            //$page->setSlug($this->get('slugger')->slugify($page->getTitle()));

            $post->setPostCategory($category);
            $post->setSlug(Slugger::slugify($post->getTitle()));

            // Create Image
            if($post->getFiles()) {

                $temp_path = __DIR__ .ConstantBundle::UPLOAD_DIR.self::UPLOAD_FOLDER;

                $isUploaded = $post->upload($temp_path);
            }
            
            if($isUploaded) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($post);
                $em->flush();
            }


            return $this->redirectToRoute('admin_post_list', array('id' =>$category->getId()));
        }

        return $this->render('admin/post/new.html.twig', array(
            'page' => $post,
            'category' => $category,
            'type' => CF::getType($category->getType()),
            'form' => $form->createView(),
            'breadCrumb' => $this->getBreadCrumb($root, $parent, $children),
            'user' => $this->getUser(),
        ));
    }

    /**
     * Finds and displays a Post entity.
     *
     * @Route("/category/show/{id}", requirements={"id" = "\d+"}, name="admin_post_category_show")
     * @Method("GET")
     *
     * @param PostCategory $category
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showPostCategoryAction(PostCategory $category)
    {
        $root = array(
            'title' => 'title.home',
            'url' => $this->container->get('router')->generate('admin_menu_index')
        );
        $parent = array(
            'title' => 'title.category.management',
            'url' => $this->container->get('router')
                ->generate('admin_post_category_index', array('type'=>$category->getType()))
        );
        $children = array(
            'title' => 'title.category.list',
            'url' => ''
        );

        // This security check can also be performed:
        //   1. Using an annotation: @Security("post.isAuthor(user)")
        //   2. Using a "voter" (see http://symfony.com/doc/current/cookbook/security/voters_data_permission.html)
//        if (null === $this->getUser() || !$page->isAuthor($this->getUser())) {
//            throw $this->createAccessDeniedException('Posts can only be shown to their authors.');
//        }

        $deleteForm = $this->createCategoryDeleteForm($category);

        return $this->render('admin/post/category/show.html.twig', array(
            'category' => $category,
            'delete_form' => $deleteForm->createView(),
            'breadCrumb' => $this->getBreadCrumb($root, $parent, $children),
            'user' => $this->getUser()
        ));
    }

    /**
     * Finds and displays a Post entity.
     *
     * @Route("/show/{id}", requirements={"id" = "\d+"}, name="admin_post_show")
     * @Method("GET")
     */
    public function showPostAction(Post $post)
    {
        // This security check can also be performed:
        //   1. Using an annotation: @Security("post.isAuthor(user)")
        //   2. Using a "voter" (see http://symfony.com/doc/current/cookbook/security/voters_data_permission.html)
//        if (null === $this->getUser() || !$page->isAuthor($this->getUser())) {
//            throw $this->createAccessDeniedException('Posts can only be shown to their authors.');
//        }

        $deleteForm = $this->createPostDeleteForm($post);
        $category = $post->getPostCategory();

        $root = array(
            'title' => 'title.home',
            'url' => $this->container->get('router')->generate('admin_menu_index')
        );
        $parent = array(
            'title' => 'title.category.management',
            'url' => $this->container->get('router')
                ->generate('admin_post_category_index', array('type'=>$category->getType()))
        );
        $children = array(
            'title' => 'title.category.list',
            'url' => ''
        );

        return $this->render('admin/post/show.html.twig', array(
            'post'        => $post,
            'category'    => $category,
            'delete_form' => $deleteForm->createView(),
            'user' => $this->getUser(),
            'breadCrumb' => $this->getBreadCrumb($root, $parent, $children),
        ));
    }

    /**
     * Displays a form to edit an existing Post entity.
     *
     * @Route("/edit/{id}", requirements={"id" = "\d+"}, name="admin_post_edit")
     * @Method({"GET", "POST"})
     * @param Post $post
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editPostAction(Post $post, Request $request)
    {
        $post->setAuthorEmail($this->getUser()->getEmail());
        $post->setPublishedAt(new \DateTime());
        $post->setSlug(Slugger::slugify($post->getTitle()));
        
        $category = $post->getPostCategory();

        $editForm = $this->createForm(new PostType(), $post);
        $deleteForm = $this->createPostDeleteForm($post);

        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $post->setPostCategory($category);

            $folder = '/posts';

            // Create Image
            if($post->getFiles()) {

                $temp_path = __DIR__ .ConstantBundle::UPLOAD_DIR. $folder;

                $isUploaded = $post->upload($temp_path);
            }

            if($isUploaded) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($post);
                $em->flush();
            }

            return $this->redirectToRoute('admin_post_list', array('id' =>$category->getId()));
        }

        $root = array(
            'title' => 'title.home',
            'url' => $this->container->get('router')->generate('admin_menu_index')
        );
        $parent = array(
            'title' => 'title.category.management',
            'url' => $this->container->get('router')
                ->generate('admin_post_category_index', array('type'=>$category->getType()))
        );
        $children = array(
            'title' => 'title.category.list',
            'url' => ''
        );

        return $this->render('admin/post/edit.html.twig', array(
            'post'        => $post,
            'category'    => $category,
            'type' => CF::getType($category->getType()),
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'user' => $this->getUser(),
            'breadCrumb' => $this->getBreadCrumb($root, $parent, $children),
        ));
    }

    /**
     * Deletes a Post entity.
     *
     * @Route("/delete/{id}", name="admin_post_delete")
     * @@@@Method("DELETE")
     * @@@@Security("post.isAuthor(user)")
     *
     * The Security annotation value is an expression (if it evaluates to false,
     * the authorization mechanism will prevent the user accessing this resource).
     * The isAuthor() method is defined in the AppBundle\Entity\Post entity.
     */
    public function deletePostAction(Request $request, Post $post)
    {
        $form = $this->createPostDeleteForm($post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $em->remove($post);
            $em->flush();
        }

        return $this->redirectToRoute('admin_post_list', array('id' => $post->getPostCategory()->getId()));
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
    private function createPostDeleteForm(Post $post)
    {

        return $this->createFormBuilder()
            ->setAction($this->generateUrl('admin_post_delete',
                array(
                    'id' => $post->getId())
                )
            )
            ->setMethod('DELETE')
            ->getForm()
            ;
    }

    /**
     * Displays a form to edit an existing Post entity.
     *
     * @Route("/category/edit/{id}", requirements={"id" = "\d+"}, name="admin_post_category_edit")
     * @Method({"GET", "POST"})
     *
     * @param PostCategory $category
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editPostCategoryAction(PostCategory $category, Request $request)
    {
//        if (null === $this->getUser() || !$menu->isAuthor($this->getUser())) {
//            throw $this->createAccessDeniedException('Posts can only be edited by their authors.');
//        }

        $em = $this->getDoctrine()->getManager();

        $editForm = $this->createForm(new PostCategoryType(), $category);
        $deleteForm = $this->createCategoryDeleteForm($category);

        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            //$menu->setSlug($this->get('slugger')->slugify($menu->getTitle()));
            $em->flush();

            return $this->redirectToRoute('admin_post_category_edit', array('id' => $category->getId()));
        }

        $root = array(
            'title' => 'title.home',
            'url' => $this->container->get('router')->generate('admin_menu_index')
        );
        $parent = array(
            'title' => 'title.category.management',
            'url' => $this->container->get('router')
                ->generate('admin_post_category_index', array('type'=>$category->getType()))
        );
        $children = array(
            'title' => 'title.category.list',
            'url' => ''
        );

        return $this->render('admin/post/category/edit.html.twig', array(
            'category'        => $category,
            'type'        => CF::getType($category->getType()),
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'user' => $this->getUser(),
            'breadCrumb' => $this->getBreadCrumb($root, $parent, $children),
        ));
    }

    /**
     * Deletes a Post entity.
     *
     * @Route("/{id}", name="admin_post_category_delete")
     * @Method("DELETE")
     * ///@Security("post.isAuthor(user)")
     *
     * The Security annotation value is an expression (if it evaluates to false,
     * the authorization mechanism will prevent the user accessing this resource).
     * The isAuthor() method is defined in the AppBundle\Entity\Post entity.
     */
    public function deleteCategoryAction(Request $request, PostCategory $category)
    {
        $form = $this->createCategoryDeleteForm($category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $em->remove($category);
            $em->flush();
        }

        return $this->redirectToRoute('admin_post_category_index', array('type'=>$category->getType()) );
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
     * @param PostCategory $category The post object
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCategoryDeleteForm(PostCategory $category)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('admin_post_category_delete', array(
                'id' => $category->getId()))
            )
            ->setMethod('DELETE')
            ->getForm()
            ;
    }



}
