<?php

namespace App\Form\JobOffer;

use App\Entity\Job_offer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class JobOfferType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre',
                'required' => true,
                'attr' => ['placeholder' => "Titre de l'offre d'emploi"],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le titre est obligatoire.']),
                    new Assert\Length([
                        'min' => 3,
                        'minMessage' => 'Le titre doit contenir au moins {{ limit }} caractères.',
                    ]),
                    new Assert\Regex([
                        'pattern' => '/\p{L}/u',
                        'message' => 'Le titre doit contenir au moins une lettre.',
                    ]),
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => true,
                'attr' => ['placeholder' => "Description détaillée du poste", 'rows' => 5],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'La description est obligatoire.']),
                    new Assert\Length([
                        'min' => 20,
                        'minMessage' => 'La description doit contenir au moins {{ limit }} caractères.',
                    ]),
                ],
            ])
            ->add('location', TextType::class, [
                'label' => 'Localisation',
                'required' => true,
                'attr' => ['placeholder' => 'Ex: Tunis, Sfax, Remote...'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'La localisation est obligatoire.']),
                    new Assert\Regex([
                        'pattern' => '/\p{L}/u',
                        'message' => 'La localisation doit contenir au moins une lettre.',
                    ]),
                ],
            ])
            ->add('contract_type', ChoiceType::class, [
                'label' => 'Type de contrat',
                'choices' => [
                    'CDI' => 'CDI',
                    'CDD' => 'CDD',
                    'Stage' => 'STAGE',
                    'Freelance' => 'FREELANCE',
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le type de contrat est obligatoire.']),
                ],
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'Statut',
                'choices' => [
                    'Ouverte' => 'OPEN',
                    'Fermée' => 'CLOSED',
                    'Brouillon' => 'DRAFT',
                ],
            ])
            ->add('skills', TextareaType::class, [
                'label' => 'Compétences requises',
                'required' => true,
                'attr' => ['placeholder' => 'Ex: PHP, Symfony, MySQL...', 'rows' => 3],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Les compétences requises sont obligatoires.']),
                ],
            ])
            ->add('soft_skills', TextareaType::class, [
                'label' => 'Soft Skills',
                'required' => false,
                'attr' => ['placeholder' => 'Ex: Travail en équipe, Communication...', 'rows' => 3],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Job_offer::class,
            'is_edit' => false,
        ]);
    }
}
