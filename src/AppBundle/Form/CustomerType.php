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
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Defines the form used to create and manipulate blog posts.
 *
 * @author Ryan Weaver <weaverryan@gmail.com>
 * @author Javier Eguiluz <javier.eguiluz@gmail.com>
 */
class CustomerType extends AbstractType
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

        $builder
            ->add('name', null, array('label' => 'label.customer.name','required' => true))
            ->add('address', 'text', array(
                'label' => 'label.customer.address',
                'required' => false,
            ))
            ->add('phone', 'text', array(
                'label' => 'label.customer.phone',
                'required' => false,
            ))
            ->add('fax', 'text', array(
                'label' => 'label.customer.fax',
                'required' => false,
            ))
            ->add('email', 'text', array(
                'label' => 'label.customer.email',
                'required' => false,
            ))
            ->add('website', 'text', array(
                'label' => 'label.customer.website',
                'required' => false,
            ))
            ->add('uploadedFile','file',array(
                'label'=>'label.customer.uploadLogo',
                'required' => true,
                'multiple' => false,
                'data_class' => null,
            ))
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Customer',
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        // Best Practice: use 'app_' as the prefix of your custom form types names
        // see http://symfony.com/doc/current/best_practices/forms.html#custom-form-field-types
        return 'app_customer';
    }
}
