<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegisterType extends AbstractType
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
                'label' => 'Type de compte',
                'choices' => [
                    'Lecteur' => 'ROLE_USER',
                    'Auteur'  => 'ROLE_AUTEUR',
                ],
                'required' => true,
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false,
                'label' => 'Mot de passe',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez saisir un mot de passe.']),
                    new Length(['min' => 6, 'minMessage' => 'Le mot de passe doit contenir au moins {{ limit }} caractères.']),
                ],
                'first_options' => ['label' => 'Mot de passe'],
                'second_options' => ['label' => 'Confirmer le mot de passe'],
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