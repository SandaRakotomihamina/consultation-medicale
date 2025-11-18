<?php

namespace App\Form;

use App\Entity\ConsultationList;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConsultationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('Date')
            ->add('Grade')
            ->add('Nom')
            ->add('Matricule')
            ->add('Motif')
            ->add('DelivreurDeMotif')
            ->add('Observation')
            ->add('DelivreurDObservation', null, [
                'attr' => [
                    'readonly' => true
                ]
            ])
            ->add('Repos', ChoiceType::class, [
                'label' => 'Repos administré',
                'choices' => [
                    '24h' => '24h',
                    '48h' => '48h',
                    '72h' => '72h'
                ],
                'placeholder' => 'Aucun',
                'required' => false,
            ])
        ;

        if($builder->getData()->getRepos()){
            

        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ConsultationList::class,
        ]);
    }
}
