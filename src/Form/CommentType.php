<?php

namespace App\Form;

use App\Entity\Comment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class CommentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('content', TextareaType::class, [
            'label' => 'Ecrire un commentaire',
            'constraints' => [
                new NotBlank([
                    'message' => 'Merci de renseigner un titre'
                ]),
                new Length ([
                    'min' => 1,
                    'minMessage' => 'Le titre doit contenir au moins {{ limit }} caractère(s)',
                    'max' => 150,
                    'maxMessage' => 'Le titre doit contenir au maximum {{ limit }} caractères',
                ]),
            ]
        ])
        ->add('save', SubmitType::class, [
            'label' => 'Publier',
            'attr' => [
                'class' => 'btn btn-outline-primary col-12',
            ],
        ])
    ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Comment::class,
        ]);
    }
}
