<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use AppBundle\Entity\Category;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceList;
use Doctrine\ORM\EntityRepository;
/**
 * Defines the form used to create and manipulate blog posts.
 *
 * @author Ryan Weaver <weaverryan@gmail.com>
 * @author Javier Eguiluz <javier.eguiluz@gmail.com>
 */
class ProductType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // For the full reference of options defined by each form field type
        // see http://symfony.com/doc/current/reference/forms/types.html

        // By default, form fields include the 'required' attribute, which enables
        // the client-side form validation. This means that you can't test the
        // server-side validation errors from the browser. To temporarily disable
        // this validation, set the 'required' attribute to 'false':
        //
        //     $builder->add('title', null, array('required' => false, ...));
       /* $dql = "SELECT c.id, c.name from AppBundle:Category ORDER BY c.name ASC";
        //$results = $this->getDoctrine->createQuery($dql)->getArrayResult();*/
        //$categories = $options['em']->getRepository('AppBundle:Category')->findAll();

        $builder
            ->add('name', null, array('label' => 'label.product.name'))
            ->add('code', null, array('label' => 'label.product.code'))
            ->add('category', 'entity', array(
                'class' =>  'AppBundle:Category',
                'label' => 'label.category.name',
            ))
            ->add('file','file',array('label'=>'label.product.upload_image', 'required' => false))
            ->add('summary', 'textarea', array('label' => 'label.product.summary'))
            ->add('content', 'textarea', array(
                'attr' => array('rows' => 20),
                'label' => 'label.product.content',
            ))
            ->add('price', null, array('label' => 'label.product.price'))
            ->add('createdAt', 'datetime', array(
                'widget' => 'single_text',
                'label' => 'label.product.created_at',
            ));

    }
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Product',
            'categories' => '',
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        // Best Practice: use 'app_' as the prefix of your custom form types names
        // see http://symfony.com/doc/current/best_practices/forms.html#custom-form-field-types
        return 'app_product';
    }

}
