<?php

namespace App\Form\interview;

use App\Entity\Interview;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class InterviewType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
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
                ],
            ])
            ->add('companyName', TextType::class, [
                'label' => 'Nom de l\'entreprise',
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le nom de l\'entreprise est obligatoire']),
                    new Assert\Length([
                        'min' => 2,
                        'max' => 255,
                        'minMessage' => 'Le nom doit contenir au moins 2 caractères',
                        'maxMessage' => 'Le nom ne peut dépasser 255 caractères',
                    ]),
                ],
            ])
            ->add('interviewDate', DateTimeType::class, [
                'label' => 'Date et heure de l\'entretien',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'La date est obligatoire']),
                ],
            ])
            ->add('heureDebut', TimeType::class, [
                'label' => 'Heure de début (HH:MM)',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'L\'heure de début est obligatoire']),
                ],
            ])
            ->add('heureFin', TimeType::class, [
                'label' => 'Heure de fin (HH:MM)',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'L\'heure de fin est obligatoire']),
                ],
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'Statut',
                'choices' => [
                    'En attente' => 'PENDING',
                    'Accepté' => 'ACCEPTED',
                    'Refusé' => 'REJECTED',
                    'Annulé' => 'CANCELLED',
                ],
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le statut est obligatoire']),
                    new Assert\Choice([
                        'choices' => ['PENDING', 'ACCEPTED', 'REJECTED', 'CANCELLED'],
                        'message' => 'Statut invalide',
                    ]),
                ],
            ])
            ->add('result', ChoiceType::class, [
                'label' => 'Résultat',
                'choices' => [
                    'Envoyé' => 'SENT',
                    'Réponse entretien' => 'INTERVIEW_ANSWER',
                    'Décision prise' => 'DECISION_TAKEN',
                ],
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le résultat est obligatoire']),
                    new Assert\Choice([
                        'choices' => ['SENT', 'INTERVIEW_ANSWER', 'DECISION_TAKEN'],
                        'message' => 'Résultat invalide',
                    ]),
                ],
            ])
            ->add('attendanceStatus', ChoiceType::class, [
                'label' => 'Statut de présence',
                'choices' => [
                    'Planifié' => 'PLANNED',
                    'No-show' => 'NO_SHOW',
                    'Terminé' => 'COMPLETED',
                    'Annulé' => 'CANCELLED',
                ],
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le statut de présence est obligatoire']),
                    new Assert\Choice([
                        'choices' => ['PLANNED', 'NO_SHOW', 'COMPLETED', 'CANCELLED'],
                        'message' => 'Statut de présence invalide',
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Interview::class,
        ]);
    }
}
