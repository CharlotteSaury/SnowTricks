<?php

namespace App\Form;

use App\Entity\Video;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Validator\Constraints\Regex;

class VideoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('link', TextType::class, [
                'label' => false,
                'required' => false,
                'constraints' => [
                    new Regex([
                        'pattern' => '#^((?:https?:)?\/\/)?(?:www\.)?((?:youtube\.com|youtu\.be|dai\.ly|dailymotion\.com|vimeo\.com|player\.vimeo\.com))(\/(?:[\w\-]+\?v=|embed\/|video\/|embed\/video\/)?)([\w\-]+)(\S+)?$#'
                    ])
                ]
            ])
            ->add('delete', ButtonType::class, [
                'label_html' => true,
                'label' => '<i class="fas fa-times"></i>',
                'attr' => [
                    'data-action' => 'delete',
                    'data-target' => '#trick_videos___name__'
                ]
            ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Video::class,
        ]);
    }
}
