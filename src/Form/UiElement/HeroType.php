<?php

declare(strict_types=1);

namespace App\Form\UiElement;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;

class HeroType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('blocks', CollectionType::class, [
            'entry_type' => HeroSlideType::class,
            'button_add_label' => 'Add block',
            'allow_add' => true,
            'allow_delete' => true,
            'by_reference' => false,
            'delete_empty' => true,
            'label' => 'Blocks list',
        ]);
    }
}
