<?php

namespace App\Form;

use App\Entity\Group;
use App\Entity\ReportedTrick;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType as TypeEntityType;

class ReportedTrickType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', null, [
                'required' => true,
                'label' => false
            ])
            ->add('description', TextareaType::class, [
                'required' => true,
                'label' => false
            ])
            ->add('groups', TypeEntityType::class, [
                'required' => false,
                'label' => false,
                'class' => Group::class,
                'choice_label' => 'name',
                'multiple' => true
            ])
            ->add('mainImage', FileType::class, [
                'label' => 'Define the main image',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new Image()
                ]
            ])
            ->add('images', CollectionType::class, [
                'entry_type'   		=> ImageType::class,
                'prototype'			=> true,
                'allow_add'			=> true,
                'allow_delete'		=> true,
                'by_reference' 		=> false,
                'required'			=> false,
                'label'			=> false
            ])
            ->add('videos', CollectionType::class, [
                'entry_type'   		=> VideoType::class,
                'prototype'			=> true,
                'allow_add'			=> true,
                'allow_delete'		=> true,
                'by_reference' 		=> false,
                'required'			=> false,
                'label'			=> false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ReportedTrick::class,
        ]);
    }
}
