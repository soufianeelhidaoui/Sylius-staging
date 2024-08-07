<?php

declare(strict_types=1);

namespace App\Form\UiElement;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class CoverType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('image', FileType::class, [
                'label' => 'Image',
                'data_class' => null,
                'required' => true,
                'attr' => ['data-image' => 'true'], // To be able to manage display in form
            ])
            ->add('alt', TextType::class, [
                'required' => false,
                'label' => 'Alt',
            ]);
    }
}
