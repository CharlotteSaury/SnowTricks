<?php

namespace App\Form;

use App\Entity\Group;
use App\Entity\Trick;
use Symfony\Bridge\Doctrine\Form\Type\EntityType as TypeEntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TrickType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $trick = new Trick;
        $builder
            ->add('name', null, [
                'required' => true,
                'label' => false
            ])
            ->add('description', null, [
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
            ->add('author', )
            //->add('mainImage')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Trick::class,
        ]);
    }
}
