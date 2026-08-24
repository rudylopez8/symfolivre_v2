<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ProfileType extends AbstractType
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
            ->add('newPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new Length(['min' => 6, 'minMessage' => 'Le mot de passe doit contenir au moins {{ limit }} caractères.']),
                ],
                'first_options' => ['label' => 'Nouveau mot de passe (laisser vide pour ne pas changer)'],
                'second_options' => ['label' => 'Confirmer le nouveau mot de passe'],
            ])
            ->add('currentPassword', PasswordType::class, [
                'mapped' => false,
                'label' => 'Mot de passe actuel (pour confirmer les modifications)',
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez saisir votre mot de passe actuel pour confirmer.']),
                ],
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