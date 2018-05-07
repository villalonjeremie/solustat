<?php

namespace Solustat\TimeSheetBundle\Form;

use Solustat\TimeSheetBundle\Form\SectorType;
use Solustat\TimeSheetBundle\Form\FrequencyType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class PatientType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('folderNumber',   NumberType::class)
            ->add('startingDate',   DateType::class)
            ->add('name',   TextType::class)
            ->add('surname',    TextType::class)
            ->add('address',    TextType::class)
            ->add('zip',    TextType::class)
            ->add('town',   TextType::class)
            ->add('tel',    TextType::class)
            ->add('sector', EntityType::class, array(
                'class'        => 'SolustatTimeSheetBundle:Sector',
                'choice_label' => 'name',
                'multiple'     => false))
            ->add('TypeCare', EntityType::class, array(
                'class'        => 'SolustatTimeSheetBundle:TypeCare',
                'choice_label' => 'name',
                'multiple'     => false))
            ->add('VisitTime', EntityType::class, array(
                'class'        => 'SolustatTimeSheetBundle:VisitTime',
                'choice_label' => 'name',
                'multiple'     => false))
            ->add('Frequency', EntityType::class, array(
                'class'        => 'SolustatTimeSheetBundle:Frequency',
                'choice_label' => 'name',
                'multiple'     => false))
            ->add('Nurse', EntityType::class, array(
                'class'        => 'SolustatTimeSheetBundle:User',
                'choice_label' => 'name',
                'multiple'     => false))
            ->add('save',      SubmitType::class);
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Solustat\TimeSheetBundle\Entity\Patient'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'solustat_timesheetbundle_patient';
    }
}
