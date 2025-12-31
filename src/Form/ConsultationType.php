<?php

namespace App\Form;

use App\Entity\ConsultationList;
use App\Repository\ExemptionOptionRepository;
use App\Repository\AdresseOptionRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormError;

class ConsultationType extends AbstractType
{
    private array $exemptionChoices = [];
    private array $adresseChoices = [];

    public function __construct(ExemptionOptionRepository $exemptionRepo, AdresseOptionRepository $adresseRepo)
    {
        $this->exemptionChoices = [];
        foreach ($exemptionRepo->findBy([], ['value' => 'ASC']) as $opt) {
            $this->exemptionChoices[$opt->getValue()] = $opt->getValue();
        }

        $this->adresseChoices = [];
        foreach ($adresseRepo->findBy([], ['value' => 'ASC']) as $opt) {
            $this->adresseChoices[$opt->getValue()] = $opt->getValue();
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('Date')
            ->add('Grade' , null , [
                'attr' => [
                    'readonly' => true
                ]
            ])
            ->add('Nom' , null , [
                'attr' => [
                    'readonly' => true
                ]
            ])
            ->add('Matricule' , null , [
                'attr' => [
                    'readonly' => true
                ]
            ])
            ->add('Motif' , null , [
                'attr' => [
                    'readonly' => true
                ]
            ])
            ->add('DelivreurDeMotif', null, [
                'attr' => [
                    'readonly' => true
                ]
            ])
            ->add('LIBUTE', TextType::class, [
                'required' => false,
                'label' => 'Libelé unité',
                'attr' => ['readonly' => true]
            ])
            ->add('Exemption', ChoiceType::class, [
                'label' => 'Exemption(s)',
                'choices' => $this->exemptionChoices,
                'multiple' => true,
                'required' => false,
            ])
            ->add('debutExemption', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('finExemption', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('Adrresse', ChoiceType::class, [
                'label' => 'Adressé(s)',
                'choices' => $this->adresseChoices,
                'multiple' => true,
                'required' => false,
            ])
            ->add('PATC', IntegerType::class, [
                'label' => 'PATC (jours)',
                'required' => false,
                'attr' => ['min' => 0],
                'constraints' => [new Range(['min' => 0, 'minMessage' => 'Le nombre de jours doit être positif'])]
            ])
            ->add('Observation')
            ->add('DelivreurDObservation', null,[
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

        // Validation: debutExemption ne doit pas être avant aujourd'hui, finExemption >= debutExemption
        $builder->addEventListener(FormEvents::POST_SUBMIT, function(FormEvent $event) {
            $form = $event->getForm();
            $data = $event->getData(); // ConsultationList

            $debut = $data->getDebutExemption();
            $fin = $data->getFinExemption();

            $today = new \DateTime();
            $today->setTime(0,0,0);

            if ($debut instanceof \DateTimeInterface) {
                $debutDate = \DateTime::createFromFormat('Y-m-d', $debut->format('Y-m-d'));
                if ($debutDate < $today) {
                    $form->get('debutExemption')->addError(new FormError('La date de début ne peut pas être antérieure à aujourd\'hui.'));
                }
            }

            if ($fin instanceof \DateTimeInterface && $debut instanceof \DateTimeInterface) {
                $finDate = \DateTime::createFromFormat('Y-m-d', $fin->format('Y-m-d'));
                $debutDate = \DateTime::createFromFormat('Y-m-d', $debut->format('Y-m-d'));
                if ($finDate < $debutDate) {
                    $form->get('finExemption')->addError(new FormError('La date de fin doit être postérieure ou égale à la date de début.'));
                }
            }
        });

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
