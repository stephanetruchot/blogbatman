<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;


class EditPhotoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('photo', FileType::class, [
                'label' => 'Sélectionnez une nouvelle photo',
                'attr' => [
                    'accept' => 'image/jpeg, image/png'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Vous devez sélectionner un fichier'
                    ]),
                    new File([
                        'maxSize' => '1M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'L\image doit petre de type jpg ou png',
                        'maxSizeMessage' => 'Fichier trop volumineux ( {{ size }} {{ suffix }} ). La taille maximum autorisée est {{ limit }}{{ suffix }}',
                    ])
                    ],
                ])
                ->add('save', SubmitType::class, [
                    'label' => 'Envoyer',
                    'attr' => [
                        'class' => 'btn btn-outline-primary col-12',
                    ],
                ])
        ;
    }

}
