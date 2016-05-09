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

use AppBundle\Entity\Category;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\False;

/**
 * Defines the form used to create and manipulate blog comments. Although in this
 * case the form is trivial and we could build it inside the controller, a good
 * practice is to always define your forms as classes.
 * See http://symfony.com/doc/current/book/forms.html#creating-form-classes
 *
 * @author Ryan Weaver <weaverryan@gmail.com>
 * @author Javier Eguiluz <javier.eguiluz@gmail.com>
 */
class CategoryType extends AbstractType
{
    protected $em;

    protected $category;

    public function __construct($em, $category)
    {
        $this->em = $em;
        $this->category = $category;
    }
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // By default, form fields include the 'required' attribute, which enables
        // the client-side form validation. This means that you can't test the
        // server-side validation errors from the browser. To temporarily disable
        // this validation, set the 'required' attribute to 'false':
        //
        //     $builder->add('content', null, array('required' => false));
        $builder
            ->add('name', 'text', array('label' => false) )
            ->add('slug', 'text', array( 'label' => false ))
            ->add('parent', 'entity', array(
                'class' =>  'AppBundle:Category',
                'property' => 'name',
                'label' => false,
                //  'multiple' => true,
//                'query_builder' => function(EntityRepository $er) {
//                    return $er->createQueryBuilder('u')
//                        //->where('u.id != :selected')
//                        ->orderBy('u.name', 'ASC');
//                },
                'required' => FALSE,
                //'mapped' => FALSE,
                'empty_value' => 'Chọn danh mục',
                'empty_data' => null,
//                'choice_value' => function ($choiceKey) {
//                    if (null === $choiceKey) {
//                        return null;
//                    }
//                    // cast to string after testing for null,
//                    // as both null and false cast to an empty string
//                    $stringChoiceKey = (string) $choiceKey;
//
//                    // true casts to '1'
//                    if ('1' === $stringChoiceKey) {
//                        return 'true';
//                    }
//
//                    // false casts to an empty string
//                    if ('' === $stringChoiceKey) {
//                        return 'false';
//                    }
//
//                    throw new \Exception('Unexpected choice key: ' . $choiceKey);
//                }
                'choices_as_values' => true,
            ));

    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Category',
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
        return 'category';
    }

}
