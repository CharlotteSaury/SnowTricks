<?php

namespace App\Form;

use App\Entity\Group;
use App\Entity\Trick;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType as TypeEntityType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class TrickType extends AbstractType
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
                'required' => false
            ])
            ->add('images', CollectionType::class, [
                'entry_type'   		=> ImageType::class,
                'prototype'			=> true,
                'allow_add'			=> true,
                'allow_delete'		=> true,
                'by_reference' 		=> false,
                'required'			=> false,
                'label'			=> false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Trick::class,
        ]);
    }
}
