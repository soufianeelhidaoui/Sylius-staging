<?php

/*
 * This file is part of Monsieur Biz' Rich Editor plugin for Sylius.
 *
 * (c) Monsieur Biz <sylius@monsieurbiz.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Form\UiElement;

use MonsieurBiz\SyliusRichEditorPlugin\Form\Type\WysiwygType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class TextImageType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('images', CollectionType::class, [
                'entry_type' => TextImageImagesType::class,
                'button_add_label' => 'Add image',
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'delete_empty' => true,
                'label' => 'Images list',
            ])
            ->add('title', TextType::class, [
                'required' => false,
                'label' => 'Title',
            ])
            ->add('text', WysiwygType::class, [
                'required' => false,
                'label' => 'Text',
            ])
            ->add('cta_link', TextType::class, [
                'required' => false,
                'label' => 'Cta link',
                'constraints' => [
                    new Assert\Url([]),
                ],
            ])
            ->add('cta_label', TextType::class, [
                'required' => false,
                'label' => 'Cta text'
            ])
            ->add('mod', ChoiceType::class, [
                'choices'  => [
                    'Image on the left' => 'image-left',
                    'Image on the right' => 'image-right'
                ]
            ])
            ->add('black_background', CheckboxType::class, [
                    'label'    => 'Background noir',
                    'required' => false,
                ]
            )
        ;
    }
}
