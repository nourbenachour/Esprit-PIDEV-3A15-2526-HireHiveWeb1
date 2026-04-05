<?php

namespace App\Form\JobOffer;

use App\Entity\Application;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\File;

class ApplicationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('lettre', TextareaType::class, [
                'label' => 'Lettre de motivation',
                'required' => true,
                'attr' => [
                    'placeholder' => 'Rédigez votre lettre de motivation ici...',
                    'rows' => 6,
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'La lettre de motivation est obligatoire.']),
                    new Assert\Length([
                        'min' => 20,
                        'minMessage' => 'La lettre de motivation doit contenir au moins {{ limit }} caractères.',
                    ]),
                    new Assert\Regex([
                        'pattern' => '/\p{L}/u',
                        'message' => 'La lettre de motivation doit contenir au moins une lettre.',
                    ]),
                ],
            ])
            ->add('cv_file', FileType::class, [
                'label' => 'CV (fichier PDF)',
                'mapped' => false,
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le CV est obligatoire (fichier PDF).']),
                    new File([
                        'mimeTypes' => ['application/pdf'],
                        'mimeTypesMessage' => 'Le CV doit être au format PDF.',
                        'maxSize' => '5M',
                        'maxSizeMessage' => 'Le fichier ne doit pas dépasser 5 Mo.',
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Application::class,
        ]);
    }
}
