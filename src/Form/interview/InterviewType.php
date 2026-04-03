<?php

namespace App\Form\interview;

use App\Entity\Interview;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class InterviewType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $companyReadonly = (bool) $options['lock_company_name'];
        $companyHint = $options['company_name_hint'];

        $builder
            ->add('candidateName', TextType::class, [
                'label' => 'Nom du candidat',
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le nom du candidat est obligatoire']),
                    new Assert\Length([
                        'min' => 2,
                        'max' => 255,
                        'minMessage' => 'Le nom doit contenir au moins 2 caractères',
                        'maxMessage' => 'Le nom ne peut dépasser 255 caractères',
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^(?=.*\p{L})[\p{L}\s\'\-]+$/u',
                        'message' => 'Le nom du candidat doit contenir uniquement des lettres.',
                    ]),
                ],
            ])
            ->add('companyName', TextType::class, [
                'label' => 'Nom de l\'entreprise',
                'attr' => [
                    'class' => 'form-control',
                    'readonly' => $companyReadonly,
                ],
                'help' => $companyHint ? sprintf('Auto-rempli depuis votre profil recruteur: %s', $companyHint) : null,
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le nom de l\'entreprise est obligatoire']),
                    new Assert\Length([
                        'min' => 2,
                        'max' => 255,
                        'minMessage' => 'Le nom doit contenir au moins 2 caractères',
                        'maxMessage' => 'Le nom ne peut dépasser 255 caractères',
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^(?=.*\p{L})[\p{L}\s\'\-]+$/u',
                        'message' => 'Le nom de l\'entreprise doit contenir uniquement des lettres.',
                    ]),
                ],
            ])
            ->add('interviewDate', DateType::class, [
                'label' => 'Date de l\'entretien',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'La date est obligatoire']),
                ],
            ])
            ->add('heureDebut', TimeType::class, [
                'label' => 'Heure de début (HH:MM)',
                'widget' => 'single_text',
                'input' => 'string',
                'input_format' => 'H:i',
                'with_seconds' => false,
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'L\'heure de début est obligatoire']),
                ],
            ])
            ->add('heureFin', TimeType::class, [
                'label' => 'Heure de fin (HH:MM)',
                'widget' => 'single_text',
                'input' => 'string',
                'input_format' => 'H:i',
                'with_seconds' => false,
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'L\'heure de fin est obligatoire']),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Interview::class,
            'lock_company_name' => false,
            'company_name_hint' => null,
        ]);

        $resolver->setAllowedTypes('lock_company_name', 'bool');
        $resolver->setAllowedTypes('company_name_hint', ['null', 'string']);
    }
}
