<?php
namespace Solustat\TimeSheetBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use FOS\UserBundle\Form\Type\RegistrationFormType as BaseRegistrationFormType;


class RegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->remove('username')
            ->add('name')
            ->add('surname')
            ->add('registration_nb')
            ->add('tel_work')
            ->add('tel_mobile')
            ->add('sector')
            ->add('position');
    }

    public function getParent()
    {
        return BaseRegistrationFormType::class;
    }
}