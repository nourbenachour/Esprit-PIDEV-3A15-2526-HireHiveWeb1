<?php

namespace App\Form\Post;

use App\Entity\Post;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Regex;

class PostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre',
                'required' => true,
                'attr' => [
                    'placeholder' => 'Titre du post',
                    'maxlength' => 150,
                    'pattern' => "[A-Za-zÀ-ÿ\\s'’-]+",
                    'title' => 'Le titre doit contenir uniquement des lettres.',
                ],
                'constraints' => [
                    new Regex([
                        'pattern' => "/^[\p{L}\s'’-]+$/u",
                        'message' => 'Le titre doit contenir uniquement des lettres.',
                    ]),
                ],
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Contenu',
                'required' => true,
                'attr' => ['placeholder' => 'Ecrivez votre post ici...', 'rows' => 5],
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Type',
                'choices' => [
                    'Texte' => 'TEXT',
                    'Image' => 'IMAGE',
                    'Video' => 'VIDEO',
                    'Article' => 'ARTICLE',
                ],
                'required' => true,
            ])
            ->add('visibility', ChoiceType::class, [
                'label' => 'Visibilite',
                'choices' => [
                    'Public' => 'PUBLIC',
                    'Candidat' => 'CANDIDAT',
                    'Recruteur' => 'RECRUITER',
                ],
                'required' => true,
            ])
            ->add('imageFile', FileType::class, [
                'label' => 'Image du post',
                'required' => false,
                'mapped' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => ['image/jpeg', 'image/png', 'image/webp', 'image/gif'],
                        'mimeTypesMessage' => 'Veuillez choisir une image valide (jpg, png, webp ou gif).',
                    ]),
                ],
                'attr' => ['accept' => 'image/*'],
            ]);

        if ($options['is_admin'] ?? false) {
            $builder->add('is_published', CheckboxType::class, [
                'label' => 'Publier',
                'required' => false,
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Post::class,
            'is_admin' => false,
        ]);
    }
}
