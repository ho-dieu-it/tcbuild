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
 * @Route("/du-anaa")
 * @author Ryan Weaver <weaverryan@gmail.com>
 * @author Javier Eguiluz <javier.eguiluz@gmail.com>
 */
class ProductController extends Controller
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
     *
     * @Route("/page.{pageNum}", name="product_index")
     *
     */

    public function indexAction($pageNum)
    {
        $em = $this->getDoctrine()->getManager();

        $repo = $em->getRepository('AppBundle:Product');
        $repoCategory = $em->getRepository('AppBundle:Category');

        $categories = $repoCategory->findAll();
        $category = new Category();
        $category->setName('Tất cả');

        $countProducts = $repo->getCountProductsByAllCate();

        $paginator = new Paginator($countProducts, $pageNum, $limit = 5);

        $pagenums = $paginator->getNumPages();

        $products = $repo->findProductsWithPagingByAllCate(
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
            'categories' => $categories,
            'category' => $category,
            //'header_footer'=> $this->getHeaderFooter(),
        ));
    }
    /**
     * Finds and displays a Product entity.
     * @Route("/chi-tiet/{slug}", name="product_show")
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
     * @Route("/{slug}.{pageNum}", name="product_list")
     * @ParamConverter("post", class="AppBundle:Category")
     * @Method("GET")
     */
    public function listAction(Category $category, $pageNum = 1)
    {
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('AppBundle:Product');
        $repoCategory = $em->getRepository('AppBundle:Category');
        $categories = $repoCategory->findAll();
        
        $countProducts = $repo->getCountProductsByCate($category);

        $paginator = new Paginator($countProducts, $pageNum, $limit = 5);

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
            'categories' => $categories,
            'category' => $category,
//            'header_footer'=> $this->getHeaderFooter(),
        ));
    }
}
