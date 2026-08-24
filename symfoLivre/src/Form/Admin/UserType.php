<?php

namespace App\Form\Admin;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Adresse e-mail',
            ])
            ->add('firstname', null, [
                'label' => 'Prénom',
            ])
            ->add('lastname', null, [
                'label' => 'Nom',
            ])
            ->add('role', ChoiceType::class, [
                'label' => 'Rôle',
                'choices' => [
                    'Lecteur' => 'ROLE_USER',
                    'Auteur' => 'ROLE_AUTEUR',
                    'Administrateur' => 'ROLE_ADMIN',
                ],
                'required' => true,
            ])
            ->add('plainPassword', PasswordType::class, [
                'mapped' => false,
                'label' => $options['is_new']
                    ? 'Mot de passe'
                    : 'Nouveau mot de passe (laisser vide pour ne pas changer)',
                'required' => $options['is_new'],
                'constraints' => $options['is_new'] ? [
                    new NotBlank(['message' => 'Veuillez saisir un mot de passe.']),
                    new Length(['min' => 6, 'minMessage' => 'Le mot de passe doit contenir au moins {{ limit }} caractères.']),
                ] : [
                    new Length(['min' => 6, 'minMessage' => 'Le mot de passe doit contenir au moins {{ limit }} caractères.']),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'is_new' => false,
        ]);
    }
}