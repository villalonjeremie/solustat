<?php
namespace Solustat\TimeSheetBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use FOS\UserBundle\Form\Type\ProfileFormType as BaseProfileFormType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class ProfileFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->remove('username');
        $builder->add('name', null,
                    array(
                        'required'   => true,
                        'empty_data' => 'John'
                    ))
                ->add('surname', null,
                    array(
                        'required'   => true,
                        'empty_data' => 'Doe'
                    ))
                ->add('registration_nb', null,
                    array(
                    'required'   => true,
                    'empty_data' => '12345'
                    ))
                ->add('tel_work',null,
                    array(
                        'required'   => true,
                        'empty_data' => '514-621-7983'
                    ))
                ->add('tel_mobile',null,
                    array(
                        'required'   => true,
                        'empty_data' => '514-621-7983'
                    ))
                ->add('sector', EntityType::class, array(
                    'class'        => 'SolustatTimeSheetBundle:Sector',
                    'choice_label' => 'name',
                    'multiple'     => false))
                ->add('position', EntityType::class, array(
                    'class'        => 'SolustatTimeSheetBundle:Position',
                    'choice_label' => 'position',
                    'multiple'     => false));
    }

    public function getParent()
    {
        return BaseProfileFormType::class;
    }
}