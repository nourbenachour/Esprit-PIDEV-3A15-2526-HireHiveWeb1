<?php

namespace App\Form\Reclamation;

use App\Entity\Claim_response;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * ClaimResponseType – form for an admin to post a response to a Claim.
 *
 * Like ClaimType, HTML5 validation is disabled via 'novalidate' to demonstrate
 * that the Assert\NotBlank on Claim_response::$message is the sole validation gate.
 */
class ClaimResponseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // The response message – Assert\NotBlank is on the entity
            ->add('message', TextareaType::class, [
                'label' => 'Your Response',
                'attr'  => [
                    'rows'        => 4,
                    'placeholder' => 'Type your response here…',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Claim_response::class,
            // HTML5 validation disabled – proves validation is server-side only
            'attr'       => ['novalidate' => 'novalidate'],
        ]);
    }
}
