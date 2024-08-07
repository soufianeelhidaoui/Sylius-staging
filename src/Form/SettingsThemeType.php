<?php

/*
 * This file is part of Monsieur Biz' Settings plugin for Sylius.
 *
 * (c) Monsieur Biz <sylius@monsieurbiz.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Form;

use MonsieurBiz\SyliusSettingsPlugin\Form\AbstractSettingsType;
use MonsieurBiz\SyliusSettingsPlugin\Form\SettingsTypeInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class SettingsThemeType extends AbstractSettingsType implements SettingsTypeInterface
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('theme', ChoiceType::class, [
                'label' => 'Theme',
                'data_class' => null,
                'required' => true,
                'choices'  => [
                    'Cupra' => 'cupra',
                    'Skoda' => 'skoda',
                    'Volkswagen' => 'vw',
                    'Volkswagen Utilitaire' => 'vwu',
                    'Seat' => 'seat'
                ],
            ])
            ->add('brand', TextType::class, [
                'label' => 'Brand',
                'data_class' => null,
                'required' => true
            ])
            ->add('short_brand', TextType::class, [
                'label' => 'Short brand',
                'data_class' => null,
                'required' => false
            ])
            ->add('official_logo', CheckboxType::class, [
                'label' => 'Display logo on hero',
                'data_class' => null,
                'required' => false
            ])
            ->add('mentions', TextType::class, [
                'label' => 'Mention',
                'data_class' => null,
                'required' => false
            ])
            ->add('cover_height', NumberType::class, [
                'label' => 'Cover height',
                'data_class' => null,
                'required' => true
            ])
            ->add('email_logo_width', NumberType::class, [
                'label' => 'Email logo width',
                'data_class' => null,
                'required' => true
            ])
            ->add('legals_title', TextType::class, [
                'label' => 'Legals title',
                'data_class' => null,
                'required' => true
            ])
            ->add('legals_text', TextareaType::class, [
                'label' => 'Legals text',
                'data_class' => null,
                'required' => true
            ])
            ->add('default_family', TextType::class, [
                'label' => 'Vehicle defautl family',
                'data_class' => null,
                'required' => false
            ])
            ->add('default_id', TextType::class, [
                'label' => 'Vehicle defautl id',
                'data_class' => null,
                'required' => true
            ])
            ->add('payment', CheckboxType::class, [
                'label' => 'Enable payment',
                'data_class' => null,
                'required' => false
            ]);
    }
}
