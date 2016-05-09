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

use AppBundle\Form\CategoryType;
use AppBundle\Form\ProductType;
use Symfony\Component\HttpFoundation\JsonResponse;
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
use AppBundle\Entity\Page;
use AppBundle\Entity\Menu;
use AppBundle\Utils\Paginator;

/**
 * Controller used to manage blog contents in the backend.
 *
 * Please note that the application backend is developed manually for learning
 * purposes. However, in your real Symfony application you should use any of the
 * existing bundles that let you generate ready-to-use backends without effort.
 * See http://knpbundles.com/keyword/admin
 *
 * @Route("/")
 * @author Ryan Weaver <weaverryan@gmail.com>
 * @author Javier Eguiluz <javier.eguiluz@gmail.com>
 */
class HomeController extends BaseController
{
    const FIELD = 7;


    public function getMenus()
    {

        $em = $this->getDoctrine()->getManager();

        $categories = $em->getRepository('AppBundle:Category')->getNodesHierarchy();

        $menus = array();

        foreach($categories as $category){
            if($category['lvl'] === 0){
                $menus[$category['id']] = $category;
            }else if($category['lvl'] === 1){
                $menus[$category['root']]['children'][] = $category;
            }
        }

        return $menus;

    }
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
     * @Route("/", name="homepage")
     * $Method("GET)
     */

    public function indexAction()
    {

        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('AppBundle:Post');
        $repoCategory = $em->getRepository('AppBundle:PostCategory'); 

        $present_fields = array_slice($repo->findHotPostHome(1),0,3);
        $temp_projects = $repo->findHotPostHome(2);

        $present_projects = array();
        $present_projects[0] = array_slice( $temp_projects,0,10 );

        $repoCustomer = $em->getRepository('AppBundle:Customer');
        $customers = $repoCustomer->findAll();

        $repoBanner = $em->getRepository('AppBundle:Banner');
        $banners = $repoBanner->findAll();

        $project_categories = $repoCategory->findByType(2);
        
        foreach ( $project_categories as $item )
        {
            $count = 0;
            foreach ( $temp_projects as $project )
            {

                $category_id = $item->getId();
                $project_category_id = $project['category_id'];
                if($category_id  == $project_category_id)
                {
                    $count++;
                    if($count > 10) break;

                    $present_projects[$category_id][] = $project;
                }
                else
                {
                    continue;
                }
            }

        }

        $menus = $repoCategory->findByType(1);

        return $this->render('home/homepage.html.twig',
            array(
                'menus' => $menus,
                'project_categories' => $project_categories,
                'present_fields' => $present_fields,
                'present_projects' => $present_projects,
                'page' => 1,
                'customers' => $customers,
                'banners' => $banners,
                'header_footer'=> $this->getHeaderFooter(),
            ));
    }

    /**
     * Creates a new Post entity.
     *
     * @Route("/new", name="admin_product_new")
     * @Method({"GET", "POST"})
     * NOTE: the Method annotation is optional, but it's a recommended practice
     * to constraint the HTTP methods each controller responds to (by default
     * it responds to all methods).
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

            // Create Image
            $tmpImage = $product->file;
            $temp_path = __DIR__.'/../../../../web/uploads/products';
            $product->file->move($temp_path, $product->file->getClientOriginalName());
            $image = new Image();
            $image->setName($tmpImage->getClientOriginalName());
            $image->setType($tmpImage->guessClientExtension());
            $image->setSize($tmpImage->getClientsize());
            $product->addImage($image);

            $em = $this->getDoctrine()->getManager();
            $em->persist($product);
            $em->persist($image);
            $em->flush();

            return $this->redirectToRoute('admin_product_index');
        }

        return $this->render('admin/product/new.html.twig', array(
            'product' => $product,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a Product entity.
     * @Route("/du-ana/chi-tiet/{slug}", name="product_show")
     */
    public function showAction(Product $product)
    {

        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('AppBundle:Product');
        $product = $repo->findProductById($product);
        $related_products = $repo->findRelatedProduct($product);
        $hot_products = $repo->findHotProduct();

        return $this->render('product/show.html.twig', array(
            'product'        => $product,
            'related_products' => $related_products,
            'hot_products' => $hot_products,
            'menus' => $this->getMenus(),
            'page' => 1,
//            'header_footer'=> $this->getHeaderFooter(),
        ));
    }

    /**
     *
     * @Route("/du-ana/{slug}", name="product_list")
     * @Route("/du-an", name="product_list")
     * @Method("GET")
     */
    public function listAction(Category $category, $pageNum = 1)
    {
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('AppBundle:Product');

        $countProducts = $repo->getCountProductsByCate($category);

        $paginator = new Paginator($countProducts, $pageNum, $limit = 12);

        $pagenums = $paginator->getNumPages();

        $products = $repo->findProductsWithPagingByCate(
            $category,
            $paginator->getOffset(),
            $paginator->getLimit()
        );

        $hot_products = $repo->findHotProduct();

        return $this->render('product/list.html.twig', array(
            'products' => $products,
            'page' => $pageNum,
            'pagenums' => $pagenums,
            'hot_products' => $hot_products,
            'menus' => $this->getMenus(),
            'slug' => $category->getSlug(),
            'header_footer'=> $this->getHeaderFooter(),
        ));
    }

    /**
     * Displays a form to edit an existing Post entity.
     *
     * @Route("/{id}/edit", requirements={"id" = "\d+"}, name="admin_product_edit")
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
            $em->flush();

            return $this->redirectToRoute('admin_product_edit', array('id' => $product->getId()));
        }

        return $this->render('admin/product/edit.html.twig', array(
            'product'        => $product,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
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
     * @Route("/ajax/index", name="homepage_ajax_index")
     * $Method("GET)
     */

    public function indexAjaxAction(Request $request)
    {
        if($request->isXmlHttpRequest())
        {
            $em = $this->getDoctrine()->getManager();
            $repo = $em->getRepository('AppBundle:Post');
            $present_projects = array_slice($repo->findHotPostHome(2),0,5);

            $response = $this->render('home/ajax/project_slide.html.twig',
                array(
                    'present_projects' => $present_projects,
                ));

            return new JsonResponse($response);
        }else {
            $em = $this->getDoctrine()->getManager();
            $repo = $em->getRepository('AppBundle:Post');
            $repoCategory = $em->getRepository('AppBundle:PostCategory');

            $project_categories = $repoCategory->findByType(2);

            $present_fields = array_slice($repo->findHotPostHome(1), 0, 3);
            $present_projects = array_slice($repo->findHotPostHome(2), 0, 10);

            $menus = $repoCategory->findByType(1);

            return $this->render('home/homepage.html.twig',
                array(
                    'menus' => $menus,
                    'project_categories' => $project_categories,
                    'present_fields' => $present_fields,
                    'present_projects' => $present_projects,
                    'page' => 1,
                ));
        }
    }
}
