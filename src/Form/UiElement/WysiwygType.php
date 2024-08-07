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

use Symfony\Component\Form\AbstractType;
use MonsieurBiz\SyliusRichEditorPlugin\Form\Type\WysiwygType as WysiwygFormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class WysiwygType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'required' => false,
                'label' => 'Title',
            ])
            ->add('wysiwyg', WysiwygFormType::class, [
                'required' => true,
                'label' => 'Content',
            ])
        ;
    }
}
