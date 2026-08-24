<?php

namespace App\Form;

use App\Entity\Book;
use App\Entity\Category;
use App\Repository\CategoryRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BookType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', null, [
                'label' => 'Titre',
            ])
            ->add('isbn', null, [
                'label' => 'ISBN (13 chiffres)',
            ])
            ->add('summary', TextareaType::class, [
                'label' => 'Résumé',
                'required' => false,
            ])
            ->add('publicationDate', DateType::class, [
                'label' => 'Date de publication',
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Type de livre',
                'choices' => [
                    'Texte (.txt / .md)' => 'TEXTE',
                    'Audio (.zip)'       => 'AUDIO',
                ],
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'label',
                'label' => 'Catégorie',
            ])
            ->add('file', FileType::class, [
                'label' => 'Fichier du livre',
                'required' => $options['required'],
                'constraints' => [
                    // Symfony\Component\Validator\Constraints\File([
                    //     'maxSize' => '50M',
                    //     'mimeTypes' => ['text/plain', 'text/markdown', 'application/zip'],
                    //     'mimeTypesMessage' => 'Formats acceptés : .txt, .md, .zip (max 50 Mo)',
                    // ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Book::class,
        ]);
        $resolver->setRequired(['required']);
    }
}