<?php

namespace App\Form;

use App\Entity\DemandeDeConsultation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DemandeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('Grade')
            ->add('Nom')
            ->add('Matricule')
            ->add('LIBUTE', null, [
                'required' => false,
                'label' => 'libelé unité',
            ])
            ->add('Motif')
            ->add('DelivreurDeMotif', null)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DemandeDeConsultation::class,
        ]);
    }
}
