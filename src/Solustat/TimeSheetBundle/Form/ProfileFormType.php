<?php
namespace Solustat\TimeSheetBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use FOS\UserBundle\Form\Type\ProfileFormType as BaseProfileFormType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class ProfileFormType extends AbstractType
{
    public function buildUserForm(FormBuilderInterface $builder, array $options)
    {
        $builder->remove('username');
        $builder
            ->add('name')
            ->add('surname')
            ->add('registration_nb')
            ->add('tel_work')
            ->add('tel_mobile')
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

    public function getBlockPrefix()
    {
        return 'fos_user_profile_edit';
    }
}