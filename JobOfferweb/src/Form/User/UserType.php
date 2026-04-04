<?php

namespace App\Form\User;

use App\Entity\Users;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $isEdit = $options['is_edit'] ?? false;

        $builder
            ->add('firstName', TextType::class, [
                'label' => 'Prénom',
                'required' => true,
                'attr' => ['placeholder' => 'Prénom'],
                'constraints' => [new Assert\NotBlank()],
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Nom',
                'required' => true,
                'attr' => ['placeholder' => 'Nom'],
                'constraints' => [new Assert\NotBlank()],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'required' => true,
                'attr' => ['placeholder' => 'email@domaine.com'],
                'constraints' => [new Assert\NotBlank(), new Assert\Email()],
            ])
            ->add('phone', TelType::class, [
                'label' => 'Téléphone',
                'required' => false,
                'attr' => ['placeholder' => '+216 ...'],
            ])
            ->add('role', ChoiceType::class, [
                'label' => 'Rôle',
                'choices' => [
                    'Admin' => 'ROLE_ADMIN',
                    'Recruiter' => 'ROLE_RECRUITER',
                    'Candidate' => 'ROLE_CANDIDATE',
                ],
                'constraints' => [new Assert\NotBlank(), new Assert\Choice(['ROLE_ADMIN','ROLE_RECRUITER','ROLE_CANDIDATE'])],
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'Statut',
                'choices' => [
                    'Vérifié' => 'USER_VERIFIED',
                    'En attente' => 'USER_SUSPEND',
                    'Banni' => 'USER_BANNED',
                ],
            ])
            ->add('isActive', CheckboxType::class, [
                'label' => 'Actif',
                'required' => false,
            ])
        ;

        $builder->add('plainPassword', PasswordType::class, [
            'label' => false,
            'required' => false,
            'mapped' => false,
            'row_attr' => ['style' => 'display:none'],
            'attr' => ['autocomplete' => 'new-password'],
            'constraints' => [],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Users::class,
            'is_edit' => false,
        ]);
    }
}
