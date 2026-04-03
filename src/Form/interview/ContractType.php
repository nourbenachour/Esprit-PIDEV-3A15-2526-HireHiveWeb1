<?php

namespace App\Form\interview;

use App\Entity\Contrat;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ContractType extends AbstractType
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
            ->add('salary', NumberType::class, [
                'label' => 'Salaire annuel (€)',
                'attr' => ['class' => 'form-control'],
                'invalid_message' => 'Veuillez saisir un nombre valide.',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le salaire est obligatoire']),
                    new Assert\GreaterThan([
                        'value' => 0,
                        'message' => 'Le salaire doit être > 0',
                    ]),
                ],
            ])
            ->add('contractType', ChoiceType::class, [
                'label' => 'Type de contrat',
                'choices' => [
                    'CDI' => 'CDI',
                    'CDD' => 'CDD',
                    'Stage' => 'STAGE',
                    'Alternance' => 'ALTERNANCE',
                    'Freelance' => 'FREELANCE',
                ],
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le type de contrat est obligatoire']),
                    new Assert\Choice([
                        'choices' => ['CDI', 'CDD', 'STAGE', 'ALTERNANCE', 'FREELANCE'],
                        'message' => 'Type de contrat invalide',
                    ]),
                ],
            ])
            ->add('startDate', DateType::class, [
                'label' => 'Date de début',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'La date de début est obligatoire']),
                ],
            ])
            ->add('endDate', DateType::class, [
                'label' => 'Date de fin',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'La date de fin est obligatoire']),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Contrat::class,
            'lock_company_name' => false,
            'company_name_hint' => null,
        ]);

        $resolver->setAllowedTypes('lock_company_name', 'bool');
        $resolver->setAllowedTypes('company_name_hint', ['null', 'string']);
    }
}
