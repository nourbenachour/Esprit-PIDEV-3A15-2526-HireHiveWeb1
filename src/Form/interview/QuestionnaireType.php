<?php

namespace App\Form\interview;

use App\Entity\Questionnaire;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class QuestionnaireType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('jobTitle', TextType::class, [
                'label' => 'Intitulé du poste',
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'L\'intitulé du poste est obligatoire']),
                    new Assert\Length([
                        'min' => 2,
                        'max' => 255,
                        'minMessage' => 'L\'intitulé doit contenir au moins 2 caractères',
                        'maxMessage' => 'L\'intitulé ne peut dépasser 255 caractères',
                    ]),
                ],
            ])
            ->add('questionText', TextareaType::class, [
                'label' => 'Texte de la question',
                'attr' => ['class' => 'form-control', 'rows' => 4],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le texte de la question est obligatoire']),
                    new Assert\Length([
                        'min' => 5,
                        'minMessage' => 'La question doit contenir au moins 5 caractères',
                    ]),
                ],
            ])
            ->add('answerOptions', TextareaType::class, [
                'label' => 'Options de réponse (JSON: ["option1", "option2", ...])',
                'attr' => ['class' => 'form-control', 'rows' => 3],
                'required' => false,
                'help' => 'Format JSON obligatoire si rempli',
            ])
            ->add('correctAnswer', TextType::class, [
                'label' => 'Réponse correcte',
                'attr' => ['class' => 'form-control'],
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Questionnaire::class,
        ]);
    }
}
