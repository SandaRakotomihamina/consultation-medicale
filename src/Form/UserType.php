<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('matricule', TextType::class, [
                'required' => true,
                'label' => 'Matricule',
            ])
            ->add('title', TextType::class, [
                'required' => true,
                'label' => 'Titre',
            ])
            ->add('name', TextType::class, [
                'required' => true,
                'label' => 'Nom',
            ])
            ->add('username', TextType::class, [
                'required' => true,
                'label' => 'Nom d\'utilisateur',
            ])
            ->add('plainPassword', PasswordType::class, [
                'mapped' => false,
                'label' => 'Mot de passe',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez renseigner un mot de passe']),
                    new Length(['min' => 6, 'minMessage' => 'Le mot de passe doit contenir au moins {{ limit }} caractères']),
                ],
            ])

            ->add('roles', ChoiceType::class, [
                'label' => 'Rôle de l’utilisateur',
                'choices' => [
                    'Utilisateur simple' => 'ROLE_USER',
                    'Administrateur' => 'ROLE_ADMIN',
                    'Super Administrateur' => 'ROLE_SUPER_ADMIN'
                ],
                'multiple' => true,
                'expanded' => false,
                'attr' => [
                    'class' => 'form-select-custom'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
