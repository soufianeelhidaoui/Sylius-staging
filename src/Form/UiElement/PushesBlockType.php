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

use MonsieurBiz\SyliusRichEditorPlugin\Form\Constraints\RichEditorConstraints;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints as Assert;

class PushesBlockType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
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
            ])
            ->add('title', TextType::class, [
                'required' => false,
                'label' => 'Title',
            ])
            ->add('text', TextareaType::class, [
                'required' => false,
                'label' => 'Text',
            ])
            ->add('url', UrlType::class, [
                'required' => false,
                'label' => 'Url',
                'constraints' => [
                    new Assert\Url([]),
                ],
            ])
            ->add('label', TextType::class, [
                'required' => false,
                'label' => 'Label CTA'
            ])
            ->add('cta_style', ChoiceType::class, [
                'label' => 'Style CTA',
                'choices'  => [
                    'Button' => 'button',
                    'Link' => 'link'
                ]
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function(FormEvent $event): void {
            // Change image field constraints depending on submitted value
            $options = $event->getForm()->get('image')->getConfig()->getOptions();
            $options['constraints'] = RichEditorConstraints::getImageConstraints($event->getData(), 'image');
            $event->getForm()->add('image', FileType::class, $options);
        });
    }
}
